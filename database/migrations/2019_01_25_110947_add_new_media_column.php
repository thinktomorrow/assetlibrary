<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
