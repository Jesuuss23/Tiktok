@extends('layouts.tiktok')

@section('title', 'Dashboard - TikTok ERP')

@section('content')
<div class="p-8 space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                ¡Bienvenido, {{ $userName }}! 👋
            </h1>
            <p class="text-gray-500 mt-1">Resumen de tu actividad en TikTok</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                <i class="fas fa-circle text-green-500 text-xs mr-1"></i> Conectado con TikTok
            </span>
            <span class="text-sm text-gray-400">
                {{ $currentTime->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>

<!-- Tarjetas de Métricas -->
<!-- Tarjetas de Métricas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    
    <!-- Seguidores -->
    <div class="card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Seguidores</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">
                    {{ number_format($session->follower_count ?? 0) }}
                </p>
            </div>
            <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Videos -->
    <div class="card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Videos</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">
                    {{ number_format($session->video_count ?? 0) }}
                </p>
            </div>
            <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-video text-red-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Likes -->
    <div class="card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Likes</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">
                    {{ number_format($session->likes_count ?? 0) }}
                </p>
            </div>
            <div class="w-14 h-14 bg-pink-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-heart text-pink-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Siguiendo -->
    <div class="card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Siguiendo</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">
                    {{ number_format($session->following_count ?? 0) }}
                </p>
            </div>
            <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-check text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Banner informativo sobre permisos -->
@if(!$session || !$session->follower_count)
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
    <div class="flex items-start space-x-3">
        <i class="fas fa-lightbulb text-yellow-500 text-xl mt-1"></i>
        <div>
            <h3 class="font-bold text-yellow-800">Datos de demostración</h3>
            <p class="text-yellow-700 text-sm mt-1">
                Para ver datos reales, necesitas solicitar scopes adicionales en TikTok Developers.
                Actualmente solo tienes el permiso básico (<code>user.info.basic</code>).
            </p>
            <div class="mt-2 space-y-1 text-sm text-yellow-700">
                <p><strong>Scopes necesarios para datos reales:</strong></p>
                <ul class="list-disc list-inside ml-2">
                    <li><code>user.info.stats</code> - Para seguidores, likes, etc.</li>
                    <li><code>user.info.profile</code> - Para información del perfil</li>
                    <li><code>video.list</code> - Para listar videos</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

    <!-- Pedidos Hoy -->
    <div class="card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Pedidos Hoy</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">
                    {{ $ordersCount }}
                </p>
                <p class="text-xs text-gray-400 mt-1">IA detectados</p>
            </div>
            <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-shopping-bag text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Likes Totales -->
    <div class="card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Likes</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">
                    {{ number_format($userStats->likes_count ?? ($session->likes_count ?? 0)) }}
                </p>
                <p class="text-xs text-gray-400 mt-1">En todos los videos</p>
            </div>
            <div class="w-14 h-14 bg-pink-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-heart text-pink-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Mensajes Recientes -->
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold text-gray-800">
            <i class="fas fa-comments text-blue-500 mr-2"></i>Mensajes Recientes
        </h2>
        <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">
            Ver todos <i class="fas fa-arrow-right ml-1"></i>
        </button>
    </div>
    
    @if($recentMessages && $recentMessages->count() > 0)
        <div class="space-y-3">
            @foreach($recentMessages as $message)
                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user text-gray-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-medium text-gray-800">{{ $message->sender_username }}</p>
                            @if($message->is_order)
                                <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded-full">Pedido</span>
                            @endif
                            @if($message->is_blocked)
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded-full">Bloqueado</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 truncate">{{ $message->content }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $message->message_created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-inbox text-gray-400 text-2xl"></i>
            </div>
            <p class="text-gray-600 font-medium">No hay mensajes todavía</p>
            <p class="text-gray-400 text-sm mt-1">Inicia un LIVE para recibir mensajes</p>
        </div>
    @endif
</div>

        <!-- Pedidos Detectados -->
        <div class="card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pedidos Hoy</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">--</p>
                    <p class="text-xs text-gray-400 mt-1">IA detectados</p>
                </div>
                <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Estado LIVE -->
        <div class="card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Estado LIVE</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">
                        <span class="text-gray-400">Offline</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-1">No hay transmisión</p>
                </div>
                <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-broadcast-tower text-gray-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de 2 columnas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Mensajes Recientes -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-comments text-blue-500 mr-2"></i>Mensajes Recientes
                </h2>
                <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Ver todos <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
            
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                </div>
                <p class="text-gray-600 font-medium">No hay mensajes todavía</p>
                <p class="text-gray-400 text-sm mt-1">Inicia un LIVE para recibir mensajes</p>
            </div>
        </div>

        <!-- Configuración de IA -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-robot text-purple-500 mr-2"></i>IA Moderación
            </h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-800">Filtro de palabras</p>
                        <p class="text-sm text-gray-500">Bloquea mensajes ofensivos</p>
                    </div>
                    <button class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition">
                        Configurar
                    </button>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-800">Detección de pedidos</p>
                        <p class="text-sm text-gray-500">Identifica órdenes en mensajes</p>
                    </div>
                    <button class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition">
                        Configurar
                    </button>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-800">Respuestas automáticas</p>
                        <p class="text-sm text-gray-500">Mensajes predefinidos</p>
                    </div>
                    <button class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition">
                        Configurar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- API Info -->
    <div class="bg-gradient-to-r from-gray-900 to-gray-700 text-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold">
                    <i class="fab fa-tiktok mr-2"></i>Estado de Conexión API
                </h3>
                <div class="flex gap-4 mt-2 text-sm">
                    <span>✅ Autenticado</span>
                    <span>🟢 Token activo</span>
                    <span>📊 ID: {{ $userId ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="text-right text-sm">
                <p>Última actualización:</p>
                <p class="font-mono">{{ $currentTime->format('H:i:s') }}</p>
            </div>
        </div>
    </div>

</div>
@endsection