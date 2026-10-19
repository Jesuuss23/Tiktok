@extends('layouts.tiktok')

@section('title', 'Control LIVE - TikTok ERP')

@section('content')
<div class="p-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">🎥 Control de LIVE</h1>
        <div class="flex items-center gap-3">
            <span id="liveStatusBadge" class="px-4 py-2 bg-gray-500 text-white rounded-full text-sm">
                <i class="fas fa-circle mr-1"></i> Verificando...
            </span>
            <button id="btnMonitoreo" onclick="toggleMonitoreo()" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 hidden">
                <i class="fas fa-play mr-1"></i> Iniciar Monitoreo
            </button>
        </div>
    </div>

    <!-- Panel principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Columna izquierda: Mensajes y Bloqueados -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Mensajes APROBADOS -->
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="p-4 border-b flex justify-between items-center bg-green-50 rounded-t-xl">
                    <h2 class="font-bold text-green-800">✅ Mensajes Aprobados</h2>
                    <span id="contadorAprobados" class="text-sm text-green-600">0 mensajes</span>
                </div>
                <div id="aprobadosContainer" class="h-64 overflow-y-auto p-4 space-y-2">
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p>Esperando mensajes...</p>
                    </div>
                </div>
            </div>

            <!-- Mensajes BLOQUEADOS -->
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="p-4 border-b flex justify-between items-center bg-red-50 rounded-t-xl">
                    <h2 class="font-bold text-red-800">🚫 Mensajes Bloqueados</h2>
                    <span id="contadorBloqueados" class="text-sm text-red-600">0 mensajes</span>
                </div>
                <div id="bloqueadosContainer" class="h-64 overflow-y-auto p-4 space-y-2">
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-shield-alt text-3xl mb-2"></i>
                        <p>No hay mensajes bloqueados</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Info y controles -->
        <div class="space-y-4">
            
            <!-- Info del LIVE -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-bold mb-2">📡 Estado del LIVE</h3>
                <div id="liveInfo" class="text-sm text-gray-500">
                    <p>No hay LIVE activo</p>
                </div>
            </div>

            <!-- Palabras Bloqueadas -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-bold mb-3">🚫 Palabras Bloqueadas</h3>
                <div class="flex gap-2 mb-3">
                    <input type="text" id="blockedWord" class="flex-1 border rounded px-3 py-2 text-sm" placeholder="Nueva palabra...">
                    <button onclick="addBlockedWord()" class="bg-red-500 text-white px-4 py-2 rounded text-sm">+</button>
                </div>
                <div id="blockedWordsList" class="text-sm space-y-1 max-h-40 overflow-y-auto"></div>
            </div>
        </div>
    </div>
</div>

<script>
let monitoreoActivo = false;
let liveIdActual = null;

document.addEventListener('DOMContentLoaded', function() {
    verificarEstadoLive();
    cargarPalabrasBloqueadas();
    setInterval(verificarEstadoLive, 30000);
    
    @if(session('monitoreo_activo'))
        monitoreoActivo = true;
        actualizarBotonMonitoreo();
        cargarMensajesEnVivo();
    @endif
});

function verificarEstadoLive() {
    fetch('/tiktok-api/api/check-live')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('liveStatusBadge');
            const btn = document.getElementById('btnMonitoreo');
            const info = document.getElementById('liveInfo');
            
            if (data.live) {
                badge.className = 'px-4 py-2 bg-red-500 text-white rounded-full text-sm';
                badge.innerHTML = '<i class="fas fa-circle live-pulse mr-1"></i> EN VIVO';
                btn.classList.remove('hidden');
                liveIdActual = data.room_id;
                info.innerHTML = `
                    <p>🔴 <strong>@${data.username}</strong></p>
                    <p>Room ID: ${data.room_id ? data.room_id.substring(0, 10) + '...' : 'N/A'}</p>
                `;
            } else {
                badge.className = 'px-4 py-2 bg-gray-500 text-white rounded-full text-sm';
                badge.innerHTML = '<i class="fas fa-circle mr-1"></i> Offline';
                btn.classList.add('hidden');
                liveIdActual = null;
                info.innerHTML = '<p>No hay LIVE activo</p>';
            }
        });
}

function toggleMonitoreo() {
    if (monitoreoActivo) {
        detenerMonitoreo();
    } else {
        iniciarMonitoreo();
    }
}

function iniciarMonitoreo() {
    fetch('/tiktok-api/api/iniciar-monitoreo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({username: 'donguando'})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            monitoreoActivo = true;
            actualizarBotonMonitoreo();
            cargarMensajesEnVivo();
            alert(data.message);
        }
    });
}

function detenerMonitoreo() {
    fetch('/tiktok-api/api/detener-monitoreo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            monitoreoActivo = false;
            actualizarBotonMonitoreo();
            document.getElementById('aprobadosContainer').innerHTML = '<div class="text-center py-12 text-gray-500">Monitoreo detenido</div>';
            document.getElementById('bloqueadosContainer').innerHTML = '<div class="text-center py-12 text-gray-500">Monitoreo detenido</div>';
            alert(data.message);
        }
    });
}

function actualizarBotonMonitoreo() {
    const btn = document.getElementById('btnMonitoreo');
    if (monitoreoActivo) {
        btn.className = 'px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700';
        btn.innerHTML = '<i class="fas fa-stop mr-1"></i> Detener Monitoreo';
    } else {
        btn.className = 'px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700';
        btn.innerHTML = '<i class="fas fa-play mr-1"></i> Iniciar Monitoreo';
    }
}

function cargarMensajesEnVivo() {
    setInterval(() => {
        if (!monitoreoActivo) return;
        
        // Cargar APROBADOS
        fetch('/tiktok-api/api/live-mensajes-filtrados?status=approved&live_id=' + (liveIdActual || '') + '&pagina=1&por_pagina=50')
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('aprobadosContainer');
                document.getElementById('contadorAprobados').textContent = (data.estadisticas?.total || 0) + ' mensajes';
                
                if (!data.mensajes || data.mensajes.length === 0) {
                    container.innerHTML = '<div class="text-center py-8 text-gray-500">Esperando mensajes...</div>';
                    return;
                }
                
                container.innerHTML = data.mensajes.map(msg => `
                    <div class="flex items-start space-x-2 p-2 bg-green-50 rounded border border-green-100">
                        <span class="font-bold text-sm text-green-700">${msg.sender_username}:</span>
                        <span class="text-sm flex-1">${msg.content}</span>
                        <span class="text-xs text-gray-400">${new Date(msg.message_created_at).toLocaleTimeString()}</span>
                    </div>
                `).join('');
            });
        
        // Cargar BLOQUEADOS
        fetch('/tiktok-api/api/live-mensajes-filtrados?status=blocked&live_id=' + (liveIdActual || '') + '&pagina=1&por_pagina=50')
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('bloqueadosContainer');
                document.getElementById('contadorBloqueados').textContent = (data.estadisticas?.total || 0) + ' bloqueados';
                
                if (!data.mensajes || data.mensajes.length === 0) {
                    container.innerHTML = '<div class="text-center py-8 text-gray-500">No hay mensajes bloqueados</div>';
                    return;
                }
                
                container.innerHTML = data.mensajes.map(msg => `
                    <div class="p-2 bg-red-50 rounded border border-red-100">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-red-800">🚫 ${msg.sender_username}</span>
                            <span class="text-xs text-gray-400">${new Date(msg.message_created_at).toLocaleTimeString()}</span>
                        </div>
                        <p class="text-sm text-gray-700 mt-1">${msg.content}</p>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-xs text-red-500">${msg.moderation_reason || 'Bloqueado por filtro'}</span>
                            <span class="text-xs text-gray-400">ID: ${msg.sender_id ? msg.sender_id.substring(0, 10) + '...' : 'N/A'}</span>
                        </div>
                    </div>
                `).join('');
            });
    }, 2000);
}

function cargarPalabrasBloqueadas() {
    fetch('/tiktok-api/blocked-words/list')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('blockedWordsList');
            if (!data.words || data.words.length === 0) {
                container.innerHTML = '<p class="text-gray-400 text-sm">No hay palabras bloqueadas</p>';
                return;
            }
            container.innerHTML = data.words.map(w => `
                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                    <span class="text-sm font-medium text-red-600">${w}</span>
                    <button onclick="eliminarPalabra('${w}')" class="text-red-400 hover:text-red-600 text-xs">✕</button>
                </div>
            `).join('');
        });
}

function addBlockedWord() {
    const word = document.getElementById('blockedWord').value.trim();
    if (!word) return;
    
    fetch('/tiktok-api/blocked-words/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({word: word})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('blockedWord').value = '';
            cargarPalabrasBloqueadas();
        }
    });
}

function eliminarPalabra(word) {
    fetch('/tiktok-api/blocked-words/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({word: word})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) cargarPalabrasBloqueadas();
    });
}
</script>
@endsection