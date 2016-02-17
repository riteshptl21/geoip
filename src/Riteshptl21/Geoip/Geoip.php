<?php namespace Riteshptl21\Geoip;

use GeoIp2\Database\Reader;
use GeoIp2\WebService\Client;

use GeoIp2\Exception\AddressNotFoundException;

use Illuminate\Config\Repository;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Facades\Response;

class Geoip
{

    /**
     * The session store.
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;

    /**
     * Illuminate config repository instance.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Remote Machine IP address.
     *
     * @var float
     */
    protected $remote_ip = null;

    /**
     * Location data.
     *
     * @var array
     */
    protected $location = null;

    /**
     * Reserved IP address.
     *
     * @var array
     */
    protected $reserved_ips = array(
        array('0.0.0.0', '0.255.255.255'),
        array('10.0.0.0', '10.255.255.255'),
        array('127.0.0.0', '127.255.255.255'),
        array('169.254.0.0', '169.254.255.255'),
        array('172.16.0.0', '172.31.255.255'),
        array('192.0.2.0', '192.0.2.255'),
        array('192.168.0.0', '192.168.255.255'),
        array('255.255.255.0', '255.255.255.255')
    );

    /**
     * Create a new GeoIP instance.
     *
     * @param  \Illuminate\Config\Repository $config
     * @param  \Illuminate\Session\Store $session
     */
    public function __construct(Repository $config, SessionStore $session)
    {
        $this->config = $config;
        $this->session = $session;

        $this->remote_ip = $this->getClientIP();
    }

    /**
     * Save location data in the session.
     *
     * @return void
     */
    function saveLocation()
    {
        $this->session->set('geoip-location', $this->location);
    }

    /**
     * Get location from IP.
     *
     * @param  string $ip Optional
     * @return array
     */
    function getLocation($ip = null)
    {
        // Get location data
        $this->location = $this->find($ip);

        // Save user's location
        if ($ip === null) {
            $this->saveLocation();
        }

        return $this->location;
    }

    /**
     * Find location from IP.
     *
     * @param  string $ip Optional
     * @return array
     */
    private function find($ip = null)
    {
        // Check Session
        if ($ip === null && $position = $this->session->get('geoip-location')) {
            return $position;
        }

        // If IP not set, user remote IP
        if ($ip === null) {
            $ip = $this->remote_ip;
        }

        //get config from config file
        $config['default_location'] = $this->config->get('geoip::default_location');
        $config['default_location']['ip'] = $ip;
        // Check if the ip is not local or empty
        if ($this->checkIp($ip)) {
            $locale = $this->config->get('geoip::locale');
            try{
                if ($this->config->get('geoip::type') === 'web_service') {
                    $maxmind = new Client($this->config->get('geoip::user_id'), $this->config->get('geoip::license_key'));
                } else {
                    $maxmind = new Reader($config['database_path']);
                }
            } catch(\Exception $e){
                return Response::json(['please check config'],400);
            }try {
                $iplocation = $maxmind->city($ip);
                $location = array(
                    "ip" => $ip,
                    "isoCode" => $iplocation->country->isoCode,
                    "country" => isset($iplocation->country->names[$locale])?$iplocation->country->names[$locale]:$iplocation->country->name,
                    "city" => isset($iplocation->city->names[$locale])?$iplocation->city->names[$locale]:$iplocation->city->name,
                    "state_code" => $iplocation->mostSpecificSubdivision->isoCode,
                    "postal_code" => $iplocation->postal->code,
                    "lat" => $iplocation->location->latitude,
                    "lon" => $iplocation->location->longitude,
                    "timezone" => $iplocation->location->timeZone,
                    "continent" => $iplocation->continent->code,
                    'isp'=> $iplocation->traits,
                    "default" => false
                );

            }
            catch (AddressNotFoundException $e)
            {
                return $config['default_location'];
            }

            unset($iplocation);

            return $location;
        }

        return $config['default_location'];
    }
    /**
     *  Get the county information from maxmind GeoIp2\Record\Country object
     */
    private function getCountry(){

    }
    /**
     * Get the client IP address.
     *
     * @return string
     */
    private function getClientIP()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = '127.0.0.0';
        }

        return $ipaddress;
    }

    /**
     * Checks if the ip is not local or empty.
     * @var $ip
     * @return bool
     */
    private function checkIp($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $longip = ip2long($ip);
            if (! empty($ip)) {
                foreach ($this->reserved_ips as $r) {
                    $min = ip2long($r[0]);
                    $max = ip2long($r[1]);
                    if ($longip >= $min && $longip <= $max) {
                        return false;
                    }
                }
                return true;
            }
        } else if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return true;
        }
        return false;
    }

}
