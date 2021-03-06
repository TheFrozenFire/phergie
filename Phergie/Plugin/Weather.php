<?php
/**
 * Phergie
 *
 * PHP version 5
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://phergie.org/license
 *
 * @category  Phergie
 * @package   Phergie_Plugin_Weather
 * @author    Phergie Development Team <team@phergie.org>
 * @author    Quentin B. <quentin@quentinboughner.com>
 * @copyright 2008-2011 Phergie Development Team (http://phergie.org)
 * @license   http://phergie.org/license New BSD License
 * @link      http://pear.phergie.org/package/Phergie_Plugin_Weather
 */

/**
 * Detects and responds to requests for current weather conditions in a
 * particular location using data from a web service.
 *
 * @category Phergie
 * @package  Phergie_Plugin_Weather
 * @author   Phergie Development Team <team@phergie.org>
 * @license  http://phergie.org/license New BSD License
 * @link     http://pear.phergie.org/package/Phergie_Plugin_Weather
 * @uses     Phergie_Plugin_Cache pear.phergie.org
 * @uses     Phergie_Plugin_Command pear.phergie.org
 * @uses     Phergie_Plugin_Http pear.phergie.org
 * @uses     Phergie_Plugin_Temperature pear.phergie.org
 */

class Phergie_Plugin_weather extends Phergie_Plugin_Abstract
{
    /**
    *True if the last location used is reliable
    *
    *@var bool
    */
    protected $isLocationReliable = false;

    /**
    *Loads dependancies
    *
    *@return void
    */
    public function onLoad()
    {
        $plugins = $this->getPluginHandler();
        $plugins -> getPlugin('Cache');
        $plugins -> getPlugin('Command');
        $plugins -> getPlugin('Http');
        $plugins -> getPlugin('Temperature');
    }
    /**
    *Returns a weather report for specified location on command 'weather'
    *
    *@param string $location Specifies a location
    *@return void
    */
    public function onCommandWeather($location)
    {
        try
        {
            $this->doPrivMsg(
                $this->event->getSource(),
                $this->event->getNick() . ':' . $this->getWeatherReport($location,$unit)
                );
        } catch(Phergie_Exception $e) {
            $this->doNotice($this->event->getNick(), $e->getMessage());
        }
    }

    /**
    *Generates weather report for specified location to be returned by command Weather
    *
    *@param string $location zip code or city/state/country specification
    *@return void
    */
    protected function getWeatherReport($location)
    {
        $location = urlencode($location);
        $conditions = $this->getWeatherData($location);

        $report = ' Weather for ' . $conditions['city_name'] . ' - ';

        $report .= 'Current conditions: ' . $conditions['current_conditions'] . ' - ';
        $report .= 'Temperature: ' . $conditions['temp_c'] . '*C (' . $conditions['temp_f'] . '*F) - ';
        $report .= $conditions['humidity'];
        return $report;
    }

    /**
    *Retrieves weather data via Weather Channel API
    *
    *@param string $location zip code or city/state/country specification
    *@return void
    */
    public function getWeatherData($location)
    {
        $response = $this->getPluginHandler()
            ->getPlugin('Http')
            ->get('http://www.google.com/ig/api?weather=' . $location);
        $data  = $response->getContent();

        $weather = $data->weather;
        $condition = $weather->current_conditions;
        $conditions = array(
        'city_name'=> (string) $weather->forecast_information->city['data'],
        'current_conditions'=>(string) $condition->condition['data'],
        'temp_f'=> (string) $condition->temp_f['data'],
        'temp_c'=> (string) $condition->temp_c['data'],
        'humidity'=> (string) $condition->humidity['data'],
        );
        return $conditions;

    }
}
?>