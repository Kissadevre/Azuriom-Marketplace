<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('marketplace_tags', fn(Blueprint $table) => $table->json('publish_roles')->nullable()->after('category_id')); } public function down(): void { Schema::table('marketplace_tags', fn(Blueprint $table) => $table->dropColumn('publish_roles')); } };
