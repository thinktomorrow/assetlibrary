<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
