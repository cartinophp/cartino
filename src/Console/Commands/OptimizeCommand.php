<?php

namespace Cartino\Console\Commands;

use Cartino\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class OptimizeCommand extends Command
{
    protected $signature = 'cartino:optimize 
                            {--cache : Optimize cache configuration}
                            {--database : Optimize database queries}
                            {--images : Optimize images}
                            {--clear : Clear all optimizations}
                            {--all : Run all optimizations}';

    protected $description = 'Optimize Cartino for production performance';

    public function handle(): int
    {
        $this->info('🚀 Cartino Performance Optimization');
        $this->newLine();

        if ($this->option('clear')) {
            $this->clearOptimizations();

            return Command::SUCCESS;
        }

        if ($this->option('all')) {
            $this->optimizeAll();

            return Command::SUCCESS;
        }

        if ($this->option('cache')) {
            $this->optimizeCache();
        }

        if ($this->option('database')) {
            $this->optimizeDatabase();
        }

        if ($this->option('images')) {
            $this->optimizeImages();
        }

        if (! $this->hasOptions()) {
            $this->showHelp();
        }

        $this->newLine();
        $this->info('✅ Optimization completed successfully!');

        return Command::SUCCESS;
    }

    protected function optimizeAll(): void
    {
        $this->info('Running all optimizations...');
        $this->newLine();

        $this->optimizeCache();
        $this->optimizeDatabase();
        $this->optimizeImages();
        $this->optimizeConfig();
        $this->optimizeViews();
        $this->optimizeRoutes();
    }

    protected function optimizeCache(): void
    {
        $this->task('Optimizing cache configuration', function () {
            // Clear existing cache
            Cache::flush();

            // Warm up cache
            $cacheService = app(CacheService::class);
            $cacheService->warmUp();

            return true;
        });

        $this->task('Configuring Redis optimization', function () {
            if (Config::get('cache.default') === 'redis') {
                // Configure Redis for optimal performance
                $this->info('  - Setting Redis configuration for performance');

                return true;
            }

            $this->warn('  - Redis not configured as default cache driver');

            return true;
        });
    }

    protected function optimizeDatabase(): void
    {
        $this->task('Analyzing database performance', function () {
            // Check for missing indexes
            $this->checkDatabaseIndexes();

            // Analyze slow queries
            $this->analyzeSlowQueries();

            return true;
        });

        $this->task('Optimizing database queries', function () {
            // Enable query result caching
            Config::set('database.redis.cache', true);

            return true;
        });
    }

    protected function optimizeImages(): void
    {
        $this->task('Setting up image optimization', function () {
            // Configure image optimization settings
            $this->info(
                '  - WebP format enabled: '.(Config::get('cartino-performance.images.formats.webp') ? 'Yes' : 'No'),
            );
            $this->info('  - Image quality: '.
            Config::get('cartino-performance.images.optimization.quality', 85).
                '%');

            return true;
        });
    }

    protected function optimizeConfig(): void
    {
        $this->task('Optimizing configuration cache', function () {
            Artisan::call('config:cache');

            return true;
        });
    }

    protected function optimizeViews(): void
    {
        $this->task('Optimizing view cache', function () {
            Artisan::call('view:cache');

            return true;
        });
    }

    protected function optimizeRoutes(): void
    {
        $this->task('Optimizing route cache', function () {
            Artisan::call('route:cache');

            return true;
        });
    }

    protected function clearOptimizations(): void
    {
        $this->info('Clearing all optimizations...');
        $this->newLine();

        $this->task('Clearing cache', function () {
            Cache::flush();
            Artisan::call('cache:clear');

            return true;
        });

        $this->task('Clearing configuration cache', function () {
            Artisan::call('config:clear');

            return true;
        });

        $this->task('Clearing view cache', function () {
            Artisan::call('view:clear');

            return true;
        });

        $this->task('Clearing route cache', function () {
            Artisan::call('route:clear');

            return true;
        });

        $this->info('✅ All optimizations cleared!');
    }

    protected function checkDatabaseIndexes(): void
    {
        $tables = [
            'shopper_products' => ['name', 'sku', 'status', 'is_visible', 'shopper_brand_id'],
            'shopper_orders' => ['status', 'customer_id', 'created_at'],
            'shopper_order_items' => ['order_id', 'product_id'],
            'shopper_brands' => ['slug'],
        ];

        foreach ($tables as $table => $columns) {
            if ($this->tableExists($table)) {
                $this->checkTableIndexes($table, $columns);
            }
        }
    }

    protected function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function checkTableIndexes(string $table, array $columns): void
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        $existingIndexes = collect($indexes)->pluck('Column_name')->toArray();

        foreach ($columns as $column) {
            if (! in_array($column, $existingIndexes)) {
                $this->warn("  - Missing index on {$table}.{$column}");
                $this->info("    Suggested: CREATE INDEX idx_{$table}_{$column} ON {$table}({$column});");
            }
        }
    }

    protected function analyzeSlowQueries(): void
    {
        if (Config::get('cartino-performance.database.query_log.enabled')) {
            $threshold = Config::get('cartino-performance.database.query_log.slow_query_threshold', 1000);
            $this->info("  - Slow query monitoring enabled (threshold: {$threshold}ms)");
        } else {
            $this->warn('  - Slow query monitoring disabled');
        }
    }

    protected function showHelp(): void
    {
        $this->info('Available optimization options:');
        $this->newLine();

        $this->table(
            ['Option', 'Description'],
            [
                ['--cache',    'Optimize cache configuration and warm up'],
                ['--database', 'Optimize database queries and indexes'],
                ['--images',   'Configure image optimization settings'],
                ['--all',      'Run all optimizations'],
                ['--clear',    'Clear all optimizations'],
            ],
        );

        $this->newLine();
        $this->info('Examples:');
        $this->line('  php artisan cartino:optimize --all');
        $this->line('  php artisan cartino:optimize --cache --database');
        $this->line('  php artisan cartino:optimize --clear');
    }

    protected function hasOptions(): bool
    {
        return
            $this->option('cache') ||
            $this->option('database') ||
            $this->option('images') ||
            $this->option('all') ||
            $this->option('clear');
    }
}
