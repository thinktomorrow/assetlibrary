<?php

namespace Thinktomorrow\Locale\Facades;

use Illuminate\Support\Facades\Facade;

class LocaleUrlFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'tt-locale-url';
    }
}
