<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTok ERP - Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900">
    
    @if($isAuthenticated)
    <!-- Página de Bienvenida (Ya autenticado) -->
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center">
            <div class="mb-8">
                <i class="fab fa-tiktok text-7xl text-white"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">¡Hola de nuevo, {{ $userName }}!</h1>
            <p class="text-gray-400 mb-8">Tu sesión está activa</p>
            
            <a href="{{ url('/tiktok-api/panel') }}" 
               class="inline-flex items-center px-10 py-4 bg-white text-gray-900 rounded-full font-bold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                <i class="fas fa-chart-line mr-3"></i>
                Ir al Panel de Control
            </a>
            
            <div class="mt-6">
                <a href="{{ url('/tiktok-api/logout') }}" 
                   class="text-gray-400 hover:text-white text-sm">
                    <i class="fas fa-sign-out-alt mr-1"></i>Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
    
    @else
    <!-- Página de Landing (No autenticado) -->
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center max-w-2xl px-4">
            <!-- Logo y título -->
            <div class="mb-8">
                <div class="inline-block p-4 bg-gray-800 rounded-full mb-6">
                    <i class="fab fa-tiktok text-6xl text-white"></i>
                </div>
                <h1 class="text-5xl font-bold text-white mb-4">
                    TikTok <span class="text-purple-400">ERP</span>
                </h1>
                <p class="text-xl text-gray-400">
                    Gestiona tus mensajes, ventas y transmisiones en vivo con IA
                </p>
            </div>
            
            <!-- Botón de Login -->
            <a href="{{ url('/tiktok-api/login') }}" 
               class="inline-flex items-center px-10 py-5 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-full font-bold text-lg hover:from-purple-600 hover:to-pink-600 transition-all transform hover:scale-105 shadow-xl">
                <i class="fab fa-tiktok mr-3 text-2xl"></i>
                Iniciar Sesión con TikTok
            </a>
            
            <!-- Características -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16">
                <div class="p-6 bg-gray-800 rounded-xl">
                    <i class="fas fa-robot text-3xl text-purple-400 mb-3"></i>
                    <h3 class="text-white font-bold mb-2">Moderación IA</h3>
                    <p class="text-gray-400 text-sm">Filtra mensajes automáticamente con inteligencia artificial</p>
                </div>
                
                <div class="p-6 bg-gray-800 rounded-xl">
                    <i class="fas fa-comments text-3xl text-blue-400 mb-3"></i>
                    <h3 class="text-white font-bold mb-2">Chat en Vivo</h3>
                    <p class="text-gray-400 text-sm">Visualiza y modera mensajes de tus transmisiones</p>
                </div>
                
                <div class="p-6 bg-gray-800 rounded-xl">
                    <i class="fas fa-shopping-cart text-3xl text-green-400 mb-3"></i>
                    <h3 class="text-white font-bold mb-2">Pedidos</h3>
                    <p class="text-gray-400 text-sm">Detecta y gestiona pedidos automáticamente</p>
                </div>
            </div>
        </div>
    </div>
    @endif

</body>
</html>