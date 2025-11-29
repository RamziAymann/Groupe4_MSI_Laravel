<?php

namespace App\Services;

use App\Jobs\SyncClientJob;
use Illuminate\Support\Facades\Log;

class QueueService
{
    public function publishMessage(array $data, string $action = 'create')
    {
        try {
            SyncClientJob::dispatch($data, $action);
            
            Log::info("Message ajouté à la queue", ['action' => $action, 'email' => $data['email'] ?? 'N/A']);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'ajout à la queue: " . $e->getMessage());
            return false;
        }
    }
}