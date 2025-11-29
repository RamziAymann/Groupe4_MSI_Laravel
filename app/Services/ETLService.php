<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ETLService
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function extractFromSource()
    {
        try {
            $clients = DB::connection('source')
                ->table('clients')
                ->where('updated_at', '>', now()->subMinutes(5))
                ->get();

            Log::info("Extraction de {$clients->count()} clients depuis la source");

            return $clients;
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'extraction: " . $e->getMessage());
            return collect();
        }
    }

    public function transform($rawData)
    {
        return $rawData->map(function ($client) {
            return [
                'nom' => strtoupper($client->nom),
                'prenom' => ucfirst(strtolower($client->prenom)),
                'email' => strtolower($client->email),
                'telephone' => $this->formatTelephone($client->telephone ?? null),
                'adresse' => $client->adresse ?? null,
                'ville' => $client->ville ? ucfirst($client->ville) : null,
                'code_postal' => $client->code_postal ?? null,
                'date_naissance' => $client->date_naissance ?? null,
                'statut' => $client->statut ?? 'actif',
            ];
        });
    }

    public function load($transformedData)
    {
        $count = 0;
        foreach ($transformedData as $clientData) {
            try {
                $this->queueService->publishMessage($clientData, 'sync');
                $count++;
            } catch (\Exception $e) {
                Log::error("Erreur lors du chargement: " . $e->getMessage());
            }
        }
        return $count;
    }

    protected function formatTelephone($telephone)
    {
        if (!$telephone) return null;
        
        $telephone = preg_replace('/[^0-9]/', '', $telephone);
        
        if (strlen($telephone) === 10) {
            return substr($telephone, 0, 2) . ' ' . 
                   substr($telephone, 2, 2) . ' ' . 
                   substr($telephone, 4, 2) . ' ' . 
                   substr($telephone, 6, 2) . ' ' . 
                   substr($telephone, 8, 2);
        }
        
        return $telephone;
    }

    public function runETLProcess()
    {
        $rawData = $this->extractFromSource();
        $transformedData = $this->transform($rawData);
        $count = $this->load($transformedData);
        
        return $count;
    }
}