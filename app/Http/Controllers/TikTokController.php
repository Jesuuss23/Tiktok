<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Session;
use App\Services\TikTokApiService;
use App\Services\TikTokLiveMonitor;
use App\Models\TikTokSession;
use App\Models\TikTokBlockedWord;
use Illuminate\Support\Facades\Log;

class TikTokController extends Controller
{
    protected $tikTokApi;

    public function __construct()
    {
        $this->tikTokApi = new TikTokApiService(session('tiktok_token'));
    }

    // ==================== AUTENTICACIÓN ====================
    
    /**
     * 1. Página de inicio
     */
    public function index() {
        if (session()->has('tiktok_token')) {
            return view('tiktok.landing', [
                'isAuthenticated' => true,
                'userName' => session('tiktok_user_name')
            ]);
        }
        return view('tiktok.landing', [
            'isAuthenticated' => false
        ]);
    }

    /**
     * 2. Redirección al Login de TikTok
     */
    public function redirectToTikTok() {
        if (session()->has('tiktok_token')) {
            return redirect('https://auto.donguando.com/tiktok-api/panel');
        }
        
        // Construir URL manualmente para asegurar los scopes
        $clientId = config('services.tiktok.client_id');
        $state = bin2hex(random_bytes(16));
        session(['tiktok_oauth_state' => $state]);
        
        $scopes = [
            'user.info.basic',
            'user.info.profile',
            'user.info.stats',
            'video.list',
        ];
        
        $scopeString = implode(',', $scopes);
        
        $url = "https://www.tiktok.com/v2/auth/authorize/?" . http_build_query([
            'client_key' => $clientId,
            'response_type' => 'code',
            'scope' => $scopeString,
            'redirect_uri' => 'https://auto.donguando.com/tiktok-api/api/tiktok/callback',
            'state' => $state,
        ]);
        
        return redirect($url);
    }

    /**
     * 3. Callback de TikTok
     */
    public function handleTikTokCallback(Request $request) {
        try {
            if (!$request->has('code')) {
                return redirect('https://auto.donguando.com/tiktok-api/panel');
            }
            
            if ($request->has('state') && $request->state !== session('tiktok_oauth_state')) {
                Log::warning('State mismatch en callback');
                return redirect('https://auto.donguando.com/tiktok-api/')->with('error', 'Error de seguridad');
            }
            
            $client = new \GuzzleHttp\Client(['http_errors' => false]);
            
            $response = $client->post('https://open.tiktokapis.com/v2/oauth/token/', [
                'form_params' => [
                    'client_key' => config('services.tiktok.client_id'),
                    'client_secret' => config('services.tiktok.client_secret'),
                    'code' => $request->code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => 'https://auto.donguando.com/tiktok-api/api/tiktok/callback',
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ]
            ]);
            
            $tokenData = json_decode($response->getBody(), true);
            
            if (isset($tokenData['error'])) {
                if (str_contains($tokenData['error_description'] ?? '', 'invalid_grant')) {
                    return redirect('https://auto.donguando.com/tiktok-api/panel');
                }
                throw new \Exception($tokenData['error_description'] ?? $tokenData['error']);
            }
            
            if (!isset($tokenData['access_token'])) {
                throw new \Exception('No se recibió access_token');
            }
            
            // Obtener información del usuario
            $userResponse = $client->get('https://open.tiktokapis.com/v2/user/info/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $tokenData['access_token'],
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'fields' => 'open_id,union_id,avatar_url,display_name,username,follower_count,following_count,likes_count,video_count,is_verified,bio_description'
                ]
            ]);
            
            $userData = json_decode($userResponse->getBody(), true);
            $user = $userData['data']['user'] ?? $userData['data'];
            
            // Guardar en sesión
            session([
                'tiktok_user_name' => $user['display_name'] ?? 'Usuario',
                'tiktok_user_id' => $user['open_id'] ?? $user['union_id'],
                'tiktok_token' => $tokenData['access_token'],
                'tiktok_refresh_token' => $tokenData['refresh_token'] ?? null,
            ]);
            
            // Guardar en BD
            TikTokSession::updateOrCreate(
                ['tiktok_user_id' => session('tiktok_user_id')],
                [
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? null,
                    'username' => $user['username'] ?? $user['display_name'],
                    'display_name' => $user['display_name'],
                    'avatar_url' => $user['avatar_url'] ?? null,
                    'follower_count' => $user['follower_count'] ?? 0,
                    'following_count' => $user['following_count'] ?? 0,
                    'likes_count' => $user['likes_count'] ?? 0,
                    'video_count' => $user['video_count'] ?? 0,
                    'token_expires_at' => isset($tokenData['expires_in']) 
                        ? now()->addSeconds($tokenData['expires_in']) 
                        : now()->addHours(24),
                    'raw_data' => json_encode($userData['data']),
                ]
            );

            return redirect('https://auto.donguando.com/tiktok-api/panel');
            
        } catch (\Exception $e) {
            Log::error('Error en callback: ' . $e->getMessage());
            
            return "<div style='padding: 40px; font-family: Arial;'>" .
                   "<h1>Error de autenticación</h1>" .
                   "<p style='color: red;'>" . $e->getMessage() . "</p>" .
                   "<br><a href='/tiktok-api/' style='color: blue;'>← Volver al inicio</a>" .
                   "</div>";
        }
    }

    /**
     * 4. Cerrar Sesión
     */
public function logout(Request $request) {
    // 1. Limpiar TODAS las variables de sesión de TikTok
    session()->forget([
        'tiktok_token',
        'tiktok_user_name', 
        'tiktok_user_id',
        'tiktok_refresh_token',
        'tiktok_oauth_state'
    ]);
    
    // 2. Invalidar sesión completamente
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    // 3. Redirigir al inicio SIN sesión
    return redirect('https://auto.donguando.com/tiktok-api/');
}

    // ==================== DASHBOARD ====================
    
    /**
     * 5. Panel de Control Principal
     */
    public function dashboard() {
        if (!session()->has('tiktok_token')) {
            return redirect()->route('tiktok.index');
        }

        $session = TikTokSession::where('tiktok_user_id', session('tiktok_user_id'))->first();
        
        $recentMessages = \App\Models\TikTokMessage::where('tiktok_user_id', session('tiktok_user_id'))
            ->orderBy('message_created_at', 'desc')
            ->take(5)
            ->get();
            
        $ordersCount = \App\Models\TikTokOrder::where('tiktok_user_id', session('tiktok_user_id'))
            ->whereDate('created_at', today())
            ->count();

        return view('tiktok.dashboard', [
            'userName' => session('tiktok_user_name'),
            'userId' => session('tiktok_user_id'),
            'session' => $session,
            'recentMessages' => $recentMessages,
            'ordersCount' => $ordersCount,
            'currentTime' => now()
        ]);
    }

    // ==================== CONTROL LIVE ====================
    
    /**
     * 6. Vista de Control LIVE
     */
    public function liveControl()
    {
        if (!session()->has('tiktok_token')) {
            return redirect()->route('tiktok.index');
        }

        $blockedWords = TikTokBlockedWord::where('tiktok_user_id', session('tiktok_user_id'))
            ->pluck('word')
            ->toArray();

        return view('tiktok.live-control', [
            'userName' => session('tiktok_user_name'),
            'blockedWords' => $blockedWords,
        ]);
    }

    /**
     * 7. Iniciar monitoreo de LIVE
     */
    public function startLiveMonitoring(Request $request)
    {
        try {
            $liveMonitor = new TikTokLiveMonitor();
            
            $session = $liveMonitor->startMonitoring(
                session('tiktok_user_id'),
                $request->live_id,
                $request->title ?? 'Sin título'
            );

            return response()->json([
                'success' => true,
                'session_id' => $session->id,
                'message' => 'Monitoreo iniciado correctamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error iniciando monitoreo LIVE: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 8. Detener monitoreo de LIVE
     */
    public function stopLiveMonitoring(Request $request)
    {
        try {
            $liveMonitor = new TikTokLiveMonitor();
            
            $liveMonitor->stopMonitoring(
                session('tiktok_user_id'),
                $request->live_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Monitoreo detenido'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 9. Procesar mensaje entrante del LIVE
     */
    public function processLiveMessage(Request $request)
    {
        try {
            $liveMonitor = new TikTokLiveMonitor();
            
            $result = $liveMonitor->processIncomingMessage([
                'tiktok_user_id' => session('tiktok_user_id'),
                'live_id' => $request->live_id,
                'sender_id' => $request->sender_id,
                'sender_username' => $request->sender_username,
                'content' => $request->content,
            ]);

            return response()->json([
                'success' => true,
                'result' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error procesando mensaje LIVE: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== PALABRAS BLOQUEADAS ====================
    
    /**
     * 10. Agregar palabra bloqueada
     */
    public function addBlockedWord(Request $request)
    {
        $request->validate([
            'word' => 'required|string|max:255'
        ]);

        try {
            TikTokBlockedWord::create([
                'tiktok_user_id' => session('tiktok_user_id'),
                'word' => $request->word,
                'action' => 'block'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Palabra bloqueada agregada'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 11. Eliminar palabra bloqueada
     */
    public function removeBlockedWord(Request $request)
    {
        TikTokBlockedWord::where('tiktok_user_id', session('tiktok_user_id'))
            ->where('word', $request->word)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Palabra eliminada'
        ]);
    }

    /**
     * 12. Listar palabras bloqueadas
     */
    public function getBlockedWords()
    {
        $words = TikTokBlockedWord::where('tiktok_user_id', session('tiktok_user_id'))
            ->pluck('word')
            ->toArray();

        return response()->json([
            'success' => true,
            'words' => $words
        ]);
    }
    /**
 * Vista de Mensajes
 */
public function mensajes()
{
    if (!session()->has('tiktok_token')) {
        return redirect()->route('tiktok.index');
    }
    
    return view('tiktok.mensajes', [
        'userName' => session('tiktok_user_name')
    ]);
}

/**
 * API: Obtener mensajes
 */
public function getMensajes()
{
    // Por ahora devolvemos un array vacío
    // Cuando implementemos la API de mensajes de TikTok, aquí obtendremos los datos reales
    return response()->json([
        'success' => true,
        'messages' => []
    ]);
}

/**
 * Vista de Moderación
 */
public function moderacion()
{
    if (!session()->has('tiktok_token')) {
        return redirect()->route('tiktok.index');
    }
    
    return view('tiktok.moderacion', [
        'userName' => session('tiktok_user_name')
    ]);
}
/**
 * Recibir mensaje desde el script Python del LIVE
 */
public function receiveLiveMessage(Request $request)
{
    // Verificar API Key
    if ($request->header('X-API-Key') !== config('app.tiktok_api_key')) {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    // Aplicar filtro de palabras bloqueadas
    $blockedWords = TikTokBlockedWord::where('tiktok_user_id', session('tiktok_user_id'))
        ->pluck('word')
        ->toArray();
    
    $content = strtolower($request->content);
    $isBlocked = false;
    $blockedWord = null;
    
    foreach ($blockedWords as $word) {
        if (stripos($content, strtolower($word)) !== false) {
            $isBlocked = true;
            $blockedWord = $word;
            break;
        }
    }

    // Guardar el mensaje
    $message = \App\Models\TikTokLiveMessage::create([
        'tiktok_user_id' => session('tiktok_user_id') ?? 'unknown',
        'live_id' => $request->live_id,
        'sender_id' => $request->sender_id,
        'sender_username' => $request->sender_username,
        'content' => $request->content,
        'status' => $isBlocked ? 'blocked' : 'approved',
        'moderation_reason' => $isBlocked ? "Contiene: {$blockedWord}" : null,
        'message_created_at' => now(),
    ]);

    $result = [
        'action' => $isBlocked ? 'blocked' : 'approved',
        'message_id' => $message->id,
    ];

    return response()->json([
        'success' => true,
        'result' => $result
    ]);
}

public function getLiveMessages()
{
    $messages = \App\Models\TikTokLiveMessage::where('tiktok_user_id', session('tiktok_user_id', 'unknown'))
        ->orderBy('message_created_at', 'desc')
        ->take(50)
        ->get();

    return response()->json([
        'success' => true,
        'messages' => $messages
    ]);
}
/**
 * Vista de mensajes filtrados
 */
public function mensajesFiltrados()
{
    if (!session()->has('tiktok_token')) {
        return redirect()->route('tiktok.index');
    }
    
    return view('tiktok.mensajes-filtrados', [
        'userName' => session('tiktok_user_name')
    ]);
}

/**
 * API: Sesiones de LIVE
 */
public function getLiveSessions()
{
    $sessions = \App\Models\TikTokLiveSession::orderBy('started_at', 'desc')
        ->take(20)
        ->get(['live_id', 'title', 'started_at', 'ended_at', 'is_active']);
    
    return response()->json(['success' => true, 'sessions' => $sessions]);
}

/**
 * API: Mensajes filtrados con paginación
 */
public function getMensajesFiltrados(Request $request)
{
    $query = \App\Models\TikTokLiveMessage::query();
    
    // Filtro por LIVE
    if ($request->live_id) {
        $query->where('live_id', $request->live_id);
    }
    
    // Filtro por estado
    if ($request->status) {
        $query->where('status', $request->status);
    }
    
    // Filtro por fecha
    if ($request->desde) {
        $query->whereDate('message_created_at', '>=', $request->desde);
    }
    if ($request->hasta) {
        $query->whereDate('message_created_at', '<=', $request->hasta);
    }
    
    // Búsqueda por texto
    if ($request->busqueda) {
        $query->where(function($q) use ($request) {
            $q->where('sender_username', 'like', "%{$request->busqueda}%")
              ->orWhere('content', 'like', "%{$request->busqueda}%");
        });
    }
    
    // Estadísticas
    $stats = [
        'total' => $query->count(),
        'aprobados' => (clone $query)->where('status', 'approved')->count(),
        'bloqueados' => (clone $query)->where('status', 'blocked')->count(),
        'pedidos' => (clone $query)->where('is_order', true)->count(),
    ];
    
    // Paginación
    $pagina = $request->pagina ?? 1;
    $porPagina = $request->por_pagina ?? 20;
    $total = $query->count();
    $mensajes = $query->orderBy('message_created_at', 'desc')
        ->skip(($pagina - 1) * $porPagina)
        ->take($porPagina)
        ->get();
    
    return response()->json([
        'success' => true,
        'mensajes' => $mensajes,
        'estadisticas' => $stats,
        'paginacion' => [
            'pagina_actual' => (int)$pagina,
            'ultima_pagina' => ceil($total / $porPagina),
            'total' => $total,
            'desde' => ($pagina - 1) * $porPagina + 1,
            'hasta' => min($pagina * $porPagina, $total),
        ]
    ]);
}

/**
 * Eliminar mensaje individual
 */
public function deleteMensaje($id)
{
    \App\Models\TikTokLiveMessage::destroy($id);
    return response()->json(['success' => true, 'message' => 'Mensaje eliminado']);
}

/**
 * Limpiar mensajes por LIVE
 */
public function limpiarMensajes(Request $request)
{
    $query = \App\Models\TikTokLiveMessage::query();
    
    if ($request->live_id) {
        $query->where('live_id', $request->live_id);
    }
    
    $count = $query->count();
    $query->delete();
    
    return response()->json([
        'success' => true,
        'message' => "{$count} mensajes eliminados correctamente"
    ]);
}

/**
 * Verificar si el usuario autenticado está en vivo
 */
public function checkIfLive(Request $request)
{
    // Buscar en BD por el username de la sesión, o usar el token como respaldo
    $username = null;
    
    // Intentar desde la sesión
    if (session()->has('tiktok_user_name')) {
        $sessionName = session('tiktok_user_name');
        $dbSession = \App\Models\TikTokSession::where('display_name', $sessionName)
            ->orWhere('username', $sessionName)
            ->first();
        $username = $dbSession->username ?? $sessionName;
    }
    
    // Si no, buscar por el último token activo
    if (!$username && session()->has('tiktok_token')) {
        $dbSession = \App\Models\TikTokSession::where('access_token', session('tiktok_token'))->first();
        $username = $dbSession->username ?? null;
    }
    
    // Si aún no, buscar el último registro de la BD
    if (!$username) {
        $dbSession = \App\Models\TikTokSession::latest()->first();
        $username = $dbSession->username ?? null;
    }
    
    // Parámetro directo (para debug)
    if (!$username && $request->has('username')) {
        $username = str_replace('@', '', $request->query('username'));
    }
    
    if (!$username) {
        return response()->json(['live' => false, 'error' => 'No se encontró el usuario']);
    }
    
    $username = str_replace('@', '', $username);

    try {
        $scriptPath = base_path('scripts/check_live.py');
        $pythonPath = '/var/www/tiktok-erp-bridge/venv/bin/python';
        
        $process = new \Symfony\Component\Process\Process([
            $pythonPath, $scriptPath, '--username', $username
        ]);
        
        $process->setTimeout(20);
        $process->run();
        
        $output = trim($process->getOutput());
        $result = json_decode($output, true);
        
        return response()->json($result);
        
    } catch (\Exception $e) {
        return response()->json(['live' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Iniciar monitoreo desde el panel (con el usuario de la sesión)
 */
public function iniciarMonitoreoPanel(Request $request)
{
    // Intentar obtener username de varias fuentes
    $username = null;
    
    // 1. Del request (enviado desde el frontend)
    if ($request->has('username')) {
        $username = str_replace('@', '', $request->username);
    }
    
    // 2. De la BD usando el token de sesión
    if (!$username && session()->has('tiktok_token')) {
        $dbSession = \App\Models\TikTokSession::where('access_token', session('tiktok_token'))->first();
        $username = $dbSession->username ?? null;
    }
    
    // 3. Último registro de la BD
    if (!$username) {
        $dbSession = \App\Models\TikTokSession::latest()->first();
        $username = $dbSession->username ?? null;
    }
    
    if (!$username) {
        return response()->json(['success' => false, 'error' => 'No se encontró el usuario']);
    }
    
    $username = str_replace('@', '', $username);
    
    // Ejecutar el script en background
    $scriptPath = base_path('scripts/bridge_live.py');
    $pythonPath = '/var/www/tiktok-erp-bridge/venv/bin/python';
    $apiKey = config('app.tiktok_api_key');
    $url = config('app.url') . '/api/live-message';
    
    $command = "nohup {$pythonPath} {$scriptPath} --username {$username} --url {$url} --api-key {$apiKey} > /dev/null 2>&1 &";
    
    exec($command);
    
    return response()->json(['success' => true, 'message' => "Monitoreo iniciado para @{$username}"]);
}

/**
 * Detener monitoreo
 */
public function detenerMonitoreoPanel()
{
    session()->forget(['monitoreo_activo', 'monitoreo_username']);
    
    // Matar el proceso Python
    exec("pkill -f bridge_live.py");
    
    return response()->json(['success' => true, 'message' => 'Monitoreo detenido']);
}

/**
 * Obtener mensajes bloqueados (para mostrar en el panel)
 */
public function getMensajesBloqueados(Request $request)
{
    $liveId = $request->live_id;
    
    $query = \App\Models\TikTokLiveMessage::where('status', 'blocked');
    
    if ($liveId) {
        $query->where('live_id', $liveId);
    }
    
    $bloqueados = $query->orderBy('message_created_at', 'desc')
        ->take(20)
        ->get(['id', 'sender_id', 'sender_username', 'content', 'moderation_reason', 'message_created_at']);
    
    return response()->json(['success' => true, 'bloqueados' => $bloqueados]);
}
}
