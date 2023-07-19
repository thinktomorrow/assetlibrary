<?php

namespace Thinktomorrow\AssetLibrary\Tests\stubs;

use Thinktomorrow\AssetLibrary\AbstractAsset;
use Thinktomorrow\AssetLibrary\External\InteractsWithLocalPreviewMedia;

class VimeoAsset extends AbstractAsset implements \Thinktomorrow\AssetLibrary\External\ExternalAssetContract
{
    use InteractsWithLocalPreviewMedia;

    /**
     * Relative path of the external media file. In case the passed conversion
     * does not exist, the path to the original is returned.
     */
    public function getPath($conversionName = ''): ?string
    {
        return $this->getData($conversionName ? 'external.path.'.$conversionName : 'external.path.original');
    }

    /**
     * Return url of the external media file. In case the passed conversion
     * does not exist, the url to the original is returned.
     */
    public function getUrl(string $conversionName = '', ?string $format = null): ?string
    {
        // TODO: format support?
        return $this->getData($conversionName ? 'external.url.'.$conversionName : 'external.url.original');
    }

    /**
     * Return filename of the external media file. In case the passed conversion
     * does not exist, the name to the original is returned.
     */
    public function getFileName(string $conversionName = ''): ?string
    {
        if(!$path = $this->getPath($conversionName)) return null;

        return basename($path);
    }

    public function getBaseName(string $conversionName = ''): string
    {
        return basename($this->getFileName($conversionName), '.' . $this->getExtension());
    }

    /**
     * Checks if the conversion exists. It checks if path reference is present
     */
    public function exists(string $conversionName = ''): bool
    {
        return !!$this->getPath($conversionName);
    }

    public function getSize(): int
    {
        return $this->getData('external.size', 0);
    }

    public function getHumanReadableSize(): string
    {
        return $this->getData('external.human_readable_size', '');
    }

    public function getMimeType(): ?string
    {
        return $this->getData('external.mime_type');
    }

    public function getExtension(): string
    {
        return $this->getData('external.extension', '');
    }

    public function getWidth(string $conversionName = ''): ?int
    {
        return $this->getData('external.width');
    }

    public function getHeight(string $conversionName = ''): ?int
    {
        return $this->getData('external.height');
    }

    public function getExternalId(): string
    {
        return $this->getData('external.id');
    }

    public function getExternalRootUrl(): string
    {
        return 'https://playback.vimeo.com/tt/';
    }
}
