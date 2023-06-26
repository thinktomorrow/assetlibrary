<?php

namespace Thinktomorrow\AssetLibrary\Tests\stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Thinktomorrow\AssetLibrary\HasAsset;
use Thinktomorrow\AssetLibrary\InteractsWithAssets;

class Article extends Model implements HasAsset
{
    use InteractsWithAssets;

    protected $table = 'articles';
    protected $guarded = [];
    public $timestamps = false;

    public static function migrate()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
        });
    }
}
