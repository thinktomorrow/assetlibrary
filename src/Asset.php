<?php

namespace Thinktomorrow\AssetLibrary;

class Asset extends AbstractAsset
{
    /**
     * @deprecated Use getFileName instead
     */
    public function filename($size = ''): string
    {
        return $this->getFileName($size);
    }

    /**
     * @deprecated use getUrl() instead
     */
    public function url($size = ''): string
    {
        return $this->getUrl($size);
    }

    /**
     * @deprecated use exists() instead
     */
    public function hasFile(): bool
    {
        return $this->exists();
    }

    /**
     * @deprecated use getWidth() instead
     */
    public function getImageWidth(string $conversionName = ''): ?int
    {
        return $this->getWidth($conversionName);
    }

    /**
     * @deprecated use getHeight() instead
     */
    public function getImageHeight(string $conversionName = ''): ?int
    {
        return $this->getHeight($conversionName);
    }
}
