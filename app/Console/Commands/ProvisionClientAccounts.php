<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\ClientAccountService;
use Illuminate\Console\Command;

class ProvisionClientAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:provision-accounts
                             {--dry-run : Lister les clients qui seraient provisionnés sans rien modifier}
                             {--force : Re-provisionner (réinitialiser le mot de passe) même les clients qui ont déjà un compte}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provisionne un compte portail (mot de passe généré) pour les clients existants qui n\'en ont pas encore.';

    /**
     * Execute the console command.
     */
    public function handle(ClientAccountService $service): int
    {
        $query = Client::where(function ($q) {
            $q->where('valeursupprimee', '!=', 1)->orWhereNull('valeursupprimee');
        });

        if (! $this->option('force')) {
            $query->whereNull('password');
        }

        $clients = $query->get();

        if ($this->option('dry-run')) {
            $this->table(
                ['id', 'nom_prenoms', 'contact', 'email'],
                $clients->map->only(['id', 'nom_prenoms', 'contact', 'email'])->toArray()
            );
            $this->info("{$clients->count()} client(s) seraient provisionnés.");

            return self::SUCCESS;
        }

        $rows = [];
        $bar = $this->output->createProgressBar($clients->count());
        foreach ($clients as $client) {
            $plainPassword = $service->provision($client);
            $rows[] = [$client->nom_prenoms, $client->contact, $client->email ?? '—', $plainPassword];
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        if (! empty($rows)) {
            $this->table(['nom_prenoms', 'contact', 'email', 'mot de passe temporaire'], $rows);
            $this->warn('Communiquez ces identifiants aux clients concernés — à changer obligatoirement à la première connexion.');
        }

        $this->info("{$clients->count()} compte(s) client provisionné(s).");

        return self::SUCCESS;
    }
}
