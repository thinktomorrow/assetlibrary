<?php

namespace Thinktomorrow\AssetLibrary\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Thinktomorrow\AssetLibrary\Models\Asset;

class MediaController extends Controller
{
    public function store(Request $request)
    {
        $asset = Asset::upload($request->file('image'));

        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        Asset::remove($request->get('imagestoremove'));

        return redirect()->back();
    }
}
