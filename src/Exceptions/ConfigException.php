<?php

namespace Thinktomorrow\AssetLibrary\Exceptions;

use Exception;

class ConfigException extends Exception
{
    public static function croppingDisabled()
    {
        return new static("The cropping config setting needs to be turned on to crop images. See 'config\assetlibrary.php' for the 'allowCropping' field.");
    }
}
