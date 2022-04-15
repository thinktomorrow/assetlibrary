<?php

use Doctrine\DBAL\Types\StringType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeEntityIdOnAssetPivots extends Migration
{
    public function up()
    {
        if (!\Doctrine\DBAL\Types\Type::hasType('char')) {
            \Doctrine\DBAL\Types\Type::addType('char', StringType::class);
        }

        Schema::table('asset_pivots', function (Blueprint $table) {
            $table->char('entity_id', 36)->change();
        });
    }

    public function down()
    {
    }
}
