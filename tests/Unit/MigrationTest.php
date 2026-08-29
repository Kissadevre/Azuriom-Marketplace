<?php

namespace Tests\Unit;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    private const BASELINE = '2026_08_29_000000_create_marketplace_schema.php';

    /** @var array<string, array<int, string>> */
    private const TABLE_COLUMNS = [
        'marketplace_categories' => ['id', 'name', 'slug', 'icon', 'description', 'roles', 'publish_roles', 'position', 'is_enabled', 'created_at', 'updated_at'],
        'marketplace_tags' => ['id', 'category_id', 'publish_roles', 'name', 'slug', 'description', 'color', 'position', 'is_enabled', 'created_at', 'updated_at'],
        'marketplace_resources' => ['id', 'uuid', 'category_id', 'user_id', 'name', 'version', 'summary', 'description', 'banner_path', 'delivery_type', 'file_path', 'external_url', 'price', 'status', 'moderation_note', 'downloads', 'published_at', 'pinned_at', 'paused_at', 'archived_at', 'created_at', 'updated_at'],
        'marketplace_resource_images' => ['id', 'uuid', 'resource_id', 'user_id', 'draft_token', 'path', 'mime_type', 'size', 'width', 'height', 'created_at', 'updated_at'],
        'marketplace_purchases' => ['id', 'resource_id', 'user_id', 'price', 'created_at', 'updated_at'],
        'marketplace_resource_tag' => ['resource_id', 'tag_id'],
        'marketplace_comments' => ['id', 'resource_id', 'user_id', 'content', 'created_at', 'updated_at'],
        'marketplace_comment_likes' => ['id', 'comment_id', 'user_id', 'created_at', 'updated_at'],
        'marketplace_ratings' => ['id', 'resource_id', 'user_id', 'rating', 'created_at', 'updated_at'],
        'marketplace_resource_updates' => ['id', 'resource_id', 'user_id', 'version', 'description', 'created_at', 'updated_at'],
        'marketplace_reports' => ['id', 'user_id', 'reportable_type', 'reportable_id', 'subject', 'excerpt', 'reason', 'created_at', 'updated_at'],
        'marketplace_restrictions' => ['id', 'user_id', 'created_by', 'lifted_by', 'actions', 'reason', 'expires_at', 'lifted_at', 'created_at', 'updated_at'],
        'marketplace_resource_follows' => ['id', 'resource_id', 'user_id', 'created_at', 'updated_at'],
        'marketplace_gift_codes' => ['id', 'user_id', 'code_hash', 'code_hint', 'usage_limit', 'expires_at', 'created_at', 'updated_at'],
        'marketplace_gift_code_resource' => ['gift_code_id', 'resource_id'],
        'marketplace_gift_code_redemptions' => ['id', 'gift_code_id', 'user_id', 'created_at', 'updated_at'],
    ];

    public function test_public_baseline_creates_and_drops_the_complete_schema(): void
    {
        $connection = config('database.default');
        $databaseKey = 'database.connections.'.$connection.'.database';
        $originalDatabase = config($databaseKey);
        DB::purge($connection);
        config([$databaseKey => ':memory:']);

        try {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
            });

            $migration = require dirname(__DIR__, 2).'/database/migrations/'.self::BASELINE;

            $this->assertInstanceOf(Migration::class, $migration);
            $migration->up();

            foreach (self::TABLE_COLUMNS as $table => $columns) {
                $this->assertTrue(Schema::hasTable($table), $table);
                $this->assertEqualsCanonicalizing($columns, Schema::getColumnListing($table), $table);

                foreach (Schema::getIndexes($table) as $index) {
                    $this->assertLessThanOrEqual(
                        64,
                        strlen($index['name']),
                        $index['name'].' exceeds the MariaDB identifier limit.'
                    );
                }

                foreach (Schema::getForeignKeys($table) as $foreignKey) {
                    $defaultName = $table.'_'.implode('_', $foreignKey['columns']).'_foreign';

                    $this->assertLessThanOrEqual(
                        64,
                        strlen($defaultName),
                        $defaultName.' exceeds the MariaDB identifier limit.'
                    );
                }
            }

            $foreignKeyCount = collect(array_keys(self::TABLE_COLUMNS))
                ->sum(fn (string $table) => count(Schema::getForeignKeys($table)));

            $this->assertSame(28, $foreignKeyCount);

            $migration->down();

            foreach (array_keys(self::TABLE_COLUMNS) as $table) {
                $this->assertFalse(Schema::hasTable($table), $table);
            }
        } finally {
            foreach (array_reverse(array_keys(self::TABLE_COLUMNS)) as $table) {
                Schema::dropIfExists($table);
            }

            Schema::dropIfExists('users');
            DB::purge($connection);
            config([$databaseKey => $originalDatabase]);
        }
    }
}
