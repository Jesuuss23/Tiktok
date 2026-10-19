@extends('layouts.tiktok')

@section('title', 'IA Moderación - TikTok ERP')

@section('content')
<div class="p-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-robot text-purple-500 mr-2"></i>IA Moderación
        </h1>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Formulario para agregar palabras -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4">Agregar Palabra Bloqueada</h3>
            
            <form onsubmit="agregarPalabra(event)" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Palabra o frase</label>
                    <input type="text" id="newWord" required 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="Ej: estafa, spam, etc.">
                </div>
                
                <button type="submit" 
                        class="w-full bg-purple-600 text-white rounded-lg px-4 py-2 hover:bg-purple-700 transition">
                    <i class="fas fa-plus mr-2"></i>Agregar
                </button>
            </form>

            <div class="mt-4 p-3 bg-yellow-50 rounded-lg">
                <p class="text-xs text-yellow-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Los mensajes que contengan estas palabras serán bloqueados automáticamente.
                </p>
            </div>
        </div>

        <!-- Lista de palabras bloqueadas -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4">Palabras Bloqueadas</h3>
            
            <div id="blockedWordsList" class="space-y-2">
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-spinner fa-spin text-xl mb-2"></i>
                    <p>Cargando lista...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cargar palabras al entrar
document.addEventListener('DOMContentLoaded', function() {
    cargarPalabras();
});

function cargarPalabras() {
    fetch('/tiktok-api/blocked-words/list')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('blockedWordsList');
            
            if (!data.words || data.words.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-shield-alt text-3xl mb-2"></i>
                        <p>No hay palabras bloqueadas</p>
                        <p class="text-sm">Agrega palabras para empezar a filtrar</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = data.words.map((word, index) => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg group">
                    <div class="flex items-center space-x-3">
                        <span class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-600 text-sm font-bold">
                            ${index + 1}
                        </span>
                        <span class="font-medium text-gray-800">${word}</span>
                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded-full">Bloqueada</span>
                    </div>
                    <button onclick="eliminarPalabra('${word}')" 
                            class="text-gray-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `).join('');
        });
}

function agregarPalabra(event) {
    event.preventDefault();
    
    const word = document.getElementById('newWord').value.trim();
    if (!word) return;
    
    fetch('/tiktok-api/blocked-words/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ word: word })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('newWord').value = '';
            cargarPalabras();
        } else {
            alert('Error: ' + (data.error || 'No se pudo agregar'));
        }
    });
}

function eliminarPalabra(word) {
    if (!confirm('¿Eliminar palabra "' + word + '"?')) return;
    
    fetch('/tiktok-api/blocked-words/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ word: word })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarPalabras();
        }
    });
}
</script>
@endsection