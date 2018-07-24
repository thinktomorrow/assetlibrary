<?php

namespace Thinktomorrow\AssetLibrary\Test\stubs;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Thinktomorrow\AssetLibrary\Traits\AssetTrait;

class Article extends Model implements HasMedia
{
    use AssetTrait;

    protected $table   = 'test_models';
    protected $guarded = [];
    public $timestamps = false;
}
