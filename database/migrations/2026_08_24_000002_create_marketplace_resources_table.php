<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_resources', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('category_id')->constrained('marketplace_categories')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('version')->nullable();
            $table->text('summary');
            $table->longText('description');
            $table->string('banner_path')->nullable();
            $table->string('delivery_type', 20);
            $table->string('file_path')->nullable();
            $table->text('external_url')->nullable();
            $table->decimal('price', 16, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('moderation_note')->nullable();
            $table->unsignedBigInteger('downloads')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('paused_at')->nullable()->index();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_resources');
    }
};
