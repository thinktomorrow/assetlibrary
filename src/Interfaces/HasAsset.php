<?php

namespace Thinktomorrow\AssetLibrary\Interfaces;

use Spatie\MediaLibrary\HasMedia\HasMedia;

/**
  * @method mixed assets()
  * @method mixed load(array|string  $relations)
  * @property mixed $assets
  */
interface HasAsset extends HasMedia
{
    
}
