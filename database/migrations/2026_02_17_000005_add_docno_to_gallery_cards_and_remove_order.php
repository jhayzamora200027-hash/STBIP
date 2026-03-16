<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('gallery_cards')) {
            Schema::table('gallery_cards', function (Blueprint $table) {
                if (!Schema::hasColumn('gallery_cards', 'docno')) {
                    $table->unsignedBigInteger('docno')->nullable()->after('id');
                }
            });

            $cards = DB::table('gallery_cards')->orderBy('id')->get();
            foreach ($cards as $c) {
                if (empty($c->docno)) {
                    DB::table('gallery_cards')->where('id', $c->id)->update(['docno' => $c->id]);
                }
            }

            $hasUniqueDocno = false;
            try {
                $driver = DB::connection()->getDriverName();
            } catch (\Throwable $e) {
                $driver = null;
            }

            try {
                if ($driver === 'mysql') {
                    $existing = DB::select("SHOW INDEX FROM gallery_cards WHERE Column_name = 'docno' AND Non_unique = 0");
                    if (!empty($existing)) {
                        $hasUniqueDocno = true;
                    }
                } elseif ($driver === 'sqlite' || $driver === 'sqlite3') {
                    $indexes = DB::select("PRAGMA index_list('gallery_cards')");
                    foreach ($indexes as $idx) {
                        $indexName = null;
                        $isUnique = false;
                        if (is_object($idx)) {
                            $indexName = $idx->name ?? null;
                            $isUnique = isset($idx->unique) ? (bool) $idx->unique : false;
                        } elseif (is_array($idx)) {
                            $indexName = $idx['name'] ?? null;
                            $isUnique = isset($idx['unique']) ? (bool) $idx['unique'] : false;
                        }

                        if (!$indexName) {
                            continue;
                        }

                        $infoRows = DB::select("PRAGMA index_info('" . $indexName . "')");
                        foreach ($infoRows as $col) {
                            $colName = is_object($col) ? ($col->name ?? null) : ($col['name'] ?? null);
                            if ($colName === 'docno' && $isUnique) {
                                $hasUniqueDocno = true;
                                break 2;
                            }
                        }
                    }
                } elseif ($driver === 'pgsql' || $driver === 'postgres' || $driver === 'postgresql') {
                    $existing = DB::select("SELECT conname FROM pg_constraint c JOIN pg_class t ON c.conrelid = t.oid JOIN pg_attribute a ON a.attrelid = t.oid WHERE t.relname = 'gallery_cards' AND c.contype = 'u' AND a.attname = 'docno'");
                    if (!empty($existing)) {
                        $hasUniqueDocno = true;
                    }
                }
            } catch (\Throwable $e) {
                $hasUniqueDocno = false;
            }

            Schema::table('gallery_cards', function (Blueprint $table) use ($hasUniqueDocno) {
                if (!Schema::hasColumn('gallery_cards', 'docno')) return;
                if ($hasUniqueDocno) return;

                try {
                    $table->unique('docno');
                } catch (\Throwable $e) {
                }
            });

            Schema::table('gallery_cards', function (Blueprint $table) {
                if (Schema::hasColumn('gallery_cards', 'order')) {
                    $table->dropColumn('order');
                }
            });
        }

        if (Schema::hasTable('gallery_children')) {
            Schema::table('gallery_children', function (Blueprint $table) {
                if (Schema::hasColumn('gallery_children', 'order')) {
                    $table->dropColumn('order');
                }
            });

            $duplicates = DB::select(
                "SELECT docno, COUNT(*) c FROM gallery_children WHERE docno IS NOT NULL GROUP BY docno HAVING c > 1"
            );
            if (empty($duplicates)) {
                Schema::table('gallery_children', function (Blueprint $table) {
                    $table->unique('docno');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('gallery_cards')) {
            Schema::table('gallery_cards', function (Blueprint $table) {
                if (!Schema::hasColumn('gallery_cards', 'order')) {
                    $table->integer('order')->default(0)->after('url');
                }
                if (Schema::hasColumn('gallery_cards', 'docno')) {
                    $table->dropUnique(['docno']);
                    $table->dropColumn('docno');
                }
            });
        }

        if (Schema::hasTable('gallery_children')) {
            Schema::table('gallery_children', function (Blueprint $table) {
                if (!Schema::hasColumn('gallery_children', 'order')) {
                    $table->integer('order')->default(0);
                }
                if (Schema::hasColumn('gallery_children', 'docno')) {
                }
            });
        }
    }
};
