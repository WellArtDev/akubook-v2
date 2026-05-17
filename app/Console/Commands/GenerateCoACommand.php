<?php

namespace App\Console\Commands;

use App\Models\Account;
use Database\Seeders\CoA\DistributorCoASeeder;
use Database\Seeders\CoA\GeneralCoASeeder;
use Database\Seeders\CoA\RetailCoASeeder;
use Database\Seeders\CoA\ServiceCoASeeder;
use Illuminate\Console\Command;

class GenerateCoACommand extends Command
{
    protected $signature = 'coa:generate {industry : Industry type (general|distributor|retail|service)} {--force : Skip confirmation}';

    protected $description = 'Generate Chart of Accounts template for specific industry';

    public function handle(): int
    {
        $industry = $this->argument('industry');
        $force = $this->option('force');

        // Validate industry
        $validIndustries = ['general', 'distributor', 'retail', 'service'];
        if (!in_array($industry, $validIndustries)) {
            $this->error("Invalid industry. Choose: " . implode(', ', $validIndustries));
            return 1;
        }

        // Check existing accounts
        $existingCount = Account::count();
        if ($existingCount > 0 && !$force) {
            if (!$this->confirm("Found {$existingCount} existing accounts. Delete and regenerate?")) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Delete existing accounts
        if ($existingCount > 0) {
            // Truncate table to reset auto-increment and remove all data
            \DB::statement('TRUNCATE TABLE accounts RESTART IDENTITY CASCADE');
            $this->info("Deleted {$existingCount} existing accounts.");
        }

        // Run appropriate seeder
        $seederClass = match($industry) {
            'general' => GeneralCoASeeder::class,
            'distributor' => DistributorCoASeeder::class,
            'retail' => RetailCoASeeder::class,
            'service' => ServiceCoASeeder::class,
        };

        $this->call('db:seed', ['--class' => $seederClass]);

        // Display summary
        $newCount = Account::count();
        $headerCount = Account::headers()->count();
        $detailCount = Account::details()->count();

        $this->info("\n✅ Chart of Accounts generated successfully!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Accounts', $newCount],
                ['Header Accounts', $headerCount],
                ['Detail Accounts', $detailCount],
                ['Industry Template', ucfirst($industry)],
            ]
        );

        return 0;
    }
}
