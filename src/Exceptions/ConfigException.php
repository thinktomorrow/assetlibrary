<?php

namespace Thinktomorrow\AssetLibrary\Exceptions;

use Exception;

class ConfigException extends Exception
{
    public static function create()
    {
        return new static("The cropping config setting needs to be turned on to crop images. See 'Config\assetlibrary.php' for the 'allowCropping' field.");
    }
}