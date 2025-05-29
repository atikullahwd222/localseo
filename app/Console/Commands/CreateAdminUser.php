<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {--name= : The admin user name} 
                                         {--email= : The admin user email}
                                         {--password= : The admin user password}
                                         {--force : Force creation even if admin users exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a default admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating admin user...');
        
        // Check if admin role exists
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->error('Admin role not found. Please run migrations first.');
            return 1;
        }
        
        // Check if admin users already exist
        $existingAdmins = User::where('role_id', $adminRole->id)->count();
        
        if ($existingAdmins > 0 && !$this->option('force')) {
            $this->warn('Admin users already exist. Use --force to create another admin.');
            
            if (!$this->confirm('Do you want to continue anyway?')) {
                return 0;
            }
        }
        
        // Get name
        $name = $this->option('name');
        if (!$name) {
            $name = $this->ask('Enter admin name', 'Admin User');
        }
        
        // Get email
        $email = $this->option('email');
        if (!$email) {
            $email = $this->ask('Enter admin email', 'admin@example.com');
        }
        
        // Get password
        $password = $this->option('password');
        if (!$password) {
            $password = $this->secret('Enter admin password (min 8 characters)');
            
            if (strlen($password) < 8) {
                $this->error('Password must be at least 8 characters long.');
                return 1;
            }
            
            $confirmPassword = $this->secret('Confirm admin password');
            
            if ($password !== $confirmPassword) {
                $this->error('Passwords do not match.');
                return 1;
            }
        }
        
        // Validate the inputs
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);
        
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }
        
        // Create admin user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => $adminRole->id,
            'status' => 'active',
            'photo' => 'assets/img/avatar/default.png',
        ]);
        
        $this->info("Admin user created successfully!");
        $this->table(['Name', 'Email', 'Role'], [
            [$user->name, $user->email, 'Admin']
        ]);
        
        return 0;
    }
}
