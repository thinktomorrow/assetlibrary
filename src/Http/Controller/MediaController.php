<?php

namespace App\Http\Controllers\Back\Media;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
