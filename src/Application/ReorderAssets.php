<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\HasAsset;

class ReorderAssets
{
    public function handle(HasAsset $model, string $type, string $locale, array $orderedAssetIds): void
    {
        $model->assetRelation()
            ->where('assets_pivot.type', $type)
            ->where('assets_pivot.locale', $locale)
            ->get()
            ->each(function(Asset $asset) use($model, $orderedAssetIds, $type, $locale){

                DB::table('assets_pivot')
                    ->where('asset_id', $asset->id)
                    ->where('entity_type', $model->getMorphClass())
                    ->where('entity_id', $model->getKey())
                    ->where('type', $type)
                    ->where('locale', $locale)
                    ->update([
                        'order' => array_search($asset->id, $orderedAssetIds),
                    ]);
            });

    }

    /**
     * Taken from: https://github.com/laravel/ideas/issues/575
     *
     * @return void
     */
    private static function batchUpdateColumn(string $table, string $column, array $indices, string $indexColumn = 'id', bool $castIdToIntegers = true, string $extraWhere = null): void
    {
        $cases = [];
        $ids = [];
        $params = [];

        foreach ($indices as $index => $modelId) {
            $id = $castIdToIntegers ? (int) $modelId : $modelId;
            $ids[] = "'{$id}'";

            $cases[] = "WHEN '{$id}' then ?";
            $params[] = $index;
        }

        $ids = implode(',', $ids);
        $cases = implode(' ', $cases);

        if ($extraWhere) {
            $extraWhere = ' AND ' . DB::raw($extraWhere);
        }

        DB::update("UPDATE `{$table}` SET `{$column}` = CASE `".$indexColumn."` {$cases} END WHERE `".$indexColumn."` in ({$ids})".$extraWhere, $params);
    }
}
