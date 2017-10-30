<?php

namespace Thinktomorrow\AssetLibrary\Http\Controllers;

use Illuminate\Pagination\Paginator;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Illuminate\Pagination\LengthAwarePaginator;

class MediaLibraryController extends Controller
{
    public function index()
    {
        $library = Asset::getAllAssets();

        $library = new LengthAwarePaginator(
            $library->forPage(Paginator::resolveCurrentPage(), 8),
            count($library),
            8,
            Paginator::resolveCurrentPage(),
            [
                'path' => Paginator::resolveCurrentPath(),
            ]);

        return view('back.media', compact('library'));
    }
}
