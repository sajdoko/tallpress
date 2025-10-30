<?php

namespace Sajdoko\TallPress\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Sajdoko\TallPress\TallPress
 */
class TallPress extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Sajdoko\TallPress\TallPress::class;
    }
}
