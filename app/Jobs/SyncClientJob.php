<?php

namespace App\Jobs;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncClientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $clientData;
    public $action;

    public function __construct(array $clientData, string $action = 'sync')
    {
        $this->clientData = $clientData;
        $this->action = $action;
    }

    public function handle()
    {
        try {
            DB::beginTransaction();

            switch ($this->action) {
                case 'create':
                case 'sync':
                case 'update':
                    Client::updateOrCreate(
                        ['email' => $this->clientData['email']],
                        $this->clientData
                    );
                    Log::info("Client synchronisé", ['email' => $this->clientData['email']]);
                    break;

                case 'delete':
                    Client::where('email', $this->clientData['email'])->delete();
                    Log::info("Client supprimé", ['email' => $this->clientData['email']]);
                    break;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur de synchronisation: " . $e->getMessage());
            throw $e;
        }
    }
}