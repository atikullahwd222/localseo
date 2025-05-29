<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:install {--force : Force installation even if already installed} 
                                          {--seed : Seed the database with test data}
                                          {--admin-email= : Admin email}
                                          {--admin-name= : Admin name}
                                          {--admin-password= : Admin password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and set up the LocalSEO system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║               LocalSEO Installer                 ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->newLine();
        
        if (file_exists(storage_path('installed.json')) && !$this->option('force')) {
            $this->warn('System appears to be already installed!');
            
            if (!$this->confirm('Do you want to reinstall anyway? This may cause data loss.', false)) {
                $this->info('Installation aborted.');
                return 0;
            }
        }
        
        $steps = [
            'Checking environment' => function() {
                $this->checkRequirements();
            },
            'Setting up database' => function() {
                $this->setupDatabase();
            },
            'Creating admin user' => function() {
                $this->createAdminUser();
            },
            'Syncing existing users' => function() {
                $this->syncUsers();
            },
            'Optimizing application' => function() {
                $this->optimizeApp();
            }
        ];
        
        $bar = $this->output->createProgressBar(count($steps));
        $bar->start();
        
        foreach ($steps as $message => $step) {
            $this->newLine(2);
            $this->info("[$message]");
            
            try {
                $step();
                $bar->advance();
            } catch (\Exception $e) {
                $this->newLine(2);
                $this->error("Error during '$message': " . $e->getMessage());
                return 1;
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Mark as installed
        File::put(storage_path('installed.json'), json_encode([
            'installed_at' => now()->toDateTimeString(),
            'version' => config('app.version', '1.0.0')
        ]));
        
        $this->info('╔═════════════════════════════════════════════════════════════════╗');
        $this->info('║                Installation completed successfully!              ║');
        $this->info('╚═════════════════════════════════════════════════════════════════╝');
        $this->newLine();
        $this->info('You can now log in to your LocalSEO system.');
        
        return 0;
    }
    
    protected function checkRequirements()
    {
        $this->line('✓ PHP version: ' . PHP_VERSION);
        $this->line('✓ Directory permissions');
        
        $writeableDirs = [
            'storage/app',
            'storage/framework',
            'storage/logs',
            'bootstrap/cache'
        ];
        
        foreach ($writeableDirs as $dir) {
            if (!is_writable(base_path($dir))) {
                throw new \Exception("Directory '$dir' is not writable. Please check permissions.");
            }
        }
        
        $this->line('✓ Database connection');
        
        try {
            \DB::connection()->getPdo();
            $database = \DB::connection()->getDatabaseName();
            $this->line("   Connected to database: $database");
        } catch (\Exception $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    protected function setupDatabase()
    {
        $this->line('Running migrations...');
        
        if ($this->option('force')) {
            Artisan::call('migrate:fresh', [
                '--force' => true
            ]);
        } else {
            Artisan::call('migrate', [
                '--force' => true
            ]);
        }
        
        $this->line(Artisan::output());
        
        if ($this->option('seed')) {
            $this->line('Seeding database with test data...');
            
            Artisan::call('db:seed', [
                '--force' => true
            ]);
            
            $this->line(Artisan::output());
        }
    }
    
    protected function createAdminUser()
    {
        $this->line('Creating admin user...');
        
        $options = ['--force' => true];
        
        if ($this->option('admin-name')) {
            $options['--name'] = $this->option('admin-name');
        }
        
        if ($this->option('admin-email')) {
            $options['--email'] = $this->option('admin-email');
        }
        
        if ($this->option('admin-password')) {
            $options['--password'] = $this->option('admin-password');
        }
        
        Artisan::call('admin:create', $options);
        $this->line(Artisan::output());
    }
    
    protected function syncUsers()
    {
        $this->line('Synchronizing user roles...');
        
        Artisan::call('users:sync-roles', [
            '--force' => true
        ]);
        
        $this->line(Artisan::output());
    }
    
    protected function optimizeApp()
    {
        $this->line('Clearing cache...');
        
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        
        $this->line('Optimizing...');
        
        Artisan::call('optimize');
        Artisan::call('storage:link', ['--force' => true]);
    }
}
