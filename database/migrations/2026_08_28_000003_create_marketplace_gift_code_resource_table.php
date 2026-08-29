<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('marketplace_gift_code_resource', function (Blueprint $table) { $table->foreignId('gift_code_id')->constrained('marketplace_gift_codes')->cascadeOnDelete(); $table->foreignId('resource_id')->constrained('marketplace_resources')->cascadeOnDelete(); $table->primary(['gift_code_id', 'resource_id']); }); } public function down(): void { Schema::dropIfExists('marketplace_gift_code_resource'); } };
