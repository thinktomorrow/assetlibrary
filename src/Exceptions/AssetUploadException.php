<?php

namespace Thinktomorrow\AssetLibrary\Exceptions;

use Exception;

class AssetUploadException extends Exception
{
    public static function assetAlreadyLinked()
    {
        return new static('This asset is already linked to this model.');
    }
}
