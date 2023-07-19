<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Illuminate\Support\Facades\DB;

class UpdateAssetData
{
    public function handle(string $assetId, array $data): void
    {
        DB::table('assets')
            ->where('id', $assetId)
            ->update(['data' => $data]);
    }
}
