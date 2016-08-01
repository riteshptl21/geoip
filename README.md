# Maxmind GeoIP2 API For Laravel 4.2
## Description ##
[![Latest Stable Version](https://poser.pugx.org/riteshptl21/geoip/v/stable)](https://packagist.org/packages/riteshptl21/geoip) [![Total Downloads](https://poser.pugx.org/riteshptl21/geoip/downloads)](https://packagist.org/packages/riteshptl21/geoip)

Geoip is a Laravel package that aims to seamlessly integrate MaxMind functionality for determine the geographical location of IP addresses in laravel.



Installation
------------

Add geoip to your composer.json file:

```"riteshptl21/geoip": "0.1.*"```

You'll then need to run `composer install` to download it and have the autoloader updated.

Add the service provider to your Laravel application config:

```PHP
'Riteshptl21\Geoip\GeoipServiceProvider'
```

Create configuration file using artisan

~~~
$ php artisan config:publish riteshptl21/geoip
~~~

Once you fire this command you see config file at `app/config/packages/riteshptl21/geoip/config.php` and set config at there.

- **Database Service**: To use the database version of MaxMind services download the `GeoLite2-City.mmdb` from [http://dev.maxmind.com/geoip/geoip2/geolite2/](http://dev.maxmind.com/geoip/geoip2/geolite2/) extract it and set file path of `GeoLite2-City.mmdb` as `database_path` at config file.
- **Web Service**: To use the web service version of MaxMind services Please create account at [https://www.maxmind.com/en/geoip2-precision-services](https://www.maxmind.com/en/geoip2-precision-services) and set config.

## Usage

Get the location data for a website visitor:

```php
$location = Geoip::getLocation();
```

> When an IP is not given the `$_SERVER["REMOTE_ADDR"]` is used.

Getting the location data for a given IP:

```php
$location = Geoip::getLocation( '2.24.234.19' );
```

#### Note

In the case that a location is not found the fallback location will be returned with the `default_location` parameter that you set in config.By default default_location is like as under: 

```php
array (
    "ip"            => "127.0.0.0",
    "isoCode"       => "IN",
    "country"       => "India",
    "city"          => "Ahmedabad",
    "state_code"         => "GJ",
    "postal_code"   => "261201",
    "lat"           => 23.0333,
    "lon"           => 72.6167,
    "timezone"      => "Asia/Kolkata",
    "continent"     => "AS",
    "default"       => true
)
```





