<?php

namespace Thinktomorrow\AssetLibrary\Test\stubs;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Thinktomorrow\AssetLibrary\Traits\AssetTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Article extends Model implements HasMedia
{
    use AssetTrait;

    protected $table   = 'test_models';
    protected $guarded = [];
    public $timestamps = false;

    public static function migrate()
    {
        Schema::table('test_models', function (Blueprint $table) {
            $table->string('imageurl')->nullable();
            $table->integer('order')->nullable();
        });
    }

    
}
