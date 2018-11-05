<?php

namespace Thinktomorrow\AssetLibrary\Test\stubs;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Thinktomorrow\AssetLibrary\Traits\AssetTrait;

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
