<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('poles', 'sub_name')) {
            DB::table('poles')->whereNull('sub_name')->update(['sub_name' => '']);
        }

        foreach ($this->singleColumnUniqueIndexes('customer_id') as $indexName) {
            Schema::table('poles', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }

        if ($this->uniqueIndexExists(['customer_id', 'sub_name'])) {
            return;
        }

        Schema::table('poles', function (Blueprint $table) {
            $table->unique(['customer_id', 'sub_name'], 'poles_customer_id_sub_name_unique');
        });
    }

    public function down(): void
    {
        if ($this->indexExists('poles_customer_id_sub_name_unique')) {
            Schema::table('poles', function (Blueprint $table) {
                $table->dropUnique('poles_customer_id_sub_name_unique');
            });
        }

        if ($this->uniqueIndexExists(['customer_id'])) {
            return;
        }

        Schema::table('poles', function (Blueprint $table) {
            $table->unique('customer_id', 'poles_customer_id_unique');
        });
    }

    private function singleColumnUniqueIndexes(string $column): array
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('poles')"))
                ->filter(fn ($index) => (int) $index->unique === 1)
                ->filter(function ($index) use ($column) {
                    $columns = collect(DB::select("PRAGMA index_info('{$index->name}')"))
                        ->pluck('name')
                        ->values()
                        ->all();

                    return $columns === [$column];
                })
                ->pluck('name')
                ->all();
        }

        return collect(DB::select(
            <<<'SQL'
                select index_name
                from information_schema.statistics
                where table_schema = database()
                    and table_name = 'poles'
                    and non_unique = 0
                    and index_name <> 'PRIMARY'
                group by index_name
                having count(*) = 1
                    and sum(case when column_name = ? then 1 else 0 end) = 1
            SQL,
            [$column],
        ))->pluck('index_name')->all();
    }

    private function uniqueIndexExists(array $columns): bool
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('poles')"))
                ->filter(fn ($index) => (int) $index->unique === 1)
                ->contains(function ($index) use ($columns) {
                    $indexColumns = collect(DB::select("PRAGMA index_info('{$index->name}')"))
                        ->pluck('name')
                        ->values()
                        ->all();

                    return $indexColumns === $columns;
                });
        }

        $columnList = implode(',', $columns);

        return DB::selectOne(
            <<<'SQL'
                select index_name
                from information_schema.statistics
                where table_schema = database()
                    and table_name = 'poles'
                    and non_unique = 0
                    and index_name <> 'PRIMARY'
                group by index_name
                having group_concat(column_name order by seq_in_index separator ',') = ?
                limit 1
            SQL,
            [$columnList],
        ) !== null;
    }

    private function indexExists(string $indexName): bool
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('poles')"))
                ->contains(fn ($index) => $index->name === $indexName);
        }

        return DB::selectOne(
            <<<'SQL'
                select index_name
                from information_schema.statistics
                where table_schema = database()
                    and table_name = 'poles'
                    and index_name = ?
                limit 1
            SQL,
            [$indexName],
        ) !== null;
    }
};
