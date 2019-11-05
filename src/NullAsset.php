<?php

namespace Thinktomorrow\AssetLibrary;

class NullAsset extends Asset
{
    public function exists(): bool
    {
        return false;
    }

    public function filename($size = ''): string
    {
        return '';
    }

    public function url($size = ''): string
    {
        return '';
    }
}
