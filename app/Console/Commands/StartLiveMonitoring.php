<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StartLiveMonitoring extends Command
{
    protected $signature = 'tiktok:monitor 
                            {username : El @username del LIVE a monitorear}
                            {--api-key= : La API key para enviar datos a Laravel}';

    protected $description = 'Inicia el monitoreo de un LIVE de TikTok';

    public function handle()
    {
        $username = $this->argument('username');
        $apiKey = $this->option('api-key') ?? config('app.tiktok_api_key');
        $laravelUrl = config('app.url') . '/api/live-message';

        $this->info("🔍 Iniciando monitoreo para @{$username}...");
        $this->info("📡 Endpoint: {$laravelUrl}");

        $scriptPath = base_path('scripts/bridge_live.py');
        
        // Verificar que el script existe
        if (!file_exists($scriptPath)) {
            $this->error("❌ Script no encontrado: {$scriptPath}");
            return 1;
        }

        // Activar entorno virtual y ejecutar script
        $venvPath = '/var/www/tiktok-erp-bridge/venv';
        $pythonPath = "{$venvPath}/bin/python";
        
        $process = new Process([
            $pythonPath,
            $scriptPath,
            '--username', $username,
            '--url', $laravelUrl,
            '--api-key', $apiKey,
        ]);

        $process->setTimeout(0); // Sin timeout, un LIVE puede durar horas
        $process->setWorkingDirectory(base_path());

        $this->info("▶️  Ejecutando script...");
        
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->error($buffer);
            } else {
                $this->line($buffer);
            }
        });

        return $process->getExitCode();
    }
}
