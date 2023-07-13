<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class AssociatedAsset extends MorphPivot
{
    use ProvidesData;

    public $guarded = [];
    protected $casts = [
        'data' => 'array',
    ];
}
