<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('asset_type');
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('assets_pivot', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id');
            $table->char('entity_id', 60);
            $table->string('entity_type');
            $table->string('type');
            $table->string('locale');
            $table->json('data')->nullable();
            $table->integer('order')->default(0);

            $table->foreign('asset_id')
                ->references('id')
                ->on('assets')
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets_pivot');
        Schema::dropIfExists('assets');
    }
};
