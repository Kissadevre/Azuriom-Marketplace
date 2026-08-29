<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('marketplace_resources', fn(Blueprint $table) => $table->timestamp('pinned_at')->nullable()->index()->after('published_at')); } public function down(): void { Schema::table('marketplace_resources', function(Blueprint $table) { $table->dropIndex(['pinned_at']); $table->dropColumn('pinned_at'); }); } };
