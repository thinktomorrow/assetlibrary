<?php

namespace Thinktomorrow\AssetLibrary\Tests\stubs;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Thinktomorrow\AssetLibrary\Traits\AssetTrait;
use Thinktomorrow\AssetLibrary\Interfaces\HasAsset;

class Article extends Model implements HasAsset
{
    use AssetTrait;

    protected $table   = 'test_models';
    protected $guarded = [];
    public $timestamps = false;

    public static function migrate()
    {
        Schema::create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('imageurl')->nullable();
            $table->integer('order')->nullable();
        });
    }
}
