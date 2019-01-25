<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewMediaColumn extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('media', function(Blueprint $table){
            if(!Schema::hasColumn('media', 'responsive_images'))
            {
                $table->json('responsive_images');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
}
