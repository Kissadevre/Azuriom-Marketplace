<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_resource_tag', function (Blueprint $table) {
            $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('marketplace_tags')->cascadeOnDelete();
            $table->primary(['resource_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_resource_tag');
    }
};
