<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewMediaColumn extends Migration
{
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            if (! Schema::hasColumn('media', 'responsive_images')) {
                $table->json('responsive_images');
            }
        });
    }

    public function down()
    {
    }
}
