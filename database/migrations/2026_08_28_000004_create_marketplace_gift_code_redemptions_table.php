<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('marketplace_gift_code_redemptions', function (Blueprint $table) { $table->id(); $table->foreignId('gift_code_id')->constrained('marketplace_gift_codes')->cascadeOnDelete(); $table->unsignedInteger('user_id'); $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete(); $table->timestamps(); $table->unique(['gift_code_id', 'user_id']); }); } public function down(): void { Schema::dropIfExists('marketplace_gift_code_redemptions'); } };
