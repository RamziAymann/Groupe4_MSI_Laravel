<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ETLService;

class RunETLProcess extends Command
{
   protected $signature = 'etl:run';
    protected $description = 'Exécute le processus ETL de synchronisation';

    public function handle(ETLService $etlService)
    {
        $this->info('Démarrage du processus ETL...');
        
        $count = $etlService->runETLProcess();
        
        $this->info("Processus ETL terminé. {$count} clients ajoutés à la queue.");
        
        return 0;
    }
}
