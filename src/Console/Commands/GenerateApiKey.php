<?php

declare(strict_types=1);

namespace Cartino\Console\Commands;

use Carbon\Carbon;
use Cartino\Models\ApiKey;
use Illuminate\Console\Command;

class GenerateApiKey extends Command
{
    protected $signature = 'cartino:api-key:generate 
                            {name : Nome descrittivo della API key}
                            {--type=full_access : Tipo di key (full_access, read_only, custom)}
                            {--expires= : Data di scadenza (Y-m-d)}
                            {--description= : Descrizione opzionale}';

    protected $description = 'Genera una nuova API key per Cartino';

    public function handle(): int
    {
        $name = $this->argument('name');
        $type = $this->option('type');
        $expiresAt = $this->option('expires');
        $description = $this->option('description');

        // Validazione tipo
        if (! in_array($type, ['full_access', 'read_only', 'custom'])) {
            $this->error('Tipo non valido. Usa: full_access, read_only, o custom');

            return self::FAILURE;
        }

        try {
            // Genera la chiave
            $plainKey = ApiKey::generate();

            // Crea il record
            $apiKey = ApiKey::create([
                'name' => $name,
                'key' => ApiKey::hash($plainKey),
                'type' => $type,
                'description' => $description,
                'expires_at' => $expiresAt ? Carbon::parse($expiresAt) : null,
                'is_active' => true,
                'created_by' => null, // CLI generation
            ]);

            $this->newLine();
            $this->info('✓ API Key creata con successo!');
            $this->newLine();

            $this->line('┌─────────────────────────────────────────────────────────────────┐');
            $this->line('│                                                                 │');
            $this->line('│  '.str_pad('ID: '.$apiKey->id, 61).'  │');
            $this->line('│  '.str_pad('Nome: '.$apiKey->name, 61).'  │');
            $this->line('│  '.str_pad('Tipo: '.$apiKey->type, 61).'  │');
            $this->line('│                                                                 │');
            $this->line('│  '.str_pad('API Key:', 61).'  │');
            $this->line('│  '.$this->comment(str_pad($plainKey, 61)).'  │');
            $this->line('│                                                                 │');
            $this->line('└─────────────────────────────────────────────────────────────────┘');

            $this->newLine();
            $this->warn('⚠️  ATTENZIONE: Salva questa chiave in un posto sicuro!');
            $this->warn('   Non sarà più visibile dopo questo momento.');
            $this->newLine();

            $this->line('Aggiungi al tuo .env:');
            $this->info("CARTINO_API_KEY=\"{$plainKey}\"");
            $this->newLine();

            $this->line('Oppure usala direttamente nelle richieste HTTP:');
            $this->info("curl -H \"X-API-Key: {$plainKey}\" https://your-domain.com/api/endpoint");
            $this->newLine();

            if ($expiresAt) {
                $this->warn("Scadenza: {$expiresAt}");
                $this->newLine();
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Errore durante la creazione della API key: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
