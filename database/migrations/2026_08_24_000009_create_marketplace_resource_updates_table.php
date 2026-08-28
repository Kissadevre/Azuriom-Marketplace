<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_resource_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('version', 30);
            $table->text('description');
            $table->timestamps();
            $table->unique(['resource_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_resource_updates');
    }
};
