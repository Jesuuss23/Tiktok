<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Session;
use App\Services\TikTokApiService;
use App\Models\TikTokSession;
use Illuminate\Support\Facades\Log;

class TikTokController extends Controller
{
    protected $tikTokApi;

    public function __construct()
    {
        $this->tikTokApi = new TikTokApiService(session('tiktok_token'));
    }

    // 1. Página de inicio
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

    // 2. Redirección al Login
public function redirectToTikTok() {
    if (session()->has('tiktok_token')) {
        return redirect('https://auto.donguando.com/tiktok-api/panel');
    }
    
    // Construir la URL manualmente para asegurar los scopes
    $clientId = config('services.tiktok.client_id');
    $redirectUri = urlencode('https://auto.donguando.com/tiktok-api/api/tiktok/callback');
    $state = bin2hex(random_bytes(16));
    session(['tiktok_oauth_state' => $state]);
    
    $scopes = [
        'user.info.basic',
        'user.info.profile',
        'user.info.stats',
        'video.list',
        'video.upload'
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

    // 3. Callback ACTUALIZADO
public function handleTikTokCallback(Request $request) {
    try {
        // Verificar que tenemos código
        if (!$request->has('code')) {
            return redirect('https://auto.donguando.com/tiktok-api/panel');
        }
        
        // Verificar state para prevenir CSRF
        if ($request->has('state') && $request->state !== session('tiktok_oauth_state')) {
            Log::warning('State mismatch en callback');
            return redirect('https://auto.donguando.com/tiktok-api/')->with('error', 'Error de seguridad');
        }
        
        // Intercambiar el código por un token
        $client = new \GuzzleHttp\Client([
            'http_errors' => false
        ]);
        
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
        
        Log::info('TikTok Token Response:', ['data' => $tokenData]);
        
        if (isset($tokenData['error'])) {
            Log::error('Error en token:', $tokenData);
            
            // Si el código ya fue usado, redirigir al panel
            if (str_contains($tokenData['error_description'] ?? '', 'invalid_grant')) {
                return redirect('https://auto.donguando.com/tiktok-api/panel');
            }
            
            throw new \Exception($tokenData['error_description'] ?? $tokenData['error']);
        }
        
        if (!isset($tokenData['access_token'])) {
            throw new \Exception('No se recibió access_token');
        }
        
        // Ahora obtener información del usuario con el token
        $userResponse = $client->get('https://open.tiktokapis.com/v2/user/info/', [
            'headers' => [
                'Authorization' => 'Bearer ' . $tokenData['access_token'],
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'fields' => 'open_id,union_id,avatar_url,display_name,username,follower_count,following_count,likes_count,video_count,is_verified,bio_description,profile_deep_link'
            ]
        ]);
        
        $userData = json_decode($userResponse->getBody(), true);
        
        Log::info('TikTok User Data obtenida:', [
            'followers' => $userData['data']['user']['follower_count'] ?? 0,
            'videos' => $userData['data']['user']['video_count'] ?? 0,
            'likes' => $userData['data']['user']['likes_count'] ?? 0,
        ]);
        
        $user = $userData['data']['user'] ?? $userData['data'];
        
        // Guardar en sesión
        session([
            'tiktok_user_name' => $user['display_name'] ?? 'Usuario',
            'tiktok_user_id' => $user['open_id'] ?? $user['union_id'],
            'tiktok_token' => $tokenData['access_token'],
            'tiktok_refresh_token' => $tokenData['refresh_token'] ?? null,
        ]);
        
        // Guardar en base de datos
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
        Log::error('Error en callback:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return "<div style='padding: 40px; font-family: Arial;'>" .
               "<h1>Error de autenticación</h1>" .
               "<p style='color: red;'>" . $e->getMessage() . "</p>" .
               "<br><a href='/tiktok-api/' style='color: blue;'>← Volver al inicio</a>" .
               "</div>";
    }
}

private function fetchAndUpdateUserData($token)
{
    try {
        $apiService = new TikTokApiService($token);
        
        // Obtener info del usuario
        $userResponse = $apiService->getUserInfo();
        
        if ($userResponse['success'] && isset($userResponse['data'])) {
            $userData = $userResponse['data']['user'] ?? $userResponse['data'];
            
            TikTokSession::where('tiktok_user_id', session('tiktok_user_id'))
                ->update([
                    'follower_count' => $userData['follower_count'] ?? 0,
                    'following_count' => $userData['following_count'] ?? 0,
                    'likes_count' => $userData['likes_count'] ?? 0,
                    'video_count' => $userData['video_count'] ?? 0,
                    'display_name' => $userData['display_name'] ?? session('tiktok_user_name'),
                    'avatar_url' => $userData['avatar_url'] ?? null,
                ]);
            
            Log::info('Datos de usuario actualizados correctamente', [
                'followers' => $userData['follower_count'] ?? 0,
                'videos' => $userData['video_count'] ?? 0,
                'likes' => $userData['likes_count'] ?? 0
            ]);
        } else {
            Log::warning('No se pudieron obtener datos del usuario', [
                'error' => $userResponse['error'] ?? 'Unknown'
            ]);
        }
        
    } catch (\Exception $e) {
        Log::error('Error obteniendo datos del usuario: ' . $e->getMessage());
    }
}

    // 4. Panel de Control ACTUALIZADO
public function dashboard() {
    if (!session()->has('tiktok_token')) {
        return redirect()->route('tiktok.index');
    }

    $session = TikTokSession::where('tiktok_user_id', session('tiktok_user_id'))->first();

    return view('tiktok.dashboard', [
        'userName' => session('tiktok_user_name'),
        'userId' => session('tiktok_user_id'),
        'session' => $session,
        'recentMessages' => \App\Models\TikTokMessage::where('tiktok_user_id', session('tiktok_user_id'))
            ->orderBy('message_created_at', 'desc')
            ->take(5)
            ->get(),
        'ordersCount' => \App\Models\TikTokOrder::where('tiktok_user_id', session('tiktok_user_id'))
            ->whereDate('created_at', today())
            ->count(),
        'currentTime' => now()
    ]);
}

    // 5. Cerrar Sesión
    public function logout(Request $request) {
        session()->forget(['tiktok_token', 'tiktok_user_name', 'tiktok_user_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('https://auto.donguando.com/tiktok-api/');
    }

    /**
     * Obtener datos del usuario desde la API
     */
    private function fetchUserData($token)
    {
        try {
            $apiService = new TikTokApiService($token);
            $response = $apiService->getUserInfo();
            
            if ($response['success'] && isset($response['data']['user'])) {
                $userData = $response['data']['user'];
                
                TikTokSession::where('tiktok_user_id', session('tiktok_user_id'))
                    ->update([
                        'follower_count' => $userData['follower_count'] ?? 0,
                        'following_count' => $userData['following_count'] ?? 0,
                        'likes_count' => $userData['likes_count'] ?? 0,
                        'video_count' => $userData['video_count'] ?? 0,
                    ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching user data: ' . $e->getMessage());
        }
    }
}