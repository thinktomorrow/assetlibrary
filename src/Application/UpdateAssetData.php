<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Illuminate\Support\Facades\DB;

class UpdateAssetData
{
    public function handle(string $assetId, array $data): void
    {
        $existingData = json_decode(DB::table('assets')->where('id', $assetId)->select('data')->first()->data, true);

        if (is_null($existingData)) {
            $existingData = [];
        }

        DB::table('assets')
            ->where('id', $assetId)
            ->update(['data' => array_merge($existingData, $data)]);
    }
}
