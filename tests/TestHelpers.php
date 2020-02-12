<?php

namespace Thinktomorrow\AssetLibrary\Tests;

use Spatie\MediaLibrary\Models\Media;
use Thinktomorrow\AssetLibrary\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\stubs\ArticleWithSoftdelete;

trait TestHelpers
{
    public function getArticleWithAsset(string $type, string $locale = 'en'): Article
    {
        $article = Article::create();
        // $article->assetRelation()->attach($this->getUploadedAsset(), ['type' => $type, 'locale' => $locale]);

        app(AddAsset::class)->add($article, $this->getUploadedAsset(), $type, $locale);

        return $article;
    }

    public function getSoftdeleteArticleWithAsset($type = '', $locale = 'en'): ArticleWithSoftdelete
    {
        $article = ArticleWithSoftdelete::create();
        $article->assetRelation()->attach($this->getUploadedAsset(), ['type' => $type, 'locale' => $locale]);

        return $article;
    }

    public function getUploadedAsset($filename = 'image.png', $width = 100, $height = 100): Asset
    {
        $asset = Asset::create();

        @mkdir(public_path('/media/'.$asset->id), 0777, true);
        copy(public_path('/../media-stubs/'.$filename), public_path('/media/'.$asset->id.'/'.$filename));

        Media::create([
            'model_type'        => 'Thinktomorrow\AssetLibrary\Asset',
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

    public function recurse_copy($src, $dst)
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

    public function getBase64WithName($name = 'tt-favicon.png')
    {
        return '{"server":null,"meta":{},"input":{"name":"'.$name.'","type":"image/png","size":5558,"width":32,"height":32,"field":null},"output":{"name":"'.$name.'","type":"image/png","width":32,"height":32,"image":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAEp0lEQVRYR+1XTW8bVRQ94/HY46/4K4lTF1KipBASEbVpELQpICRAYlFgQRelCxZ8SLAoDRIFpC5Dmq5AINT+ASIEAqGABCskBIiEqiQNoVDR0CihiRvHju3Escfzhe6zY894nDhBrbrpkyxr5s1798w95553h9N1XcdtHNwdADvJQIUsHbIGyFqRPcFGPw4A/QCu+LetsS0KKHBB0zCVlHBxOYeZTAEJSYGkFgE4eA4hJ482nwO9YRf2h0WIvG1bQLYEQNsXVA3f/buGb+dXkZRUcHVejzTd4ODx5G4vjrT64KoDZFMAFHwmI+Hjy0lcz8p1A1vzraNRtOP1+0PoCjg3XV8TAAW/EF/Hh38kyzxvi9AaD/Ec8GpnCI+1uGuCsAAgvqdX8jhzKQ5F35DV/w1fXEeafKM7jIcj7pJMK/tZAKxIKt76NYZVknlpEK/3+BwIOHl2559MwTTfLPLY5RHY3GJWxlJetSAWeQ7DD0bQ4hZMIEwAKNC5P5P4IZYtlxTtpEPHya4wDkY87GpoMo6pFakc5NlWH461+9n1yNU0RudXa6asNyziVE+jiQoTgHhOwcmxRZb6ytsXAQx0h3GoxQOaem9iiZXkxnhujw/HOgLs8pO/UxidqwAwFg1RQVmgbG6MMgDifnQug5GZtAl9u0/AS/cGEXHZ4XXwDEAsKyOnVCjyO3mERDtbl8wrSEsq84jBko6MG1JpHm8PlD3CBODsVBwTibwJAPH7SMSDwy1uRD0CA/DjQhaxnFJ+rjvoRHdIZHNTiRyupApQNB3fzK+iWg0dDQ4MHmgu02AAoGNgLIZFw8ZGEb7T04TeJhcLMkQUGDRwtK0Bz7f52dwIUbCJBmg/v2DDuf4oeGbdQBmAput47ecFpAqV1G4AoLl3bxIAj53D+f7dzL4tACgDxtTeCgA+wYbz/VHYqzNAIhy6ZFb3rQDQ5hNwpi9SSwPAF7NpfH6NqsB8nlZTMDwZx2SyIlajBj69msJXhjKsNoSn7/Lixb1BaxUwF1uX8eZ4DKVj3uAFOk50hXF4lweUqa/nMvjsWhp2jkNO1XHkbh+OdxRL67flHD6YToAynFfJQcyDKmCv32n1AbpDTvj+dALj8ZzFyR4IOvF2T1OJOx05Rcf1NRmnJ5bQJPIY7ovAI5BV6yxwVtZw6sINZA1+QeV6el8TbAZ3qrJi4KcbWXx0OQGuigYC1+oV0BkQ2ZvG1xVMp/KQ1aJThp127G8UWXllJBW/r+SxJmtlrqlrGjwQwR6vw9SoWM6Cs1PLFjOypGMHN4gCDjpevi+EJ6JeS5dkArCcV3Dil0WUOq0dhNn60Rfa/Xim1bd1P0DiqlQBwHMc+hpduJKWkCqQoe6g0yzh8dpteKUziIearX2A5TAi7x4Yj7Ee8PGoF09FPQg6eawpGr6czeD7hSzyJKi6La8OwWbDoy1uHG3zsx5iK+hlCmLrCv5KSzjY7ILDZu5oSYDUoIzFc6zMZlcLSMtauVyp5MjhSGD7wiIONbsRcNAe9bNmOg2ZN2+xpvhdoDON5FUN64rOfMFt5+Cy21C0d65+kgyS2dZ3wU1TY42N7gC47Rn4Dw+ni78hQfokAAAAAElFTkSuQmCC"},"actions":{"rotation":null,"crop":{"x":0,"y":0,"height":32,"width":32,"type":"auto"},"size":null,"filters":{"sharpen":0},"minSize":{"width":0,"height":0}}}';
    }
}
