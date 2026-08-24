<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('bi bi-box');
            $table->text('description')->nullable();
            $table->json('roles')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('marketplace_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('marketplace_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
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

        Schema::create('marketplace_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 16, 2);
            $table->timestamps();
            $table->unique(['resource_id', 'user_id']);
        });

        Schema::create('marketplace_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('marketplace_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->timestamps();
            $table->unique(['resource_id', 'user_id']);
        });

        Schema::create('marketplace_resource_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('version', 30);
            $table->text('description');
            $table->timestamps();
            $table->unique(['resource_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_resource_updates');
        Schema::dropIfExists('marketplace_ratings');
        Schema::dropIfExists('marketplace_comments');
        Schema::dropIfExists('marketplace_purchases');
        Schema::dropIfExists('marketplace_resources');
        Schema::dropIfExists('marketplace_categories');
    }
};
