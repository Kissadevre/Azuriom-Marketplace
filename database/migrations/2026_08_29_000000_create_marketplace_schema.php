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
            $table->json('publish_roles')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('marketplace_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('marketplace_categories')
                ->restrictOnDelete();
            $table->json('publish_roles')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#6c757d');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

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
            $table->timestamp('pinned_at')->nullable()->index();
            $table->timestamp('paused_at')->nullable()->index();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->index(['status', 'published_at']);
        });

        Schema::create('marketplace_resource_images', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('resource_id')->nullable()->constrained('marketplace_resources')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->uuid('draft_token')->nullable()->index();
            $table->string('path');
            $table->string('mime_type', 20);
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->timestamps();
            $table->index(['user_id', 'resource_id']);
        });

        Schema::create('marketplace_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->decimal('price', 16, 2);
            $table->timestamps();
            $table->unique(['resource_id', 'user_id']);
        });

        Schema::create('marketplace_resource_tag', function (Blueprint $table) {
            $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('marketplace_tags')->cascadeOnDelete();
            $table->primary(['resource_id', 'tag_id']);
        });

        Schema::create('marketplace_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('marketplace_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('marketplace_comments')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['comment_id', 'user_id']);
        });

        Schema::create('marketplace_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->timestamps();
            $table->unique(['resource_id', 'user_id']);
        });

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

        Schema::create('marketplace_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('reportable_type', 50);
            $table->unsignedBigInteger('reportable_id');
            $table->string('subject');
            $table->text('excerpt')->nullable();
            $table->text('reason');
            $table->timestamps();
            $table->index(['reportable_type', 'reportable_id']);
            $table->unique(['user_id', 'reportable_type', 'reportable_id']);
        });

        Schema::create('marketplace_restrictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('lifted_by')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('lifted_by')->references('id')->on('users')->nullOnDelete();
            $table->json('actions');
            $table->text('reason')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('lifted_at')->nullable()->index();
            $table->timestamps();
            $table->index(['user_id', 'lifted_at']);
        });

        Schema::create('marketplace_resource_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['resource_id', 'user_id']);
        });

        Schema::create('marketplace_gift_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->char('code_hash', 64)->unique();
            $table->string('code_hint', 8);
            $table->unsignedInteger('usage_limit')->default(1);
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('marketplace_gift_code_resource', function (Blueprint $table) {
            $table->foreignId('gift_code_id')->constrained('marketplace_gift_codes')->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete();
            $table->primary(['gift_code_id', 'resource_id']);
        });

        Schema::create('marketplace_gift_code_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_code_id')->constrained('marketplace_gift_codes')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['gift_code_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_gift_code_redemptions');
        Schema::dropIfExists('marketplace_gift_code_resource');
        Schema::dropIfExists('marketplace_gift_codes');
        Schema::dropIfExists('marketplace_resource_follows');
        Schema::dropIfExists('marketplace_restrictions');
        Schema::dropIfExists('marketplace_reports');
        Schema::dropIfExists('marketplace_resource_updates');
        Schema::dropIfExists('marketplace_ratings');
        Schema::dropIfExists('marketplace_comment_likes');
        Schema::dropIfExists('marketplace_comments');
        Schema::dropIfExists('marketplace_resource_tag');
        Schema::dropIfExists('marketplace_purchases');
        Schema::dropIfExists('marketplace_resource_images');
        Schema::dropIfExists('marketplace_resources');
        Schema::dropIfExists('marketplace_tags');
        Schema::dropIfExists('marketplace_categories');
    }
};
