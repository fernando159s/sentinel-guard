<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EmpresaSetupCommand extends Command
{
    protected $signature = 'empresa:setup';

    protected $description = 'Configurar una nueva empresa con admin, políticas, templates y checklists';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║     SecuriForm — Setup de Empresa        ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('');

        $seeder = new \Database\Seeders\EmpresaSetupSeeder;
        $seeder->setCommand($this);
        $seeder->run();

        return Command::SUCCESS;
    }
}
