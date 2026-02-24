<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // for any existing selectdocslogs entries that don't have a docselected value,
        // attempt to look up the corresponding uploadlog and copy over its docno.
        DB::table('selectdocslogs')->whereNull('docselected')->cursor()->each(function ($row) {
            if ($row->excelname) {
                $docno = DB::table('uploadlogs')
                    ->where('excelname', $row->excelname)
                    ->orderBy('created_at', 'desc')
                    ->value('docno');
                if ($docno) {
                    DB::table('selectdocslogs')
                        ->where('id', $row->id)
                        ->update(['docselected' => $docno]);
                }
            }
        });
    }

    public function down()
    {
        // we don't need to revert backfilled values
        DB::table('selectdocslogs')->update(['docselected' => null]);
    }
};