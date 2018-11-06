<?php

namespace Thinktomorrow\AssetLibrary\Tests;

use Spatie\MediaLibrary\Models\Media;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;

trait TestHelpers
{
    public function getArticleWithAsset($type = '', $locale = 'nl')
    {
        $article = Article::create();
        $article->assets()->attach($this->getUploadedAsset(), ['type' => $type, 'locale' => $locale]);

        return $article->load('assets');
    }

    public function getUploadedAsset($filename = 'image.png', $width = 100, $height = 100)
    {
        $asset = Asset::create();

        @mkdir(public_path('/media/'));
        @mkdir(public_path('/media/'.$asset->id));
        copy(public_path('/../media-stubs/'.$filename), public_path('/media/'.$asset->id.'/'.$filename));

        Media::create([
            'model_type'        => 'Thinktomorrow\AssetLibrary\Models\Asset',
            'model_id'          => $asset->id,
            'collection_name'   => 'default',
            'name'              => $filename,
            'file_name'         => $filename,
            'mime_type'         => 'image/png',
            'disk'              => 'public',
            'size'              => '109',
            'manipulations'     => [],
            'responsive_images' => [],
            'custom_properties' => [
                'dimensions' => $width.' x '.$height,
            ],
        ]);

        return $asset->load('media');
    }

    private function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {
                    $this->recurse_copy($src.'/'.$file, $dst.'/'.$file);
                } else {
                    copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }
        closedir($dir);
    }
}
