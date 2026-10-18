<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // UI aplikasi memakai Bootstrap 5; default paginator Laravel adalah Tailwind.
        Paginator::useBootstrapFive();

        $this->ensurePostgresSchemaExists();
    }

    /**
     * Pastikan schema PostgreSQL yang ditentukan di konfigurasi/env sudah ada
     * sebelum migrations atau query dijalankan di console/deployment.
     */
    protected function ensurePostgresSchemaExists(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        try {
            $defaultConnection = config('database.default');
            $driver = config("database.connections.{$defaultConnection}.driver");

            if ($driver === 'pgsql') {
                $searchPath = config("database.connections.{$defaultConnection}.search_path")
                    ?? config("database.connections.{$defaultConnection}.schema")
                    ?? 'public';

                $schemas = array_map(function ($s) {
                    return trim($s, "'\" ");
                }, explode(',', $searchPath));

                $targetSchema = $schemas[0] ?? 'public';

                if ($targetSchema !== 'public' && preg_match('/^[a-zA-Z0-9_]+$/', $targetSchema)) {
                    DB::connection($defaultConnection)->statement("CREATE SCHEMA IF NOT EXISTS \"{$targetSchema}\"");
                }
            }
        } catch (\Throwable $e) {
            // Mengabaikan error koneksi saat bootstrap awal (misal saat key:generate atau DB offline)
        }
    }
}
