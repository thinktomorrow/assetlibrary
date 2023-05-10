<?php

namespace Thinktomorrow\AssetLibrary\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Thinktomorrow\AssetLibrary\Application\CreateAsset;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\HasAsset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Thinktomorrow\AssetLibrary\Tests\stubs\ArticleWithSoftdelete;

trait TestHelpers
{
    public function createAssetWithMedia($filename = 'image.png', array $values = []): Asset
    {
        $asset = Asset::create($values);
        $asset->addMedia(__DIR__.'/media-stubs/' . $filename)
            ->preservingOriginal()
            ->toMediaCollection();

        return $asset->load('media');
    }

    public function createModelWithAsset(Asset $asset, string $type = 'image', string $locale = 'en', int $order = 0): HasAsset
    {
        $model = Article::create();
        $model->assetRelation()->attach($asset, ['type' => $type, 'locale' => $locale, 'order' => $order]);

        return $model;
    }

    public function getSoftdeleteArticleWithAsset($filename = 'image.png', array $values = [], string $type = 'image', string $locale = 'en', int $order = 0): ArticleWithSoftdelete
    {
        $model = ArticleWithSoftdelete::create();
        $asset = $this->createAssetWithMedia($filename, $values);

        $model->assetRelation()->attach($asset, ['type' => $type, 'locale' => $locale, 'order' => $order]);

        return $model;
    }

    public function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }


    protected function dummyBase64Payload()
    {
        return ';base64,iVBORw0KGgoAAAANSUhEUgAAA/gAAAE4AQMAAADVYspJAAAAA1BMVEUEAgSVKDOdAAAAPUlEQVR42u3BAQ0AAADCoPdPbQ8HFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/BicAAABWZX81AAAAABJRU5ErkJggg==';
    }
}
