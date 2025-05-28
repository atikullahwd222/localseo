<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_catagories', function (Blueprint $table) {
            $table->id();
            $table->string('cat_name')->comment('Category name');
            $table->string('cat_Description')->nullable()->comment('Category description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_catagories');
    }
};
