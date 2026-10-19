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
                <i class="fas fa-circle text-green-500 text-xs mr-1"></i> Conectado
            </span>
            <span class="text-sm text-gray-400">
                {{ $currentTime->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>

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

    <!-- Accesos Rápidos -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Mensajes -->
        <a href="{{ url('/tiktok-api/mensajes') }}" 
           class="card bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-blue-300 transition">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-comments text-blue-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">Mensajes</h3>
                <p class="text-gray-500 text-sm mt-1">Gestiona tus conversaciones</p>
            </div>
        </a>

        <!-- IA Moderación -->
        <a href="{{ url('/tiktok-api/moderacion') }}" 
           class="card bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-purple-300 transition">
            <div class="text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-robot text-purple-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">IA Moderación</h3>
                <p class="text-gray-500 text-sm mt-1">Filtros y palabras bloqueadas</p>
            </div>
        </a>

        <!-- Control LIVE -->
        <a href="{{ url('/tiktok-api/live-control') }}" 
           class="card bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-red-300 transition">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-broadcast-tower text-red-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">Control LIVE</h3>
                <p class="text-gray-500 text-sm mt-1">Modera tus transmisiones</p>
            </div>
        </a>
    </div>

    <!-- API Info -->
    <div class="bg-gradient-to-r from-gray-900 to-gray-700 text-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-bold">
                    <i class="fab fa-tiktok mr-2"></i>Estado de Conexión
                </h3>
                <div class="flex gap-4 mt-2 text-sm">
                    <span>✅ Autenticado</span>
                    <span>🟢 Token activo</span>
                </div>
            </div>
            <div class="text-right text-sm">
                <p>Actualizado:</p>
                <p class="font-mono">{{ $currentTime->format('H:i:s') }}</p>
            </div>
        </div>
    </div>

</div>
@endsection