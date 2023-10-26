<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Illuminate\Http\UploadedFile;
use Thinktomorrow\AssetLibrary\Application\CreateAsset;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class AssetConversionTest extends TestCase
{
    public function test_it_can_fetch_generated_conversions()
    {
        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        config()->set('thinktomorrow.assetlibrary.formats', [

        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.jpg'))
            ->save();

        $this->assertCount(2, $asset->getFirstMedia()->getGeneratedConversions());
        $this->assertEquals([
            'small' => true, 'large' => true,
        ], $asset->getFirstMedia()->getGeneratedConversions()->all());

    }

    public function test_it_can_avoid_to_generate_conversions()
    {
        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        config()->set('thinktomorrow.assetlibrary.formats', [

        ]);

        config()->set('thinktomorrow.assetlibrary.disable_conversions_for_mimetypes', [
            'image/gif',
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->save();

        $this->assertCount(0, $asset->getFirstMedia()->getGeneratedConversions());
    }

    public function test_it_can_fetch_generated_conversions_and_formats()
    {
        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        config()->set('thinktomorrow.assetlibrary.formats', [
            'webp',
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->save();

        $this->assertCount(4, $asset->getFirstMedia()->getGeneratedConversions());
        $this->assertEquals([
            'small' => true, 'large' => true, 'webp-small' => true,'webp-large' => true,
        ], $asset->getFirstMedia()->getGeneratedConversions()->all());

    }

    public function test_it_can_create_asset_with_conversions()
    {
        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->save();

        $this->assertEquals('/media/1/conversions/test-image-small.gif', $asset->getUrl('small'));
        $this->assertEquals(50, $asset->getImageWidth('small'));
        $this->assertEquals(50, $asset->getImageHeight('small'));
        $this->assertEquals('/media/1/conversions/test-image-large.gif', $asset->getUrl('large'));
        $this->assertEquals(100, $asset->getImageWidth('large'));
        $this->assertEquals(100, $asset->getImageHeight('large'));
    }

    public function test_it_can_create_asset_with_conversions_and_format()
    {
        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        config()->set('thinktomorrow.assetlibrary.formats', [
            'webp',
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->save();

        $this->assertEquals('/media/1/conversions/test-image-webp-small.webp', $asset->getUrl('small', 'webp'));
        $this->assertEquals(50, $asset->getImageWidth('webp-small'));
        $this->assertEquals(50, $asset->getImageHeight('webp-small'));
        $this->assertEquals('/media/1/conversions/test-image-webp-large.webp', $asset->getUrl('large', 'webp'));
        $this->assertEquals(100, $asset->getImageWidth('webp-large'));
        $this->assertEquals(100, $asset->getImageHeight('webp-large'));
    }

    public function test_it_can_create_asset_with_alternate_formats()
    {
        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        config()->set('thinktomorrow.assetlibrary.formats', [
            'webp',
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->save();

        $this->assertEquals('/media/1/conversions/test-image-small.gif', $asset->getUrl('small'));
        $this->assertEquals('/media/1/conversions/test-image-large.gif', $asset->getUrl('large'));
        $this->assertEquals('/media/1/conversions/test-image-webp-small.webp', $asset->getUrl('small', 'webp'));
        $this->assertEquals('/media/1/conversions/test-image-webp-large.webp', $asset->getUrl('large', 'webp'));
    }

    public function test_if_format_does_not_exist_it_returns_original()
    {
        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        config()->set('thinktomorrow.assetlibrary.formats', [
            'webp',
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->save();

        $this->assertEquals('/media/1/conversions/test-image-small.gif', $asset->getUrl('small'));
        $this->assertEquals('/media/1/conversions/test-image-small.gif', $asset->getUrl('small', 'xxx'));
    }

    public function test_it_should_not_create_conversions_for_non_image()
    {
        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        config()->set('thinktomorrow.assetlibrary.formats', [
            'webp',
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->create('foobar.pdf'))
            ->save();

        $this->assertCount(0, $asset->getFirstMedia()->getGeneratedConversions());
    }

    public function test_it_should_not_run_additional_format_when_original_is_already_this_format()
    {
        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        config()->set('thinktomorrow.assetlibrary.formats', [
            'webp',
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.webp'))
            ->save();

        $this->assertCount(2, $asset->getFirstMedia()->getGeneratedConversions());
        $this->assertEquals('/media/1/conversions/test-image-small.webp', $asset->getUrl('small'));
        $this->assertEquals('/media/1/conversions/test-image-large.webp', $asset->getUrl('large'));
        $this->assertEquals('/media/1/conversions/test-image-small.webp', $asset->getUrl('small', 'webp'));
        $this->assertEquals('/media/1/conversions/test-image-large.webp', $asset->getUrl('large', 'webp'));
    }

    public function test_it_can_convert_svg_when_imagick_is_available()
    {
        $imagickModuleAvailable = class_exists('Imagick');

        $asset = $this->createAssetWithMedia('logo.svg');

        $this->assertStringEndsWith('/temp/media/1/logo.svg', $asset->getPath());
        $this->assertEquals('logo.svg', $asset->getFileName());
        $this->assertEquals('image/svg+xml', $asset->getMimeType());
        $this->assertEquals('svg', $asset->getExtension());
        $this->assertEquals('image', $asset->getExtensionType());

        if($imagickModuleAvailable) {
            $this->assertStringEndsWith('/temp/media/1/conversions/logo-thumb.jpg', $asset->getPath('thumb'));
            $this->assertEquals('logo-thumb.jpg', $asset->getFileName('thumb'));
        } else {

            // Without Imagick, there is no thumb conversion and original is returned
            $this->assertStringEndsWith('/temp/media/1/logo.svg', $asset->getPath('thumb'));
            $this->assertEquals('logo.svg', $asset->getFileName('thumb'));
        }
    }

    public function test_it_returns_empty_list_of_generated_conversions_if_no_conversions_ran()
    {
        config()->set('media-library.image_generators', []);

        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->save();

        $this->assertEquals([], $asset->getGeneratedConversions());
    }


    public function test_it_returns_all_generated_conversions()
    {
        config()->set('media-library.image_generators', [
            \Spatie\MediaLibrary\Conversions\ImageGenerators\Image::class,
        ]);

        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->save();

        $this->assertEquals(['small','large'], $asset->getGeneratedConversions());
    }

    public function test_it_returns_only_successful_generated_conversions()
    {
        config()->set('media-library.image_generators', [
            \Spatie\MediaLibrary\Conversions\ImageGenerators\Image::class,
        ]);

        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->save();

        $media = $asset->media->first();
        $media->generated_conversions = ['small' => false, 'large' => true];
        $media->save();

        $this->assertEquals(['large'], $asset->getGeneratedConversions());
    }

    public function test_it_can_return_generated_conversions_per_format()
    {
        config()->set('media-library.image_generators', [
            \Spatie\MediaLibrary\Conversions\ImageGenerators\Image::class,
        ]);

        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        config()->set('thinktomorrow.assetlibrary.formats', [
            'webp',
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->save();

        $this->assertEquals(['small','large', 'webp-small','webp-large'], $asset->getGeneratedConversions());
        $this->assertEquals(['webp-small','webp-large'], $asset->getGeneratedConversions('webp'));
        $this->assertEquals(['small','large'], $asset->getGeneratedConversions('original'));
    }

    public function test_it_can_return_urls_by_width()
    {
        config()->set('media-library.image_generators', [
            \Spatie\MediaLibrary\Conversions\ImageGenerators\Image::class,
        ]);

        config()->set('thinktomorrow.assetlibrary.conversions', [
            'small' => [
                'width' => 50,
                'height' => 50,
            ],
            'large' => [
                'width' => 100,
                'height' => 100,
            ],
        ]);

        config()->set('thinktomorrow.assetlibrary.formats', [
            'webp',
        ]);

        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->save();

        $this->assertEquals(collect([
            '/media/1/conversions/test-image-small.gif' => 50,
            '/media/1/conversions/test-image-large.gif' => 100,
            '/media/1/conversions/test-image-webp-small.webp' => 50,
            '/media/1/conversions/test-image-webp-large.webp' => 100,
        ]), $asset->getUrlsByConversionWidth());

        $this->assertEquals(collect([
            '/media/1/conversions/test-image-webp-small.webp' => 50,
            '/media/1/conversions/test-image-webp-large.webp' => 100,
        ]), $asset->getUrlsByConversionWidth('webp'));

        $this->assertEquals(collect([
            '/media/1/conversions/test-image-small.gif' => 50,
            '/media/1/conversions/test-image-large.gif' => 100,
        ]), $asset->getUrlsByConversionWidth('original'));

    }
}
