<?php

namespace Thinktomorrow\AssetLibrary\Tests\stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Thinktomorrow\AssetLibrary\AssetTrait;
use Thinktomorrow\AssetLibrary\HasAsset;

class ArticleWithSoftdelete extends Model implements HasAsset
{
    use AssetTrait, SoftDeletes;

    protected $table   = 'test_models_with_softdelete';
    protected $guarded = [];
    public $timestamps = false;

    public static function migrate()
    {
        Schema::create('test_models_with_softdelete', function (Blueprint $table) {
            $table->increments('id');
            $table->string('imageurl')->nullable();
            $table->integer('order')->nullable();
            $table->string('locale')->nullable();
            $table->softDeletes();
        });
    }
}
