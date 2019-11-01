<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnusedColumnToAssetPivots extends Migration
{
    public function up()
    {
        Schema::table('asset_pivots', function (Blueprint $table) {
            $table->boolean('unused')->default(false);
        });
    }

    public function down()
    {
    }
}
