<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        $stats = [
            'clients' => [
                'total' => Client::count(),
                'actifs' => Client::where('statut', 'actif')->count(),
                'inactifs' => Client::where('statut', 'inactif')->count(),
                'recent_24h' => Client::where('created_at', '>', now()->subDay())->count(),
            ],
            'queue' => [
                'jobs_pending' => DB::table('jobs')->count(),
                'jobs_failed' => DB::table('failed_jobs')->count(),
            ],
            'database' => [
                'source_clients' => DB::connection('source')->table('clients')->count(),
                'target_clients' => DB::table('clients')->count(),
            ],
            'system' => [
                'timestamp' => now()->toIso8601String(),
                'uptime' => $this->getUptime(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    private function getUptime()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return 'N/A (Windows)';
        }
        
        $uptime = shell_exec('uptime -p');
        return trim($uptime);
    }

    public function health()
    {
        $checks = [
            'database_target' => $this->checkDatabase('mysql'),
            'database_source' => $this->checkDatabase('source'),
            'queue_worker' => $this->checkQueueWorker(),
        ];

        $healthy = !in_array(false, $checks);

        return response()->json([
            'success' => true,
            'healthy' => $healthy,
            'checks' => $checks,
            'timestamp' => now()->toIso8601String()
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase($connection)
    {
        try {
            DB::connection($connection)->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkQueueWorker()
    {
        // Vérifier si des jobs récents ont été traités
        $recentJobs = DB::table('jobs')
            ->where('available_at', '>', now()->subMinutes(5)->timestamp)
            ->count();
        
        return $recentJobs > 0 || DB::table('jobs')->count() === 0;
    }
}