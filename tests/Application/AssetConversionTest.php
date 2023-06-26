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
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->save();

        $this->assertCount(2, $asset->getFirstMedia()->getGeneratedConversions());
        $this->assertEquals([
            'small' => true, 'large' => true,
        ], $asset->getFirstMedia()->getGeneratedConversions()->all());

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
        $this->assertEquals('/media/1/conversions/test-image-large.gif', $asset->getUrl('large'));
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
}
