<?php namespace Riteshptl21\Geoip\Facades;

use Illuminate\Support\Facades\Facade;

class Geoip extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'geoip'; }

}