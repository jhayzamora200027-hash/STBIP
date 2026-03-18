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
                Schema::table('gallery_cards', function (Blueprint $table) {
                    if (!Schema::hasColumn('gallery_cards', 'docno')) return;
                    $table->unique('docno');
                });
            } catch (\Throwable $e) {
            }

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
