<?php

namespace Thinktomorrow\AssetLibrary\Exceptions;

use Exception;

class CorruptMediaException extends Exception
{
    public static function corrupt($id)
    {
        return new static("There seems to be something wrong with asset id ". $id .". There is no media attached at this time.");
    }
}