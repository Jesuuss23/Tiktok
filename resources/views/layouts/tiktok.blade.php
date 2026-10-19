<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTok ERP - @yield('title', 'Panel de Control')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .sidebar { transition: all 0.3s; }
        .card { transition: transform 0.2s, box-shadow 0.2s; }
        .card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .live-pulse { animation: pulse 2s infinite; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR -->
        <aside class="sidebar w-64 bg-gray-900 text-white flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-gray-800">
                <h2 class="text-2xl font-bold flex items-center">
                    <i class="fab fa-tiktok mr-3 text-xl"></i>TikTok ERP
                </h2>
                <p class="text-gray-400 text-xs mt-1">Panel de Control</p>
            </div>
            
            <!-- Navegación -->
            <nav class="flex-1 p-4 space-y-1">
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-3 ml-3">Menú Principal</p>
                
                <a href="{{ url('/tiktok-api/panel') }}" 
                   class="flex items-center p-3 rounded-lg {{ request()->is('tiktok-api/panel') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }} transition-all">
                    <i class="fas fa-chart-line w-6"></i>
                    <span>Dashboard</span>
                </a>
                
 <a href="{{ url('/tiktok-api/mensajes') }}" 
   class="flex items-center p-3 rounded-lg {{ request()->is('tiktok-api/mensajes') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }} transition-all">
    <i class="fas fa-comments w-6"></i>
    <span>Mensajes</span>
</a>

<a href="{{ url('/tiktok-api/moderacion') }}" 
   class="flex items-center p-3 rounded-lg {{ request()->is('tiktok-api/moderacion') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }} transition-all">
    <i class="fas fa-robot w-6"></i>
    <span>IA Moderación</span>
</a>

<a href="{{ url('/tiktok-api/live-control') }}" 
   class="flex items-center p-3 rounded-lg {{ request()->is('tiktok-api/live-control') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }} transition-all">
    <i class="fas fa-broadcast-tower w-6"></i>
    <span>Control LIVE</span>
</a>
<a href="{{ url('/tiktok-api/mensajes-filtrados') }}" 
   class="flex items-center p-3 rounded-lg {{ request()->is('tiktok-api/mensajes-filtrados') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }} transition-all">
    <i class="fas fa-filter w-6"></i>
    <span>Mensajes Filtrados</span>
</a>

                <div class="mt-6">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-3 ml-3">Configuración</p>
                    <a href="#" 
                       class="flex items-center p-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition-all">
                        <i class="fas fa-cog w-6"></i>
                        <span>Ajustes</span>
                    </a>
                </div>
            </nav>
            
            <!-- Perfil y Cerrar Sesión -->
            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-gray-300"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ session('tiktok_user_name', 'Usuario') }}</p>
                        <p class="text-xs text-green-400 flex items-center">
                            <span class="live-pulse mr-1">●</span> Conectado
                        </p>
                    </div>
                </div>
                <a href="{{ url('/tiktok-api/logout') }}" 
                   class="flex items-center justify-center w-full p-2 text-sm text-gray-400 hover:text-white hover:bg-red-600 rounded-lg transition-all">
                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                </a>
            </div>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="flex-1 overflow-y-auto">
            @yield('content')
        </main>
    </div>
</body>
</html>