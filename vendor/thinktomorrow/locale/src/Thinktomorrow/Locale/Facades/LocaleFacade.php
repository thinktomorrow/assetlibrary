<?php

namespace Thinktomorrow\Locale\Facades;

use Illuminate\Support\Facades\Facade;

class LocaleFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'tt-locale';
    }
}
