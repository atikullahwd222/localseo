<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Setting key');
            $table->text('value')->nullable()->comment('Setting value');
            $table->string('group')->default('general')->comment('Setting group');
            $table->text('description')->nullable()->comment('Setting description');
            $table->boolean('is_public')->default(false)->comment('Whether setting is public');
            $table->timestamps();
        });
        
        // Insert default rating settings
        $this->insertDefaultSettings();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
    
    /**
     * Insert default settings
     */
    private function insertDefaultSettings()
    {
        $settings = [
            [
                'key' => 'rating_scale',
                'value' => '10',
                'group' => 'rating',
                'description' => 'The maximum value for normalized ratings',
                'is_public' => true
            ],
            [
                'key' => 'rating_threshold_high',
                'value' => '7',
                'group' => 'rating',
                'description' => 'Threshold for high ratings',
                'is_public' => true
            ],
            [
                'key' => 'rating_threshold_medium',
                'value' => '4',
                'group' => 'rating',
                'description' => 'Threshold for medium ratings',
                'is_public' => true
            ],
            [
                'key' => 'rating_display_decimal_places',
                'value' => '1',
                'group' => 'rating',
                'description' => 'Number of decimal places to display in ratings',
                'is_public' => true
            ]
        ];
        
        foreach ($settings as $setting) {
            DB::table('site_settings')->insert($setting);
        }
    }
};
