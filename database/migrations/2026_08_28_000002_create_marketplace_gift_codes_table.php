<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('marketplace_gift_codes', function (Blueprint $table) { $table->id(); $table->unsignedInteger('user_id'); $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete(); $table->char('code_hash', 64)->unique(); $table->string('code_hint', 8); $table->unsignedInteger('usage_limit')->default(1); $table->timestamp('expires_at')->nullable()->index(); $table->timestamps(); }); } public function down(): void { Schema::dropIfExists('marketplace_gift_codes'); } };
