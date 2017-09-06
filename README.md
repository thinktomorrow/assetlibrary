# AssetLibrary

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This assetlibrary is based on spatie's medialibrary package but provides some extra features.

## Install

Via Composer

``` bash
$ composer require thinktomorrow/assetlibrary
```

Next publish the config files

```
php artisan vendor:publish --provider="Thinktomorrow\AssetLibrary\AssetLibraryServiceProvider" --tag="migrations"
php artisan vendor:publish --provider="Thinktomorrow\AssetLibrary\AssetLibraryServiceProvider" --tag="config"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="config"
``` 

## Features

This media library provides some extra features along those of spatie/laravel-medialibrary.

    - it can upload a file to the library without an attached model
    - it has localization support
    - it can define a type for an upload attached to a model
    - it can upload a file to a model
    - it can attach a file from the library to a model
    - a media file can be attached to multiple models

## Workflow
To make a model accept file uploads we only need to implement the HasMedia interface and use the AssetTrait and HasMediaTrait.

```php
use Illuminate\Database\Eloquent\Model;
use Thinktomorrow\AssetLibrary\AssetTrait;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class Article extends Model implements HasMedia
{
    use AssetTrait, HasMediaTrait;
}
```

That's it!

#### Routes
There are some routes and controllers that help out in uploading, etc. If you need to adjust there routes 
(adding auth middleware for instance) you can put these routes in your local route file where you want:
```
    Route::get('media', '\Thinktomorrow\AssetLibrary\Http\Controllers\MediaLibraryController@index')->name('media.library');
    Route::post('media/upload', '\Thinktomorrow\AssetLibrary\Http\Controllers\MediaController@store')->name('media.upload');
    Route::post('media/remove', '\Thinktomorrow\AssetLibrary\Http\Controllers\MediaController@destroy')->name('media.remove');
```

#### Creating files

We can now upload a file to articles like this:

```php
$article->addFile('file', 'type', 'locale');
```

The file is required, the type and locale are optional.
The file van be any file or an instance of Thinktomorrow\AssetLibrary\Asset.
The Thinktomorrow\AssetLibrary\Asset upload is used to attach existing assets from the library to an existing model, and works exactly the same as uploading a file.

Type allows us to get a file based on the type for instance an article could have a banner but also a pdf file.
Without type the library wouldn't be able to discern between them.

An upload also creates conversions(size) for the file:

    - thumb:    width     150
                height    150
    - medium:   width     300
                height    130
    - large:    width     1024
                height    353
    - full:     width     1600
                height    553

The original version will be returned if you don't specify the size.

To aid you in sending the right data to the controller there are helper functions to inject an input into your form like so:

```php
{!! \Thinktomorrow\AssetLibrary\Asset::typeField('banner') !!}
{!! \Thinktomorrow\AssetLibrary\Asset::localeField($locale) !!}
```

The type field also has an optional property locale if you need to seperate multiple uploads by locale.

#### Retrieving files

Get all the uploaded files:
```php
Asset::getAllAssets()
``` 
check if the file exists for the given type and locale
```php
$model->hasFile('type','locale') 
```
Get the filename for the given type and locale:
```php
$model->getFilename('type','locale') 

```
Get the url for the given type, size and locale
```php
$model->getFileUrl('type', 'size', 'locale')
```

## Resources
- https://github.com/spatie/laravel-medialibrary


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email mr.deleeuw@gmail.com instead of using the issue tracker.

## Credits

- [Philippe Damen][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/thinktomorrow/assetlibrary.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/thinktomorrow/assetlibrary/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/thinktomorrow/assetlibrary.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/thinktomorrow/assetlibrary.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/thinktomorrow/assetlibrary.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/thinktomorrow/assetlibrary
[link-travis]: https://travis-ci.org/thinktomorrow/assetlibrary
[link-scrutinizer]: https://scrutinizer-ci.com/g/thinktomorrow/assetlibrary/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/thinktomorrow/assetlibrary
[link-downloads]: https://packagist.org/packages/thinktomorrow/assetlibrary
[link-author]: https://github.com/yinx
[link-contributors]: ../../contributors
