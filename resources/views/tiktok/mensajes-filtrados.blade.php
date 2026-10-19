@extends('layouts.tiktok')

@section('title', 'Mensajes Filtrados - TikTok ERP')

@section('content')
<div class="p-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-filter text-red-500 mr-2"></i>Mensajes Filtrados
        </h1>
        <div class="flex gap-3">
            <button onclick="actualizarMensajes()" class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600">
                <i class="fas fa-sync-alt mr-1"></i> Actualizar
            </button>
            <button onclick="limpiarRegistros()" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600">
                <i class="fas fa-trash mr-1"></i> Limpiar registros
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Buscador por Live -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sesión LIVE</label>
                <select id="filtroLive" onchange="filtrarMensajes()" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Todos los lives</option>
                </select>
            </div>
            
            <!-- Buscador por Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select id="filtroEstado" onchange="filtrarMensajes()" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Todos</option>
                    <option value="blocked">🚫 Bloqueados</option>
                    <option value="approved">✅ Aprobados</option>
                    <option value="order">📦 Pedidos</option>
                </select>
            </div>
            
            <!-- Buscador por Fecha -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                <input type="date" id="filtroDesde" onchange="filtrarMensajes()" class="w-full border rounded-lg px-3 py-2">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                <input type="date" id="filtroHasta" onchange="filtrarMensajes()" class="w-full border rounded-lg px-3 py-2">
            </div>
        </div>
        
        <!-- Buscador por palabra -->
        <div class="mt-3">
            <input type="text" id="filtroBusqueda" onkeyup="filtrarMensajes()" 
                   placeholder="🔍 Buscar por usuario o contenido..." 
                   class="w-full border rounded-lg px-4 py-2">
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6" id="estadisticas">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-gray-500 text-sm">Total Mensajes</p>
            <p class="text-2xl font-bold text-gray-800" id="totalMensajes">0</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-gray-500 text-sm">✅ Aprobados</p>
            <p class="text-2xl font-bold text-green-600" id="totalAprobados">0</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-gray-500 text-sm">🚫 Bloqueados</p>
            <p class="text-2xl font-bold text-red-600" id="totalBloqueados">0</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-gray-500 text-sm">📦 Pedidos</p>
            <p class="text-2xl font-bold text-purple-600" id="totalPedidos">0</p>
        </div>
    </div>

    <!-- Tabla de Mensajes -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mensaje</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Live</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaMensajes" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Cargando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="px-4 py-3 border-t flex justify-between items-center" id="paginacion">
            <p class="text-sm text-gray-500" id="infoPagina"></p>
            <div class="flex gap-2" id="botonesPagina"></div>
        </div>
    </div>
</div>

<script>
let paginaActual = 1;
let mensajesData = [];

// Cargar sesiones de LIVE al entrar
document.addEventListener('DOMContentLoaded', function() {
    cargarSesionesLive();
    cargarMensajes();
});

function cargarSesionesLive() {
    fetch('/tiktok-api/api/live-sessions')
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById('filtroLive');
            data.sessions.forEach(session => {
                const option = document.createElement('option');
                option.value = session.live_id;
                option.text = `LIVE ${session.live_id.substring(0, 8)}... (${new Date(session.started_at).toLocaleString()})`;
                select.appendChild(option);
            });
        });
}

function cargarMensajes(pagina = 1) {
    paginaActual = pagina;
    
    const filtros = {
        live_id: document.getElementById('filtroLive').value,
        status: document.getElementById('filtroEstado').value,
        desde: document.getElementById('filtroDesde').value,
        hasta: document.getElementById('filtroHasta').value,
        busqueda: document.getElementById('filtroBusqueda').value,
        pagina: pagina
    };
    
    const params = new URLSearchParams(filtros);
    
    fetch(`/tiktok-api/api/live-mensajes-filtrados?${params}`)
        .then(r => r.json())
        .then(data => {
            mensajesData = data.mensajes;
            renderTabla(data.mensajes);
            renderEstadisticas(data.estadisticas);
            renderPaginacion(data.paginacion);
        });
}

function renderTabla(mensajes) {
    const tbody = document.getElementById('tablaMensajes');
    
    if (mensajes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-2xl mb-2"></i>
                    <p>No hay mensajes</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = mensajes.map(msg => `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-gray-500 text-xs"></i>
                    </div>
                    <div>
                        <p class="font-medium text-sm text-gray-800">${msg.sender_username}</p>
                        <p class="text-xs text-gray-400">ID: ${msg.sender_id.substring(0, 10)}...</p>
                    </div>
                </div>
            </td>
            <td class="px-4 py-3">
                <p class="text-sm text-gray-800 max-w-xs truncate">${msg.content}</p>
                ${msg.moderation_reason ? `<p class="text-xs text-red-500 mt-1">${msg.moderation_reason}</p>` : ''}
            </td>
            <td class="px-4 py-3">
                ${getStatusBadge(msg.status, msg.is_order)}
            </td>
            <td class="px-4 py-3">
                <span class="text-xs text-gray-500">${msg.live_id ? msg.live_id.substring(0, 10) + '...' : 'N/A'}</span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-500">
                ${new Date(msg.message_created_at).toLocaleString()}
            </td>
            <td class="px-4 py-3">
                <button onclick="eliminarMensaje(${msg.id})" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-trash text-sm"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function getStatusBadge(status, isOrder) {
    if (isOrder) return '<span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full">📦 Pedido</span>';
    if (status === 'blocked') return '<span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full">🚫 Bloqueado</span>';
    return '<span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">✅ Aprobado</span>';
}

function renderEstadisticas(stats) {
    document.getElementById('totalMensajes').textContent = stats.total || 0;
    document.getElementById('totalAprobados').textContent = stats.aprobados || 0;
    document.getElementById('totalBloqueados').textContent = stats.bloqueados || 0;
    document.getElementById('totalPedidos').textContent = stats.pedidos || 0;
}

function renderPaginacion(paginacion) {
    document.getElementById('infoPagina').textContent = 
        `Mostrando ${paginacion.desde}-${paginacion.hasta} de ${paginacion.total}`;
    
    const botones = document.getElementById('botonesPagina');
    botones.innerHTML = '';
    
    if (paginacion.pagina_actual > 1) {
        botones.innerHTML += `<button onclick="cargarMensajes(${paginacion.pagina_actual - 1})" class="px-3 py-1 border rounded text-sm">← Anterior</button>`;
    }
    
    if (paginacion.pagina_actual < paginacion.ultima_pagina) {
        botones.innerHTML += `<button onclick="cargarMensajes(${paginacion.pagina_actual + 1})" class="px-3 py-1 border rounded text-sm">Siguiente →</button>`;
    }
}

function filtrarMensajes() {
    cargarMensajes(1);
}

function eliminarMensaje(id) {
    if (!confirm('¿Eliminar este mensaje?')) return;
    
    fetch(`/tiktok-api/api/live-mensaje/${id}`, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''}
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) cargarMensajes(paginaActual);
    });
}

function actualizarMensajes() {
    cargarMensajes(paginaActual);
}

function limpiarRegistros() {
    if (!confirm('¿Eliminar TODOS los mensajes? Esta acción no se puede deshacer.')) return;
    
    const filtros = {
        live_id: document.getElementById('filtroLive').value,
    };
    
    fetch('/tiktok-api/api/live-mensajes/limpiar', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify(filtros)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            cargarMensajes(1);
        }
    });
}
</script>
@endsection
