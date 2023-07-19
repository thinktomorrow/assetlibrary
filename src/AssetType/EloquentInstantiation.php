<?php

declare(strict_types=1);

namespace Thinktomorrow\AssetLibrary\AssetType;

use Illuminate\Database\Eloquent\Model;

/**
 * Overrides the eloquent model instantiation to account for proper morphable object creation.
 * This makes sure that the fetched or created class is a instance of the morphable type.
 */
trait EloquentInstantiation
{
    /**
     * Custom build for new Collections where we convert any models to the correct collection types.
     * Magic override warning.
     *
     * @ref \Illuminate\Database\Eloquent\Model::newCollection()
     *
     * @param array $models
     * @return
     * @throws NotFoundAssetType
     */
    public function newCollection(array $models = [])
    {
        foreach ($models as $k => $model) {
            if ($model instanceof HasAssetType && $assetType = $model->getAssetType()) {
                $models[$k] = $this->convertToMorphInstance($model, $assetType);
            }
        }

        return parent::newCollection($models);
    }

    /**
     * Clone the model into its expected collection class
     * @ref \Illuminate\Database\Eloquent\Model::replicate()
     *
     * @param Model $model
     * @param string $assetType
     * @return Model
     * @throws NotFoundAssetType
     */
    private function convertToMorphInstance(Model $model, string $assetType): Model
    {
        // Here we load up the proper collection model instead of the generic base class.
        return tap(AssetTypeFactory::instance($assetType, $model->attributes), function ($instance) use ($model) {
            $instance->setRawAttributes($model->attributes);
            $instance->setRelations($model->relations);
            $instance->exists = $model->exists;
        });
    }

    /**
     * Create a new instance of the given model.
     *
     * @param array $attributes
     * @param bool $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        if (! isset($attributes['asset_type'])) {
            return parent::newInstance($attributes, $exists);
        }

        $model = AssetTypeFactory::instance($attributes['asset_type'], (array)$attributes);
        $model->exists = $exists;

        $model->setConnection($this->getConnectionName());

        return $model;
    }
}
