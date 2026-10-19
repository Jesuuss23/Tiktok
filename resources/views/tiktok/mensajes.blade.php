@extends('layouts.tiktok')

@section('title', 'Mensajes - TikTok ERP')

@section('content')
<div class="p-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-comments text-blue-500 mr-2"></i>Mensajes
        </h1>
        <button onclick="cargarMensajes()" class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600">
            <i class="fas fa-sync-alt mr-1"></i> Actualizar
        </button>
    </div>

    <!-- Lista de Mensajes -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div id="messagesList" class="divide-y divide-gray-100">
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p>Cargando mensajes...</p>
            </div>
        </div>
    </div>
</div>

<script>
// Cargar mensajes al entrar
document.addEventListener('DOMContentLoaded', function() {
    cargarMensajes();
});

function cargarMensajes() {
    fetch('/tiktok-api/api/mensajes')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('messagesList');
            
            if (!data.messages || data.messages.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3"></i>
                        <p>No hay mensajes disponibles</p>
                        <p class="text-sm mt-1">Los mensajes de TikTok aparecerán aquí</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = data.messages.map(msg => `
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-start space-x-3">
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-gray-500"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="font-medium text-gray-800">@${msg.sender_username}</p>
                                <span class="text-xs text-gray-400">${msg.created_at}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">${msg.content}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            document.getElementById('messagesList').innerHTML = `
                <div class="text-center py-12 text-red-500">
                    <i class="fas fa-exclamation-circle text-2xl mb-2"></i>
                    <p>Error al cargar mensajes</p>
                </div>
            `;
        });
}
</script>
@endsection