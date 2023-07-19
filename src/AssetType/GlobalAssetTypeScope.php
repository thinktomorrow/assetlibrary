<?php

declare(strict_types=1);

namespace Thinktomorrow\AssetLibrary\AssetType;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class GlobalAssetTypeScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        try {
            $builder->where('asset_type', '=', $model->getAssetType());
        } /**
         * If query is performed on a model that has no morph key,
         * it is fine to ignore the morph scope altogether.
         */
        catch (NotFoundAssetType $e) {
        }
    }
}
