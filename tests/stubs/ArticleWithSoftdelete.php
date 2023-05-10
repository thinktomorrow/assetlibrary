<?php

namespace Thinktomorrow\AssetLibrary\Tests\stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Thinktomorrow\AssetLibrary\InteractsWithAssets;
use Thinktomorrow\AssetLibrary\HasAsset;

class ArticleWithSoftdelete extends Model implements HasAsset
{
    use InteractsWithAssets, SoftDeletes;

    protected $table   = 'articles_with_softdelete';
    protected $guarded = [];
    public $timestamps = false;

    public static function migrate()
    {
        Schema::create('articles_with_softdelete', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->softDeletes();
        });
    }
}
