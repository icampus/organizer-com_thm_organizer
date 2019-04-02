<?php
/*********************************************************************************/
/**
 *
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * @copyright Copyright (c) 2007-2015 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      http://kigkonsult.se/iCalcreator/index.php
 * @license   http://kigkonsult.se/downloads/dl.php?f=LGPL
 * @package   iCalcreator
 * @version   2.22
 */
/**
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
/*********************************************************************************/

/**
 * vcalendar class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 */
class vcalendar extends iCalBase
{
    /**
     * @var array $calscale calendar property variable
     * @var array $method   calendar property variable
     * @var array $prodid   calendar property variable
     * @var array $version  calendar property variable
     * @access private
     */
    private $calscale;
    private $method;
    private $prodid;
    private $version;
    /**
     * @var array $directory calendar config variable
     * @var array $filename  calendar config variable
     * @var array $url       calendar config variable
     * @access private
     */
    private $directory;
    private $filename;
    private $url;
    /**
     * redirect headers
     *
     * @var array $headers
     * @access private
     * @static
     */
    private static $headers = [
        'Content-Encoding: gzip',
        'Vary: *',
        'Content-Length: %s',
        'Content-Type: application/calendar+xml; charset=utf-8',
        'Content-Type: text/calendar; charset=utf-8',
        'Content-Disposition: attachment; filename="%s"',
        'Content-Disposition: inline; filename="%s"',
        'Cache-Control: max-age=10',
    ];

    /**
     * constructor for calendar object
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param array $config
     *
     * @uses   vcalendar::_makeVersion()
     * @uses   vcalendar::$calscale
     * @uses   vcalendar::$method
     * @uses   vcalendar::_makeUnique_id()
     * @uses   vcalendar::$prodid
     * @uses   vcalendar::$xprop
     * @uses   vcalendar::$language
     * @uses   vcalendar::$directory
     * @uses   vcalendar::$filename
     * @uses   vcalendar::$url
     * @uses   vcalendar::$dtzid
     * @uses   vcalendar::setConfig()
     * @uses   vcalendar::$xcaldecl
     * @uses   vcalendar::$components
     */
    function __construct($config = [])
    {
        $this->_makeVersion();
        $this->calscale = null;
        $this->method   = null;
        $this->_makeUnique_id();
        $this->prodid    = null;
        $this->xprop     = [];
        $this->language  = null;
        $this->directory = '.';
        $this->filename  = null;
        $this->url       = null;
        $this->dtzid     = null;
        /**
         *   language = <Text identifying a language, as defined in [RFC 1766]>
         */
        if (defined('ICAL_LANG') && !isset($config['language'])) {
            $config['language'] = ICAL_LANG;
        }
        if (!isset($config['allowEmpty'])) {
            $config['allowEmpty'] = true;
        }
        if (!isset($config['nl'])) {
            $config['nl'] = "\r\n";
        }
        if (!isset($config['format'])) {
            $config['format'] = 'iCal';
        }
        if (!isset($config['delimiter'])) {
            $config['delimiter'] = DIRECTORY_SEPARATOR;
        }
        $this->setConfig($config);
        $this->xcaldecl   = [];
        $this->components = [];
    }

    /**
     * return iCalcreator version number
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @uses   ICALCREATOR_VERSION
     * @return string
     */
    public static function iCalcreatorVersion()
    {
        return trim(substr(ICALCREATOR_VERSION, strpos(ICALCREATOR_VERSION, ' ')));
    }
    /*********************************************************************************/
    /**
     * Property Name: CALSCALE
     */
    /**
     * creates formatted output for calendar property calscale
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @uses   vcalendar::$calscale
     * @uses   vcalendar::$format
     * @uses   vcalendar::$nl
     * @return string
     */
    function createCalscale()
    {
        if (empty($this->calscale)) {
            return false;
        }
        switch ($this->format) {
            case 'xcal':
                return $this->nl . ' calscale="' . $this->calscale . '"';
                break;
            default:
                return 'CALSCALE:' . $this->calscale . $this->nl;
                break;
        }
    }

    /**
     * set calendar property calscale
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param string $value
     *
     * @uses   vcalendar::$calscale
     * @return void
     */
    function setCalscale($value)
    {
        if (empty($value)) {
            return false;
        }
        $this->calscale = $value;
    }
    /*********************************************************************************/
    /**
     * Property Name: METHOD
     */
    /**
     * creates formatted output for calendar property method
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @uses   vcalendar::$method
     * @uses   vcalendar::$format
     * @uses   vcalendar::$nl
     * @return string
     */
    function createMethod()
    {
        if (empty($this->method)) {
            return false;
        }
        switch ($this->format) {
            case 'xcal':
                return $this->nl . ' method="' . $this->method . '"';
                break;
            default:
                return 'METHOD:' . $this->method . $this->nl;
                break;
        }
    }

    /**
     * set calendar property method
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param string $value
     *
     * @uses   vcalendar::$method
     * @return bool
     */
    function setMethod($value)
    {
        if (empty($value)) {
            return false;
        }
        $this->method = $value;

        return true;
    }
    /*********************************************************************************/
    /**
     * Property Name: PRODID
     *
     */
    /**
     * creates formatted output for calendar property prodid
     *
     * @copyright copyright (c) 2007-2013 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
     * @license   http://kigkonsult.se/downloads/dl.php?f=LGPL
     * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @uses      vcalendar::$prodid
     * @uses      vcalendar::_makeProdid()
     * @uses      vcalendar::$format
     * @uses      vcalendar::$nl
     * @uses      vcalendar::_createElement()
     * @return string
     */
    function createProdid()
    {
        if (!isset($this->prodid)) {
            $this->_makeProdid();
        }
        switch ($this->format) {
            case 'xcal':
                return $this->nl . ' prodid="' . $this->prodid . '"';
                break;
            default:
                return $this->_createElement('PRODID', '', $this->prodid);
                break;
        }
    }

    /**
     * make default value for calendar prodid, do NOT alter or remove this method or invoke of this method
     *
     * @copyright copyright (c) 2007-2013 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
     * @license   http://kigkonsult.se/downloads/dl.php?f=LGPL
     * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @uses      vcalendar::$prodid
     * @uses      vcalendar::$unique_id
     * @uses      ICALCREATOR_VERSION
     * @uses      vcalendar::$language
     * @return void
     */
    function _makeProdid()
    {
        $this->prodid = '-//' . $this->unique_id . '//NONSGML kigkonsult.se ' . ICALCREATOR_VERSION . '//' . strtoupper($this->language);
    }
    /**
     * Conformance: The property MUST be specified once in an iCalendar object.
     * Description: The vendor of the implementation SHOULD assure that this
     * is a globally unique identifier; using some technique such as an FPI
     * value, as defined in [ISO 9070].
     */
    /**
     * make default unique_id for calendar prodid
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @uses   vcalendar::$unique_id
     * @return void
     */
    function _makeUnique_id()
    {
        $this->unique_id = (isset($_SERVER['SERVER_NAME'])) ? gethostbyname($_SERVER['SERVER_NAME']) : 'localhost';
    }
    /*********************************************************************************/
    /**
     * Property Name: VERSION
     *
     * Description: A value of "2.0" corresponds to this memo.
     */
    /**
     * creates formatted output for calendar property version
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @uses   vcalendar::$version
     * @uses   vcalendar::$format
     * @uses   vcalendar::$nl
     * @return string
     */
    function createVersion()
    {
        if (empty($this->version)) {
            $this->_makeVersion();
        }
        switch ($this->format) {
            case 'xcal':
                return $this->nl . ' version="' . $this->version . '"';
                break;
            default:
                return 'VERSION:' . $this->version . $this->nl;
                break;
        }
    }

    /**
     * set default calendar version
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @uses   vcalendar::$version
     * @return void
     */
    function _makeVersion()
    {
        $this->version = '2.0';
    }

    /**
     * set calendar version
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param string $value
     *
     * @uses   vcalendar::$version
     * @return void
     */
    function setVersion($value)
    {
        if (empty($value)) {
            return false;
        }
        $this->version = $value;

        return true;
    }
    /*********************************************************************************/
    /*********************************************************************************/
    /**
     * delete calendar property value
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param mixed $propName bool FALSE => X-property
     * @param int   $propix   specific property in case of multiply occurences
     *
     * @uses   vcalendar::$propdelix
     * @uses   vcalendar::$calscale
     * @uses   vcalendar::$method
     * @uses   vcalendar::$xprop
     * @return bool, if successfull delete
     */
    function deleteProperty($propName = false, $propix = false)
    {
        $propName = ($propName) ? strtoupper($propName) : 'X-PROP';
        if (!$propix) {
            $propix = (isset($this->propdelix[$propName]) && ('X-PROP' != $propName)) ? $this->propdelix[$propName] + 2 : 1;
        }
        $this->propdelix[$propName] = --$propix;
        $return                     = false;
        switch ($propName) {
            case 'CALSCALE':
                if (isset($this->calscale)) {
                    $this->calscale = null;
                    $return         = true;
                }
                break;
            case 'METHOD':
                if (isset($this->method)) {
                    $this->method = null;
                    $return       = true;
                }
                break;
            default:
                $reduced = [];
                if ($propName != 'X-PROP') {
                    if (!isset($this->xprop[$propName])) {
                        unset($this->propdelix[$propName]);

                        return false;
                    }
                    foreach ($this->xprop as $k => $a) {
                        if (($k != $propName) && !empty($a)) {
                            $reduced[$k] = $a;
                        }
                    }
                } else {
                    if (count($this->xprop) <= $propix) {
                        return false;
                    }
                    $xpropno = 0;
                    foreach ($this->xprop as $xpropkey => $xpropvalue) {
                        if ($propix != $xpropno) {
                            $reduced[$xpropkey] = $xpropvalue;
                        }
                        $xpropno++;
                    }
                }
                $this->xprop = $reduced;
                if (empty($this->xprop)) {
                    unset($this->propdelix[$propName]);

                    return false;
                }

                return true;
        }

        return $return;
    }

    /**
     * get calendar property value/params
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param string $propName
     * @param int    $propix specific property in case of multiply occurences
     * @param bool   $inclParam
     *
     * @uses   vcalendar::$propix
     * @uses   vcalendar::$components
     * @uses   calendarComponent::$objName
     * @uses   iCalUtilityFunctions::$vComps
     * @uses   iCalUtilityFunctions::$mProps1
     * @uses   calendarComponent::_getProperties()
     * @uses   calendarComponent::getProperty()
     * @uses   iCalUtilityFunctions::_geo2str2()
     * @uses   iCalUtilityFunctions::$geoLatFmt
     * @uses   iCalUtilityFunctions::$geoLongFmt
     * @uses   iCalUtilityFunctions::$fmt
     * @uses   vcalendar::$calscale
     * @uses   vcalendar::$method
     * @uses   vcalendar::$prodid
     * @uses   vcalendar::_makeProdid()
     * @uses   vcalendar::$version
     * @uses   vcalendar::$xprop
     * @return mixed
     */
    function getProperty($propName = false, $propix = false, $inclParam = false)
    {
        $propName = ($propName) ? strtoupper($propName) : 'X-PROP';
        if ('X-PROP' == $propName) {
            if (empty($propix)) {
                $propix = (isset($this->propix[$propName])) ? $this->propix[$propName] + 2 : 1;
            }
            $this->propix[$propName] = --$propix;
        }
        switch ($propName) {
            case 'ATTENDEE':
            case 'CATEGORIES':
            case 'CONTACT':
            case 'DTSTART':
            case 'GEOLOCATION':
            case 'LOCATION':
            case 'ORGANIZER':
            case 'PRIORITY':
            case 'RESOURCES':
            case 'STATUS':
            case 'SUMMARY':
            case 'RECURRENCE-ID-UID':
            case 'RELATED-TO':
            case 'R-UID':
            case 'UID':
            case 'URL':
                $output = [];
                foreach ($this->components as $cix => $component) {
                    if (!in_array($component->objName, iCalUtilityFunctions::$vComps)) {
                        continue;
                    }
                    if (in_array($propName, iCalUtilityFunctions::$mProps1)) {
                        $component->_getProperties($propName, $output);
                        continue;
                    } elseif ((3 < strlen($propName)) && ('UID' == substr($propName, -3))) {
                        if (false !== ($content = $component->getProperty('RECURRENCE-ID'))) {
                            $content = $component->getProperty('UID');
                        }
                    } elseif ('GEOLOCATION' == $propName) {
                        $content = (false === ($loc = $component->getProperty('LOCATION'))) ? '' : $loc . ' ';
                        if (false === ($geo = $component->getProperty('GEO'))) {
                            continue;
                        }
                        $content .= iCalUtilityFunctions::_geo2str2($geo['latitude'],
                                iCalUtilityFunctions::$geoLatFmt) .
                            iCalUtilityFunctions::_geo2str2($geo['longitude'], iCalUtilityFunctions::$geoLongFmt) . '/';
                    } elseif (false === ($content = $component->getProperty($propName))) {
                        continue;
                    }
                    if ((false === $content) || empty($content)) {
                        continue;
                    } elseif (is_array($content)) {
                        if (isset($content['year'])) {
                            $key = sprintf(iCalUtilityFunctions::$fmt['Ymd'], (int)$content['year'],
                                (int)$content['month'], (int)$content['day']);
                            if (!isset($output[$key])) {
                                $output[$key] = 1;
                            } else {
                                $output[$key] += 1;
                            }
                        } else {
                            foreach ($content as $partValue => $partCount) {
                                if (!isset($output[$partValue])) {
                                    $output[$partValue] = $partCount;
                                } else {
                                    $output[$partValue] += $partCount;
                                }
                            }
                        }
                    } // end elseif( is_array( $content )) {
                    elseif (!isset($output[$content])) {
                        $output[$content] = 1;
                    } else {
                        $output[$content] += 1;
                    }
                } // end foreach ( $this->components as $cix => $component)
                if (!empty($output)) {
                    ksort($output);
                }

                return $output;
                break;
            case 'CALSCALE':
                return (!empty($this->calscale)) ? $this->calscale : false;
                break;
            case 'METHOD':
                return (!empty($this->method)) ? $this->method : false;
                break;
            case 'PRODID':
                if (empty($this->prodid)) {
                    $this->_makeProdid();
                }

                return $this->prodid;
                break;
            case 'VERSION':
                return (!empty($this->version)) ? $this->version : false;
                break;
            default:
                if ($propName != 'X-PROP') {
                    if (!isset($this->xprop[$propName])) {
                        return false;
                    }

                    return ($inclParam) ? [$propName, $this->xprop[$propName]]
                        : [$propName, $this->xprop[$propName]['value']];
                } else {
                    if (empty($this->xprop)) {
                        return false;
                    }
                    $xpropno = 0;
                    foreach ($this->xprop as $xpropkey => $xpropvalue) {
                        if ($propix == $xpropno) {
                            return ($inclParam) ? [$xpropkey, $this->xprop[$xpropkey]]
                                : [$xpropkey, $this->xprop[$xpropkey]['value']];
                        } else {
                            $xpropno++;
                        }
                    }
                    unset($this->propix[$propName]);

                    return false; // not found ??
                }
        }

        return false;
    }

    /**
     * general vcalendar property setting
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param mixed $args variable number of function arguments,
     *                    first argument is ALWAYS component name,
     *                    second ALWAYS component value!
     *
     * @uses   vcalendar::setCalscale()
     * @uses   vcalendar::setMethod()
     * @uses   vcalendar::setVersion()
     * @uses   vcalendar::setXprop()
     * @return bool
     */
    function setProperty()
    {
        $numargs = func_num_args();
        if (1 > $numargs) {
            return false;
        }
        $arglist    = func_get_args();
        $arglist[0] = strtoupper($arglist[0]);
        switch ($arglist[0]) {
            case 'CALSCALE':
                return $this->setCalscale($arglist[1]);
            case 'METHOD':
                return $this->setMethod($arglist[1]);
            case 'VERSION':
                return $this->setVersion($arglist[1]);
            default:
                if (!isset($arglist[1])) {
                    $arglist[1] = null;
                }
                if (!isset($arglist[2])) {
                    $arglist[2] = null;
                }

                return $this->setXprop($arglist[0], $arglist[1], $arglist[2]);
        }

        return false;
    }
    /*********************************************************************************/
    /**
     * get vcalendar config values or * calendar components
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param mixed $config
     *
     * @uses   vcalendar::getConfig()
     * @uses   vcalendar::$allowEmpty
     * @uses   vcalendar::$components
     * @uses   calendarComponent::_getProperties()
     * @uses   calendarComponent::$objName
     * @uses   calendarComponent::getProperty()
     * @uses   calendarComponent::_getConfig()
     * @uses   vcalendar::$url
     * @uses   vcalendar::$delimiter
     * @uses   vcalendar::$directory
     * @uses   vcalendar::$filename
     * @uses   vcalendar::$format
     * @uses   vcalendar::$language
     * @uses   vcalendar::$nl
     * @uses   vcalendar::$dtzid
     * @uses   vcalendar::$unique_id
     * @return value
     */
    function getConfig($config = false)
    {
        if (!$config) {
            $return               = [];
            $return['ALLOWEMPTY'] = $this->getConfig('ALLOWEMPTY');
            $return['DELIMITER']  = $this->getConfig('DELIMITER');
            $return['DIRECTORY']  = $this->getConfig('DIRECTORY');
            $return['FILENAME']   = $this->getConfig('FILENAME');
            $return['DIRFILE']    = $this->getConfig('DIRFILE');
            $return['FILESIZE']   = $this->getConfig('FILESIZE');
            $return['FORMAT']     = $this->getConfig('FORMAT');
            if (false !== ($lang = $this->getConfig('LANGUAGE'))) {
                $return['LANGUAGE'] = $lang;
            }
            $return['NEWLINECHAR'] = $this->getConfig('NEWLINECHAR');
            $return['UNIQUE_ID']   = $this->getConfig('UNIQUE_ID');
            if (false !== ($url = $this->getConfig('URL'))) {
                $return['URL'] = $url;
            }
            $return['TZID'] = $this->getConfig('TZID');

            return $return;
        }
        switch (strtoupper($config)) {
            case 'ALLOWEMPTY':
                return $this->allowEmpty;
                break;
            case 'COMPSINFO':
                unset($this->compix);
                $info = [];
                foreach ($this->components as $cix => $component) {
                    if (empty($component)) {
                        continue;
                    }
                    $info[$cix]['ordno'] = $cix + 1;
                    $info[$cix]['type']  = $component->objName;
                    $info[$cix]['uid']   = $component->getProperty('uid');
                    $info[$cix]['props'] = $component->getConfig('propinfo');
                    $info[$cix]['sub']   = $component->getConfig('compsinfo');
                }

                return $info;
                break;
            case 'DELIMITER':
                return $this->delimiter;
                break;
            case 'DIRECTORY':
                if (empty($this->directory) && ('0' != $this->directory)) {
                    $this->directory = '.';
                }

                return $this->directory;
                break;
            case 'DIRFILE':
                return $this->getConfig('directory') . $this->getConfig('delimiter') . $this->getConfig('filename');
                break;
            case 'FILEINFO':
                return [$this->getConfig('directory'), $this->getConfig('filename'), $this->getConfig('filesize')];
                break;
            case 'FILENAME':
                if (empty($this->filename) && ('0' != $this->filename)) {
                    if ('xcal' == $this->format) {
                        $this->filename = date('YmdHis') . '.xml';
                    } // recommended xcs.. .
                    else {
                        $this->filename = date('YmdHis') . '.ics';
                    }
                }

                return $this->filename;
                break;
            case 'FILESIZE':
                $size = 0;
                if (empty($this->url)) {
                    $dirfile = $this->getConfig('dirfile');
                    if (!is_file($dirfile) || (false === ($size = filesize($dirfile)))) {
                        $size = 0;
                    }
                    clearstatcache();
                }

                return $size;
                break;
            case 'FORMAT':
                return ($this->format == 'xcal') ? 'xCal' : 'iCal';
                break;
            case 'LANGUAGE':
                /* get language for calendar component as defined in [RFC 1766] */
                return $this->language;
                break;
            case 'NL':
            case 'NEWLINECHAR':
                return $this->nl;
                break;
            case 'TZID':
                return $this->dtzid;
                break;
            case 'UNIQUE_ID':
                return $this->unique_id;
                break;
            case 'URL':
                if (!empty($this->url)) {
                    return $this->url;
                } else {
                    return false;
                }
                break;
        }
    }

    /**
     * general vcalendar config setting
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param mixed  $config
     * @param string $value
     *
     * @uses   vcalendar::setConfig()
     * @uses   vcalendar::$allowEmpty
     * @uses   vcalendar::$components
     * @uses   vcalendar::$url
     * @uses   vcalendar::$delimiter
     * @uses   vcalendar::$directory
     * @uses   vcalendar::$filename
     * @uses   vcalendar::$format
     * @uses   vcalendar::$language
     * @uses   vcalendar::$nl
     * @uses   vcalendar::$dtzid
     * @uses   vcalendar::$unique_id
     * @uses   vcalendar::_makeProdid()
     * @uses   vcalendar::$components
     * @uses   calendarComponent::setConfig()
     * @uses   calendarComponent::copy()
     * @return void
     */
    function setConfig($config, $value = false)
    {
        if (is_array($config)) {
            $config = array_change_key_case($config, CASE_UPPER);
            if (isset($config['DELIMITER'])) {
                if (false === $this->setConfig('DELIMITER', $config['DELIMITER'])) {
                    return false;
                }
                unset($config['DELIMITER']);
            }
            if (isset($config['DIRECTORY'])) {
                if (false === $this->setConfig('DIRECTORY', $config['DIRECTORY'])) {
                    return false;
                }
                unset($config['DIRECTORY']);
            }
            foreach ($config as $cKey => $cValue) {
                if (false === $this->setConfig($cKey, $cValue)) {
                    return false;
                }
            }

            return true;
        } else {
            $res = false;
        }
        $config = strtoupper($config);
        switch ($config) {
            case 'ALLOWEMPTY':
                $this->allowEmpty = $value;
                $subcfg           = ['ALLOWEMPTY' => $value];
                $res              = true;
                break;
            case 'DELIMITER':
                $this->delimiter = $value;

                return true;
                break;
            case 'DIRECTORY':
                if (false === ($value = realpath(rtrim(trim($value), $this->delimiter)))) {
                    return false;
                } else {
                    /* local directory */
                    $this->directory = $value;
                    $this->url       = null;

                    return true;
                }
                break;
            case 'FILENAME':
                $value   = trim($value);
                $dirfile = $this->directory . $this->delimiter . $value;
                if (file_exists($dirfile)) {
                    /* local file exists */
                    if (is_readable($dirfile) || is_writable($dirfile)) {
                        clearstatcache();
                        $this->filename = $value;

                        return true;
                    } else {
                        return false;
                    }
                } elseif (is_readable($this->directory) || is_writable($this->directory)) {
                    /* read- or writable directory */
                    clearstatcache();
                    $this->filename = $value;

                    return true;
                } else {
                    return false;
                }
                break;
            case 'FORMAT':
                $value = trim(strtolower($value));
                if ('xcal' == $value) {
                    $this->format             = 'xcal';
                    $this->attributeDelimiter = $this->nl;
                    $this->valueInit          = null;
                } else {
                    $this->format             = null;
                    $this->attributeDelimiter = ';';
                    $this->valueInit          = ':';
                }
                $subcfg = ['FORMAT' => $value];
                $res    = true;
                break;
            case 'LANGUAGE': // set language for calendar component as defined in [RFC 1766]
                $value          = trim($value);
                $this->language = $value;
                $this->_makeProdid();
                $subcfg = ['LANGUAGE' => $value];
                $res    = true;
                break;
            case 'NL':
            case 'NEWLINECHAR':
                $this->nl = $value;
                if ('xcal' == $value) {
                    $this->attributeDelimiter = $this->nl;
                    $this->valueInit          = null;
                } else {
                    $this->attributeDelimiter = ';';
                    $this->valueInit          = ':';
                }
                $subcfg = ['NL' => $value];
                $res    = true;
                break;
            case 'TZID':
                $this->dtzid = $value;
                $subcfg      = ['TZID' => $value];
                $res         = true;
                break;
            case 'UNIQUE_ID':
                $value           = trim($value);
                $this->unique_id = $value;
                $this->_makeProdid();
                $subcfg = ['UNIQUE_ID' => $value];
                $res    = true;
                break;
            case 'URL':
                /* remote file - URL */
                $value = str_replace(['HTTP://', 'WEBCAL://', 'webcal://'], 'http://', trim($value));
                $value = str_replace('HTTPS://', 'https://', trim($value));
                if (('http://' != substr($value, 0, 7)) && ('https://' != substr($value, 0, 8))) {
                    return false;
                }
                $this->directory = '.';
                $this->url       = $value;
                if ('.ics' != strtolower(substr($value, -4))) {
                    unset($this->filename);
                } else {
                    $this->filename = basename($value);
                }

                return true;
                break;
            default:  // any unvalid config key.. .
                return true;
        }
        if (!$res) {
            return false;
        }
        if (isset($subcfg) && !empty($this->components)) {
            foreach ($subcfg as $cfgkey => $cfgvalue) {
                foreach ($this->components as $cix => $component) {
                    $res = $component->setConfig($cfgkey, $cfgvalue, true);
                    if (!$res) {
                        break 2;
                    }
                    $this->components[$cix] = $component->copy(); // PHP4 compliant
                }
            }
        }

        return $res;
    }
    /*********************************************************************************/
    /**
     * add calendar component to container
     *
     * alias to setComponent
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param object $component calendar component
     *
     * @uses   vcalendar::setComponent()
     * @return void
     */
    function addComponent($component)
    {
        $this->setComponent($component);
    }

    /**
     * delete calendar component from container
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param mixed $arg1 ordno / component type / component uid
     * @param mixed $arg2 optional, ordno if arg1 = component type
     *
     * @uses   vcalendar::$components
     * @uses   calendarComponent::$objName
     * @uses   calendarComponent::getProperty()
     * @return void
     */
    function deleteComponent($arg1, $arg2 = false)
    {
        $argType = $index = null;
        if (ctype_digit((string)$arg1)) {
            $argType = 'INDEX';
            $index   = (int)$arg1 - 1;
        } elseif ((strlen($arg1) <= strlen('vfreebusy')) && (false === strpos($arg1, '@'))) {
            $argType = strtolower($arg1);
            $index   = (!empty($arg2) && ctype_digit((string)$arg2)) ? (( int )$arg2 - 1) : 0;
        }
        $cix1dC = 0;
        foreach ($this->components as $cix => $component) {
            if (empty($component)) {
                continue;
            }
            if (('INDEX' == $argType) && ($index == $cix)) {
                unset($this->components[$cix]);

                return true;
            } elseif ($argType == $component->objName) {
                if ($index == $cix1dC) {
                    unset($this->components[$cix]);

                    return true;
                }
                $cix1dC++;
            } elseif (!$argType && ($arg1 == $component->getProperty('uid'))) {
                unset($this->components[$cix]);

                return true;
            }
        }

        return false;
    }

    /**
     * get calendar component from container
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param mixed $arg1 optional, ordno/component type/ component uid
     * @param mixed $arg2 optional, ordno if arg1 = component type
     *
     * @uses   vcalendar::$compix
     * @uses   vcalendar::$components
     * @uses   iCalUtilityFunctions::$dateProps
     * @uses   iCalUtilityFunctions::$otherProps
     * @uses   iCalUtilityFunctions::$mProps1
     * @uses   calendarComponent::copy()
     * @uses   calendarComponent::$objName
     * @uses   calendarComponent::_getProperties()
     * @uses   calendarComponent::getProperty()
     * @uses   iCalUtilityFunctions::$fmt
     * @return object
     */
    function getComponent($arg1 = false, $arg2 = false)
    {
        $index = $argType = null;
        if (!$arg1) { // first or next in component chain
            $argType = 'INDEX';
            $index   = $this->compix['INDEX'] = (isset($this->compix['INDEX'])) ? $this->compix['INDEX'] + 1 : 1;
        } elseif (is_array($arg1)) {
            $arg2  = implode('-', array_keys($arg1));
            $index = $this->compix[$arg2] = (isset($this->compix[$arg2])) ? $this->compix[$arg2] + 1 : 1;
        } elseif (ctype_digit((string)$arg1)) { // specific component in chain
            $argType = 'INDEX';
            $index   = (int)$arg1;
            unset($this->compix);
        } elseif ((strlen($arg1) <= strlen('vfreebusy')) && (false === strpos($arg1, '@'))) { // object class name
            unset($this->compix['INDEX']);
            $argType = strtolower($arg1);
            if (!$arg2) {
                $index = $this->compix[$argType] = (isset($this->compix[$argType])) ? $this->compix[$argType] + 1 : 1;
            } elseif (isset($arg2) && ctype_digit((string)$arg2)) {
                $index = (int)$arg2;
            }
        } elseif ((strlen($arg1) > strlen('vfreebusy')) && (false !== strpos($arg1, '@'))) { // UID as 1st argument
            if (!$arg2) {
                $index = $this->compix[$arg1] = (isset($this->compix[$arg1])) ? $this->compix[$arg1] + 1 : 1;
            } elseif (isset($arg2) && ctype_digit((string)$arg2)) {
                $index = (int)$arg2;
            }
        }
        if (isset($index)) {
            $index -= 1;
        }
        $ckeys = array_keys($this->components);
        if (!empty($index) && ($index > end($ckeys))) {
            return false;
        }
        $cix1gC = 0;
        foreach ($this->components as $cix => $component) {
            if (empty($component)) {
                continue;
            }
            if (('INDEX' == $argType) && ($index == $cix)) {
                return $component->copy();
            } elseif ($argType == $component->objName) {
                if ($index == $cix1gC) {
                    return $component->copy();
                }
                $cix1gC++;
            } elseif (is_array($arg1)) {
                $hit  = [];
                $arg1 = array_change_key_case($arg1, CASE_UPPER);
                foreach ($arg1 as $pName => $pValue) {
                    if (!in_array($pName, iCalUtilityFunctions::$dateProps) && !in_array($pName,
                            iCalUtilityFunctions::$otherProps)) {
                        continue;
                    }
                    if (in_array($pName, iCalUtilityFunctions::$mProps1)) { // multiple occurrence
                        $propValues = [];
                        $component->_getProperties($pName, $propValues);
                        $propValues = array_keys($propValues);
                        $hit[]      = (in_array($pValue, $propValues)) ? true : false;
                        continue;
                    } // end   if(.. .// multiple occurrence
                    if (false === ($value = $component->getProperty($pName))) { // single occurrence
                        $hit[] = false; // missing property
                        continue;
                    }
                    if ('SUMMARY' == $pName) { // exists within (any case)
                        $hit[] = (false !== stripos($value, $pValue)) ? true : false;
                        continue;
                    }
                    if (in_array($pName, iCalUtilityFunctions::$dateProps)) {
                        $valuedate = sprintf(iCalUtilityFunctions::$fmt['Ymd'], (int)$value['year'],
                            (int)$value['month'], (int)$value['day']);
                        if (8 < strlen($pValue)) {
                            if (isset($value['hour'])) {
                                if ('T' == substr($pValue, 8, 1)) {
                                    $pValue = str_replace('T', '', $pValue);
                                }
                                $valuedate .= sprintf(iCalUtilityFunctions::$fmt['His'], (int)$value['hour'],
                                    (int)$value['min'], (int)$value['sec']);
                            } else {
                                $pValue = substr($pValue, 0, 8);
                            }
                        }
                        $hit[] = ($pValue == $valuedate) ? true : false;
                        continue;
                    } elseif (!is_array($value)) {
                        $value = [$value];
                    }
                    foreach ($value as $part) {
                        $part = (false !== strpos($part, ',')) ? explode(',', $part) : [$part];
                        foreach ($part as $subPart) {
                            if ($pValue == $subPart) {
                                $hit[] = true;
                                continue 3;
                            }
                        }
                    } // end foreach( $value as $part )
                    $hit[] = false; // no hit in property
                } // end  foreach( $arg1 as $pName => $pValue )
                if (in_array(true, $hit)) {
                    if ($index == $cix1gC) {
                        return $component->copy();
                    }
                    $cix1gC++;
                }
            } elseif (!$argType && ($arg1 == $component->getProperty('uid'))) { // UID
                if ($index == $cix1gC) {
                    return $component->copy();
                }
                $cix1gC++;
            }
        } // end foreach ( $this->components.. .
        /* not found.. . */
        unset($this->compix);

        return false;
    }

    /**
     * create new calendar component, already included within calendar
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param string $compType component type
     *
     * @uses   vcalendar::$components
     * @return object
     */
    function & newComponent($compType)
    {
        $config = $this->getConfig();
        $keys   = array_keys($this->components);
        $ix     = (empty($keys)) ? 0 : end($keys) + 1;
        switch (strtoupper($compType)) {
            case 'EVENT':
            case 'VEVENT':
                $this->components[$ix] = new \vevent($config);
                break;
            case 'TODO':
            case 'VTODO':
                $this->components[$ix] = new \vtodo($config);
                break;
            case 'JOURNAL':
            case 'VJOURNAL':
                $this->components[$ix] = new \vjournal($config);
                break;
            case 'FREEBUSY':
            case 'VFREEBUSY':
                $this->components[$ix] = new \vfreebusy($config);
                break;
            case 'TIMEZONE':
            case 'VTIMEZONE':
                array_unshift($this->components, new \vtimezone($config));
                $ix = 0;
                break;
            default:
                return false;
        }

        return $this->components[$ix];
    }

    /**
     * select components from calendar on date or selectOption basis
     *
     * Ensure DTSTART is set for every component.
     * No date controls occurs.
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param mixed $startY                            optional,      (int) start Year,  default current Year
     *                                                 ALT. (obj) start date (datetime)
     *                                                 ALT. array selecOptions ( *[ <propName> => <uniqueValue> ] )
     * @param mixed $startM                            optional,      (int) start Month, default current Month
     *                                                 ALT. (obj) end date (datetime)
     * @param int   $startD                            optional, start Day,   default current Day
     * @param int   $endY                              optional, end   Year,  default $startY
     * @param int   $endM                              optional, end   Month, default $startM
     * @param int   $endD                              optional, end   Day,   default $startD
     * @param mixed $cType                             optional, calendar component type(-s), default FALSE=all else string/array type(-s)
     * @param bool  $flat                              optional, FALSE (default) => output : array[Year][Month][Day][]
     *                                                 TRUE            => output : array[] (ignores split)
     * @param bool  $any                               optional, TRUE (default) - select component(-s) that occurs within period
     *                                                 FALSE          - only component(-s) that starts within period
     * @param bool  $split                             optional, TRUE (default) - one component copy every DAY it occurs during the
     *                                                 period (implies flat=FALSE)
     *                                                 FALSE          - one occurance of component only in output array
     *
     * @uses   vcalendar::$components
     * @uses   vcalendar::selectComponents2()
     * @uses   iCalUtilityFunctions::$vComps
     * @uses   calendarComponent::$objName
     * @uses   calendarComponent::getProperty()
     * @uses   iCaldateTime::factory()
     * @uses   iCaldateTime::getTimezoneName()
     * @uses   iCaldateTime::getTime()
     * @uses   iCalUtilityFunctions::$fmt
     * @uses   iCaldateTime::$SCbools
     * @uses   iCaldateTime::format()
     * @uses   iCalUtilityFunctions::_strDate2arr()
     * @uses   iCalUtilityFunctions::_recur2date()
     * @uses   iCalUtilityFunctions::_inScope()
     * @uses   calendarComponent::copy()
     * @uses   calendarComponent::setProperty()
     * @uses   iCalUtilityFunctions::$fmt
     * @uses   calendarComponent::deleteProperty()
     * @uses   iCalUtilityFunctions::_setSortArgs()
     * @uses   iCalUtilityFunctions::_cmpfcn()
     * @return array or FALSE
     */
    function selectComponents(
        $startY = false,
        $startM = false,
        $startD = false,
        $endY = false,
        $endM = false,
        $endD = false,
        $cType = false,
        $flat = false,
        $any = true,
        $split = true
    ) {
        /* check  if empty calendar */
        if (0 >= count($this->components)) {
            return false;
        }
        if (is_array($startY)) {
            return $this->selectComponents2($startY);
        }
        /* check default dates */
        if (is_a($startY, 'DateTime') && is_a($startM, 'DateTime')) {
            $endY   = $startM->format('Y');
            $endM   = $startM->format('m');
            $endD   = $startM->format('d');
            $startD = $startY->format('d');
            $startM = $startY->format('m');
            $startY = $startY->format('Y');
        } else {
            if (!$startY) {
                $startY = date('Y');
            }
            if (!$startM) {
                $startM = date('m');
            }
            if (!$startD) {
                $startD = date('d');
            }
            if (!$endY) {
                $endY = $startY;
            }
            if (!$endM) {
                $endM = $startM;
            }
            if (!$endD) {
                $endD = $startD;
            }
        }
// echo "selectComp args={$startY}-{$startM}-{$startD} - {$endY}-{$endM}-{$endD}<br>\n"; $tcnt = 0;// test ###
        /* check component types */
        if (empty($cType)) {
            $cType = iCalUtilityFunctions::$vComps;
        } else {
            if (!is_array($cType)) {
                $cType = [$cType];
            }
            $cType = array_map('strtolower', $cType);
            foreach ($cType as $cix => $theType) {
                if (!in_array($theType, iCalUtilityFunctions::$vComps)) {
                    $cType[$cix] = 'vevent';
                }
            }
            $cType = array_unique($cType);
        }
        if ((false === $flat) && (false === $any)) // invalid combination
        {
            $split = false;
        }
        if ((true === $flat) && (true === $split)) // invalid combination
        {
            $split = false;
        }
        /* iterate components */
        $result = [];
        $this->sort('UID');
        $compUIDcmp  = null;
        $exdatelist  = $recurridList = [];
        $intervalP1D = new \DateInterval('P1D');
        foreach ($this->components as $cix => $component) {
            if (empty($component)) {
                continue;
            }
            /* deselect unvalid type components */
            if (!in_array($component->objName, $cType)) {
                continue;
            }
            unset($compStart, $compEnd);
            /* select start from dtstart or due if dtstart is missing */
            $prop = $component->getProperty('dtstart', false, true);
            if (empty($prop) && ($component->objName == 'vtodo') && (false === ($prop = $component->getProperty('due',
                        false, true)))) {
                continue;
            }
            if (empty($prop)) {
                continue;
            }
            /* get UID */
            $compUID = $component->getProperty('UID');
            if ($compUIDcmp != $compUID) {
                $compUIDcmp = $compUID;
                $exdatelist = $recurridList = [];
            }
            $recurrid = false;
// file_put_contents( '/opt/work/iCal/iCalcreator/iCalcreator-2.20.x/log/log.txt', "#$cix".PHP_EOL.var_export( $component, TRUE ).PHP_EOL.PHP_EOL, FILE_APPEND ); // test ###
            $compStart = iCaldateTime::factory($prop['value'], $prop['params'], $prop['value']);
            $dtstartTz = $compStart->getTimezoneName();
            if (isset($prop['params']['VALUE']) && ('DATE' == $prop['params']['VALUE'])) {
                $compStartHis = '';
            } else {
                $his          = $compStart->getTime();
                $compStartHis = sprintf(iCalUtilityFunctions::$fmt['His'], (int)$his[0], (int)$his[1], (int)$his[2]);
            }
            /* get end date from dtend/due/duration properties */
            if (false !== ($prop = $component->getProperty('dtend', false, true))) {
                $compEnd                        = iCaldateTime::factory($prop['value'], $prop['params'], $prop['value'],
                    $dtstartTz);
                $compEnd->SCbools['dtendExist'] = true;
            }
            if (empty($prop) && ($component->objName == 'vtodo') && (false !== ($prop = $component->getProperty('due',
                        false, true)))) {
                $compEnd                      = iCaldateTime::factory($prop['value'], $prop['params'], $prop['value'],
                    $dtstartTz);
                $compEnd->SCbools['dueExist'] = true;
            }
            if (empty($prop) && (false !== ($prop = $component->getProperty('duration', false, true,
                        true)))) { // in dtend (array) format
                $compEnd                           = iCaldateTime::factory($prop['value'], $prop['params'],
                    $prop['value'], $dtstartTz);
                $compEnd->SCbools['durationExist'] = true;
            }
            if (!empty($prop) && !isset($prop['value']['hour'])) {
                /* a DTEND without time part denotes an end of an event that actually ends the day before,
             for an all-day event DTSTART=20071201 DTEND=20071202, taking place 20071201!!! */
                $compEnd->SCbools['endAllDayEvent'] = true;
                $compEnd->modify('-1 day');
                $compEnd->setTime(23, 59, 59);
            }
            unset($prop);
            if (empty($compEnd)) {
                $compDuration = false;
                $compEnd      = clone $compStart;
                $compEnd->setTime(23, 59, 59);     //  23:59:59 the same day as start
            } else {
                if ($compEnd->format('Ymd') < $compStart->format('Ymd')) { // MUST be after start date!!
                    $compEnd = clone $compStart;
                    $compEnd->setTime(23, 59, 59);   //  23:59:59 the same day as start or ???
                }
                $compDuration = $compStart->diff($compEnd); // DateInterval
            }
            /* check recurrence-id (note, a missing sequence is the same as sequence=0 so don't test for sequence), to alter when hit dtstart/recurlist */
            if (false !== ($prop = $component->getProperty('recurrence-id', false, true))) {
                $recurrid                     = iCaldateTime::factory($prop['value'], $prop['params'], $prop['value'],
                    $dtstartTz);
                $rangeSet                     = (isset($prop['params']['RANGE']) && ('THISANDFUTURE' == $prop['params']['RANGE'])) ? true : false;
                $recurridList[$recurrid->key] = [
                    clone $compStart,
                    clone $compEnd,
                    $compDuration,
                    $rangeSet
                ]; // change recur this day to new YmdHis/duration/range
// echo "adding comp no:$cix with date=".$compStart->format(iCalUtilityFunctions::$fmt['YmdHis2e'])." to recurridList id={$recurrid->key}, newDate={$compStart->key}<br>\n"; // test ###
                unset($prop);
                continue;                         // ignore any other props in the component
            } // end recurrence-id/sequence test
// else echo "comp no:$cix with date=".$compStart->format().", NO recurrence-id<br>\n"; // test ###
            ksort($recurridList, SORT_STRING);
// echo 'recurridList='.implode(', ', array_keys( $recurridList ))."<br>\n"; // test ###
            $fcnStart = clone $compStart;
            $fcnStart->setDate((int)$startY, (int)$startM, (int)$startD);
            $fcnStart->setTime(0, 0, 0);
            $fcnEnd = clone $compEnd;
            $fcnEnd->setDate((int)$endY, (int)$endM, (int)$endD);
            $fcnEnd->setTime(23, 59, 59);
// echo 'compStart='.$compStart->format().', compEnd'.$compEnd->format(); if($compDuration)echo ', interval='.$compDuration->format( iCalUtilityFunctions::$fmt['durDHis'] ); echo "<br>\n"; $tcnt = 0;// test ###
            /* *************************************************************
               make a list of optional exclude dates for component occurence from exrule and exdate
               *********************************************************** */
            $workStart = clone $compStart;
            $workStart->sub($compDuration ? $compDuration : $intervalP1D);
            $workEnd = clone $fcnEnd;
            $workEnd->add($compDuration ? $compDuration : $intervalP1D);
            while (false !== ($prop = $component->getProperty('EXRULE'))) {
                $exdatelist2 = [];
                if (isset($prop['UNTIL']['hour'])) {                 // convert until date to dtstart timezone
                    $until = iCaldateTime::factory($prop['UNTIL'], ['TZID' => 'UTC'], null, $dtstartTz);
                    $until = $until->format();
                    iCalUtilityFunctions::_strDate2arr($until);
                    $prop['UNTIL'] = $until;
                }
                iCalUtilityFunctions::_recur2date($exdatelist2, $prop, $compStart, $workStart, $workEnd);
                foreach ($exdatelist2 as $k => $v) {
                    $exdatelist[$k . $compStartHis] = $v;
                }                  // point out exact every excluded ocurrence (incl. opt. His)
                unset($until, $exdatelist2);
            }
            while (false !== ($prop = $component->getProperty('EXDATE', false, true))) { // - start check exdate
                foreach ($prop['value'] as $exdate) {
                    $exdate                   = iCaldateTime::factory($exdate, $prop['params'], $exdate, $dtstartTz);
                    $exdatelist[$exdate->key] = true;
                } // end - foreach( $exdate as $exdate )
            }  // end - check exdate
            unset($prop, $exdate);
// echo 'exdatelist='  .implode(', ', array_keys( $exdatelist ))  ."<br>\n"; // test ###
            /* *************************************************************
               select only components within.. .
               *********************************************************** */
            $xRecurrence = 1;
            if ((!$any && iCalUtilityFunctions::_inScope($compStart, $fcnStart, $compStart, $fcnEnd,
                        $compStart->dateFormat)) ||
                ($any && iCalUtilityFunctions::_inScope($fcnEnd, $compStart, $fcnStart, $compEnd,
                        $compStart->dateFormat))
            ) {
                /* add the selected component (WITHIN valid dates) to output array */
                if ($flat) { // any=true/false, ignores split
                    if (!$recurrid) {
                        $result[$compUID] = $component->copy();
                    } // copy original to output (but not anyone with recurrence-id)
                } elseif ($split) { // split the original component
// echo 'split org.:'.$compStart->format().' < '.$fcnStart->format( 'Ymd His e' )."<br>\n"; // test ###
                    if ($compStart->format(iCalUtilityFunctions::$fmt['YmdHis2']) < $fcnStart->format(iCalUtilityFunctions::$fmt['YmdHis2'])) {
                        $rstart = clone $fcnStart;
                    } else {
                        $rstart = clone $compStart;
                    }
                    if ($compEnd->format(iCalUtilityFunctions::$fmt['YmdHis2']) > $fcnEnd->format(iCalUtilityFunctions::$fmt['YmdHis2'])) {
                        $rend = clone $fcnEnd;
                    } else {
                        $rend = clone $compEnd;
                    }
// echo "going to test comp no:$cix, rstart=".$rstart->format( iCalUtilityFunctions::$fmt['YmdHis2e'] )." (key={$rstart->key}), end=".$rend->format( iCalUtilityFunctions::$fmt['YmdHis2e'] )."<br>\n"; // test ###
                    if (!isset($exdatelist[$rstart->key])) {     // not excluded in exrule/exdate
                        if (isset($recurridList[$rstart->key])) {   // change start day to new YmdHis/duration
                            $k = $rstart->key;
// echo "recurridList HIT, key={$k}, recur Date=".$recurridList[$k][0]->key."<br>\n"; // test ###
                            $rstart   = clone $recurridList[$k][0];
                            $startHis = $rstart->getTime();
                            $rend     = clone $rstart;
                            if (false !== $recurridList[$k][2]) {
                                $rend->add($recurridList[$k][2]);
                            } elseif (false !== $compDuration) {
                                $rend->add($compDuration);
                            }
                            $endHis = $rend->getTime();
                            unset($recurridList[$k]);
                        } else {
                            $startHis = $compStart->getTime();
                            $endHis   = $compEnd->getTime();
                        }
// echo "_____testing comp no:$cix, rstart=".$rstart->format( iCalUtilityFunctions::$fmt['YmdHis2e'] )." (key={$rstart->key}), end=".$rend->format( iCalUtilityFunctions::$fmt['YmdHis2e'] )."<br>\n"; // test ###
                        $cnt           = 0;                                       // exclude any recurrence START date, found in exdatelist or recurridList but accept the reccurence-id comp itself
                        $occurenceDays = 1 + (int)$rstart->diff($rend)->format('%a');  // count the days (incl start day)
                        while ($rstart->format(iCalUtilityFunctions::$fmt['Ymd2']) <= $rend->format(iCalUtilityFunctions::$fmt['Ymd2'])) {
                            $cnt += 1;
                            if (1 < $occurenceDays) {
                                $component->setProperty('X-OCCURENCE',
                                    sprintf(iCalUtilityFunctions::$fmt['dayOfDays'], $cnt, $occurenceDays));
                            }
                            if (1 < $cnt) {
                                $rstart->setTime(0, 0, 0);
                            } else {
                                $rstart->setTime($startHis[0], $startHis[1], $startHis[2]);
                                $exdatelist[$rstart->key] = $compDuration; // make sure to exclude start day from the recurrence pattern
                            }
                            $component->setProperty('X-CURRENT-DTSTART', $rstart->format($compStart->dateFormat));
                            if (false !== $compDuration) {
                                $propName = (isset($compEnd->SCbools['dueExist'])) ? 'X-CURRENT-DUE' : 'X-CURRENT-DTEND';
                                if ($cnt < $occurenceDays) {
                                    $rstart->setTime(23, 59, 59);
                                } else {
                                    $rstart->setTime($endHis[0], $endHis[1], $endHis[2]);
                                }
                                $component->setProperty($propName, $rstart->format($compEnd->dateFormat));
                            }
                            $result[(int)$rstart->format('Y')][(int)$rstart->format('m')][(int)$rstart->format('d')][$compUID] = $component->copy(); // copy to output
                            $rstart->add($intervalP1D);
                        } // end while(( $rstart->format( 'Ymd' ) < $rend->format( 'Ymd' ))
                        unset($cnt, $occurenceDays);
                    } // end if( ! isset( $exdatelist[$rstart->key] ))
// else echo "skip no:$cix with date=".$compStart->format()."<br>\n"; // test ###
                    unset($rstart, $rend);
                } // end elseif( $split )   -  else use component date
                else { // !$flat && !$split, i.e. no flat array and DTSTART within period
                    $tstart = (isset($recurridList[$compStart->key])) ? clone $recurridList[$k][0] : clone $compStart;
// echo "going to test comp no:$cix with checkDate={$compStart->key} with recurridList=".implode(',',array_keys($recurridList)); // test ###
                    if (!$any || !isset($exdatelist[$tstart->key])) {  // exclude any recurrence date, found in exdatelist
// echo " and copied to output<br>\n"; // test ###
                        $result[(int)$tstart->format('Y')][(int)$tstart->format('m')][(int)$tstart->format('d')][$compUID] = $component->copy(); // copy to output
                    }
                    unset($tstart);
                }
            } // end (dt)start within the period or occurs within the period
            /* *************************************************************
               if 'any' components, check components with reccurrence rules, removing all excluding dates
               *********************************************************** */
            if (true === $any) {
                $recurlist = [];
                /* make a list of optional repeating dates for component occurence, rrule, rdate */
                while (false !== ($prop = $component->getProperty('RRULE'))) {  // get all rrule dates (multiple values allowed)
                    $recurlist2 = [];
                    if (isset($prop['UNTIL']['hour'])) {                           // convert $rrule['UNTIL'] to the same timezone as DTSTART !!
                        $until = iCaldateTime::factory($prop['UNTIL'], ['TZID' => 'UTC'], null, $dtstartTz);
                        $until = $until->format();
                        iCalUtilityFunctions::_strDate2arr($until);
                        $prop['UNTIL'] = $until;
                    }
                    iCalUtilityFunctions::_recur2date($recurlist2, $prop, $compStart, $workStart, $workEnd);
                    foreach ($recurlist2 as $recurkey => $recurvalue) {             // recurkey=Ymd
                        $recurkey .= $compStartHis;                                    // add opt His
                        if (!isset($exdatelist[$recurkey])) {
                            $recurlist[$recurkey] = $compDuration;
                        }                       // DateInterval or FALSE
                    }
                    unset($prop, $until, $recurlist2);
                }
                $workStart = clone $fcnStart;
                $workStart->sub($compDuration ? $compDuration : $intervalP1D);
                $format = $compStart->dateFormat;
                while (false !== ($prop = $component->getProperty('RDATE', false, true))) {
                    $rdateFmt = (isset($prop['params']['VALUE'])) ? $prop['params']['VALUE'] : 'DATETIME';
                    $params   = $prop['params'];
                    $prop     = $prop['value'];
                    foreach ($prop as $theRdate) {
                        if ('PERIOD' == $rdateFmt) {                  // all days within PERIOD
                            $rdate = iCaldateTime::factory($theRdate[0], $params, $theRdate[0], $dtstartTz);
                            if (!iCalUtilityFunctions::_inScope($rdate, $workStart, $rdate, $fcnEnd,
                                    $format) || isset($exdatelist[$rdate->key])) {
                                continue;
                            }
                            if (isset($theRdate[1]['year']))           // date-date period end
                            {
                                $recurlist[$rdate->key] = $rdate->diff(iCaldateTime::factory($theRdate[1], $params,
                                    $theRdate[1], $dtstartTz));
                            } else                                         // period duration
                            {
                                $recurlist[$rdate->key] = new \DateInterval(iCalUtilityFunctions::_duration2str($theRdate[1]));
                            }
                        } // end if( 'PERIOD' == $rdateFmt )
                        elseif ('DATE' == $rdateFmt) { // single recurrence, date
                            $rdate = iCaldateTime::factory($theRdate, array_merge($params, ['TZID' => $dtstartTz]),
                                null, $dtstartTz);
                            if (iCalUtilityFunctions::_inScope($rdate, $workStart, $rdate, $fcnEnd,
                                    $format) && !isset($exdatelist[$rdate->key])) {
                                $recurlist[$rdate->key . $compStartHis] = $compDuration;
                            } // set start date for recurrence + DateInterval/FALSE (+opt His)
                        } // end DATE
                        else { // start DATETIME
                            $rdate = iCaldateTime::factory($theRdate, $params, $theRdate, $dtstartTz);
                            if (iCalUtilityFunctions::_inScope($rdate, $workStart, $rdate, $fcnEnd,
                                    $format) && !isset($exdatelist[$rdate->key])) {
                                $recurlist[$rdate->key] = $compDuration;
                            } // set start datetime for recurrence DateInterval/FALSE
                        } // end DATETIME
                    } // end foreach( $prop as $theRdate )
                }  // end while( FALSE !== ( $prop = $component->getProperty( 'rdate', FALSE, TRUE )))
                unset($prop, $workStart, $format, $theRdate, $rdate, $rend);
                foreach ($recurridList as $rKey => $rVal) { // check for recurrence-id, i.e. alter recur Ymd[His] and duration
                    if (isset($recurlist[$rKey])) {
                        unset($recurlist[$rKey]);
                        $recurlist[$rVal[0]->key] = (false !== $rVal[2]) ? $rVal[2] : $compDuration;
// echo "alter recurfrom {$rKey} to {$rVal[0]->key} ";if(FALSE!==$dur)echo " ({$dur->format( '%a days, %h-%i-%s' )})";echo "<br>\n"; // test ###
                    }
                }
                ksort($recurlist, SORT_STRING);
// echo 'recurlist='   .implode(', ', array_keys( $recurlist ))   ."<br>\n"; // test ###
// echo 'recurridList='   .implode(', ', array_keys( $recurridList ))   ."<br>\n"; // test ###
                /* *************************************************************
               output all remaining components in recurlist
               *********************************************************** */
                if (0 < count($recurlist)) {
                    $component2 = $component->copy();
                    $compUID    = $component2->getProperty('UID');
                    $workStart  = clone $fcnStart;
                    $workStart->sub($compDuration ? $compDuration : $intervalP1D);
                    $YmdOld = null;
                    foreach ($recurlist as $recurkey => $durvalue) {
                        if ($YmdOld == substr($recurkey, 0,
                                8))                         // skip overlapping recur the same day, i.e. RDATE before RRULE
                        {
                            continue;
                        }
                        $YmdOld = substr($recurkey, 0, 8);
                        $rstart = clone $compStart;
                        $rstart->setDate((int)substr($recurkey, 0, 4), (int)substr($recurkey, 4, 2),
                            (int)substr($recurkey, 6, 2));
                        $rstart->setTime((int)substr($recurkey, 8, 2), (int)substr($recurkey, 10, 2),
                            (int)substr($recurkey, 12, 2));
// echo "recur start=".$rstart->format( iCalUtilityFunctions::$fmt['YmdHis2e'] )."<br>\n"; // test ###;
                        /* add recurring components within valid dates to output array, only start date set */
                        if ($flat) {
                            if (!isset($result[$compUID])) // only one comp
                            {
                                $result[$compUID] = $component2->copy();
                            } // copy to output
                        } /* add recurring components within valid dates to output array, split for each day */
                        elseif ($split) {
                            $rend = clone $rstart;
                            if (false !== $durvalue) {
                                $rend->add($durvalue);
                            }
                            if ($rend->format(iCalUtilityFunctions::$fmt['Ymd2']) > $fcnEnd->format(iCalUtilityFunctions::$fmt['Ymd2'])) {
                                $rend = clone $fcnEnd;
                            }
// echo "recur 1={$recurkey}, start=".$rstart->format( iCalUtilityFunctions::$fmt['YmdHis2e'] ).", end=".$rend->format( iCalUtilityFunctions::$fmt['YmdHis2e'] );if($durvalue) echo ", duration=".$durvalue->format( iCalUtilityFunctions::$fmt['durDHis'] );echo "<br>\n"; // test ###
                            $xRecurrence   += 1;
                            $cnt           = 0;
                            $occurenceDays = 1 + (int)$rstart->diff($rend)->format('%a');  // count the days (incl start day)
                            while ($rstart->format(iCalUtilityFunctions::$fmt['Ymd2']) <= $rend->format(iCalUtilityFunctions::$fmt['Ymd2'])) {    // iterate.. .
                                $cnt += 1;
                                if ($rstart->format(iCalUtilityFunctions::$fmt['Ymd2']) < $fcnStart->format(iCalUtilityFunctions::$fmt['Ymd2'])) { // date before dtstart
// echo "recur 3, start=".$rstart->format( 'Y-m-d H:i:s' )." &gt;= fcnStart=".$fcnStart->format( 'Y-m-d H:i:s' )."<br>\n"; // test ###
                                    $rstart->add($intervalP1D);
                                    $rstart->setTime(0, 0, 0);
                                    continue;
                                } elseif (2 == $cnt) {
                                    $rstart->setTime(0, 0, 0);
                                }
                                $component2->setProperty('X-RECURRENCE', $xRecurrence);
                                if (1 < $occurenceDays) {
                                    $component2->setProperty('X-OCCURENCE',
                                        sprintf(iCalUtilityFunctions::$fmt['dayOfDays'], $cnt, $occurenceDays));
                                } else {
                                    $component2->deleteProperty('X-OCCURENCE');
                                }
                                $component2->setProperty('X-CURRENT-DTSTART', $rstart->format($compStart->dateFormat));
                                $propName = (isset($compEnd->SCbools['dueExist'])) ? 'X-CURRENT-DUE' : 'X-CURRENT-DTEND';
                                if (false !== $durvalue) {
                                    if ($cnt < $occurenceDays) {
                                        $rstart->setTime(23, 59, 59);
                                    } else {
                                        $His = $rend->getTime();                             // set end time
                                        $rstart->setTime($His[0], $His[1], $His[2]);
                                    }
                                    $component2->setProperty($propName, $rstart->format($compEnd->dateFormat));
// echo "checking date, (day {$cnt} of {$occurenceDays}), _end_=".$rstart->format( 'Y-m-d H:i:s e' )."<br>"; // test ###;
                                } else {
                                    $component2->deleteProperty($propName);
                                }
                                $result[(int)$rstart->format('Y')][(int)$rstart->format('m')][(int)$rstart->format('d')][$compUID] = $component2->copy(); // copy to output
                                $rstart->add($intervalP1D);
                            } // end while( $rstart->format( 'Ymd' ) <= $rend->format( 'Ymd' ))
                            unset($rstart, $rend);
                        } // end elseif( $split )
                        elseif ($rstart->format(iCalUtilityFunctions::$fmt['Ymd2']) >= $fcnStart->format(iCalUtilityFunctions::$fmt['Ymd2'])) {
                            $xRecurrence += 1;                                            // date within period  //* flat=FALSE && split=FALSE => one comp every recur startdate *//
                            $component2->setProperty('X-RECURRENCE', $xRecurrence);
                            $component2->setProperty('X-CURRENT-DTSTART', $rstart->format($compStart->dateFormat));
                            $propName = (isset($compEnd->SCbools['dueExist'])) ? 'X-CURRENT-DUE' : 'X-CURRENT-DTEND';
                            if (false !== $durvalue) {
                                $rstart->add($durvalue);
                                $component2->setProperty($propName, $rstart->format($compEnd->dateFormat));
                            } else {
                                $component2->deleteProperty($propName);
                            }
                            $result[(int)$rstart->format('Y')][(int)$rstart->format('m')][(int)$rstart->format('d')][$compUID] = $component2->copy(); // copy to output
                        } // end elseif( $rstart >= $fcnStart )
                        unset($rstart);
                    } // end foreach( $recurlist as $recurkey => $durvalue )
                    unset($component2, $xRecurrence, $compUID, $workStart, $rstart);
                } // end if( 0 < count( $recurlist ))
            } // end if( TRUE === $any )
            unset($component);
        } // end foreach ( $this->components as $cix => $component )
        unset($recurrid, $recurridList, $fcnStart, $fcnEnd, $compStart, $compEnd, $exdatelist, $recurlist); // clean up
        if (0 >= count($result)) {
            return false;
        } elseif (!$flat) {
            foreach ($result as $y => $yeararr) {
                foreach ($yeararr as $m => $montharr) {
                    foreach ($montharr as $d => $dayarr) {
                        if (empty($result[$y][$m][$d])) {
                            unset($result[$y][$m][$d]);
                        } else {
                            $result[$y][$m][$d] = array_values($dayarr); // skip tricky UID-index
                            if (1 < count($result[$y][$m][$d])) {
                                foreach ($result[$y][$m][$d] as & $c)       // sort
                                {
                                    iCalUtilityFunctions::_setSortArgs($c);
                                }
                                usort($result[$y][$m][$d], ['iCalUtilityFunctions', '_cmpfcn']);
                            }
                        }
                    } // end foreach( $montharr as $d => $dayarr )
                    if (empty($result[$y][$m])) {
                        unset($result[$y][$m]);
                    } else {
                        ksort($result[$y][$m]);
                    }
                } // end foreach( $yeararr as $m => $montharr )
                if (empty($result[$y])) {
                    unset($result[$y]);
                } else {
                    ksort($result[$y]);
                }
            }// end foreach( $result as $y => $yeararr )
            if (empty($result)) {
                unset($result);
            } else {
                ksort($result);
            }
        } // end elseif( !$flat )
        if (0 >= count($result)) {
            return false;
        }

        return $result;
    }

    /**
     * select components from calendar on based on specific property value(-s)
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param array $selectOptions (string) key => (mixed) value, (key=propertyName)
     *
     * @uses   vcalendar::$components
     * @uses   calendarComponent::$objName
     * @uses   iCalUtilityFunctions::$vComps
     * @uses   calendarComponent::getProperty()
     * @uses   iCalUtilityFunctions::$otherProps
     * @uses   calendarComponent::copy()
     * @uses   iCalUtilityFunctions::$mProps1
     * @uses   calendarComponent::_getProperties()
     * @return array
     */
    function selectComponents2($selectOptions)
    {
//     $output = [];
        $selectOptions = array_change_key_case($selectOptions, CASE_UPPER);
        foreach ($this->components as $cix => $component3) {
            if (!in_array($component3->objName, iCalUtilityFunctions::$vComps)) {
                continue;
            }
            $uid = $component3->getProperty('UID');
            foreach ($selectOptions as $propName => $pvalue) {
                if (!in_array($propName, iCalUtilityFunctions::$otherProps)) {
                    continue;
                }
                if (!is_array($pvalue)) {
                    $pvalue = [$pvalue];
                }
                if (('UID' == $propName) && in_array($uid, $pvalue)) {
                    $output[$uid][] = $component3->copy();
                    continue;
                } elseif (in_array($propName, iCalUtilityFunctions::$mProps1)) {
                    $propValues = [];
                    $component3->_getProperties($propName, $propValues);
                    $propValues = array_keys($propValues);
                    foreach ($pvalue as $theValue) {
                        if (in_array($theValue, $propValues)) { //  && !isset( $output[$uid] )) {
                            $output[$uid][] = $component3->copy();
                            break;
                        }
                    }
                    continue;
                } // end   elseif( // multiple occurrence?
                elseif (false === ($d = $component3->getProperty($propName))) // single occurrence
                {
                    continue;
                }
                if (is_array($d)) {
                    foreach ($d as $part) {
                        if (in_array($part, $pvalue) && !isset($output[$uid])) {
                            $output[$uid][] = $component3->copy();
                        }
                    }
                } elseif (('SUMMARY' == $propName) && !isset($output[$uid])) {
                    foreach ($pvalue as $pval) {
                        if (false !== stripos($d, $pval)) {
                            $output[$uid][] = $component3->copy();
                            break;
                        }
                    }
                } elseif (in_array($d, $pvalue) && !isset($output[$uid])) {
                    $output[$uid][] = $component3->copy();
                }
            } // end foreach( $selectOptions as $propName => $pvalue ) {
        } // end foreach( $this->components as $cix => $component3 ) {
        if (!empty($output)) {
            ksort($output); // uid order
            $output2 = [];
            foreach ($output as $uid => $components) {
                foreach ($components as $component) {
                    $output2[] = $component;
                }
            }
            $output = $output2;
        }

        return $output;
    }

    /**
     * replace calendar component in vcalendar
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param object $component calendar component
     *
     * @uses   calendarComponent::$objName
     * @uses   iCalUtilityFunctions::$vComps
     * @uses   vcalendar::setComponent()
     * @uses   calendarComponent::getProperty()
     * @return bool
     */
    function replaceComponent($component)
    {
        if (in_array($component->objName, iCalUtilityFunctions::$vComps)) {
            return $this->setComponent($component, $component->getProperty('UID'));
        }
        if (('vtimezone' != $component->objName) || (false === ($tzid = $component->getProperty('TZID')))) {
            return false;
        }
        foreach ($this->components as $cix => $comp) {
            if ('vtimezone' != $component->objName) {
                continue;
            }
            if ($tzid == $comp->getComponent('TZID')) {
                unset($component->propix, $component->compix);
                $this->components[$cix] = $component;

                return true;
            }
        }

        return false;
    }

    /**
     * add calendar component to calendar
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param object $component calendar component
     * @param mixed  $arg1      optional, ordno/component type/ component uid
     * @param mixed  $arg2      optional, ordno if arg1 = component type
     *
     * @uses   calendarComponent::setConfig()
     * @uses   vcalendar::getConfig()
     * @uses   calendarComponent::$objName
     * @uses   calendarComponent::getProperty()
     * @uses   vcalendar::$components
     * @uses   iCalUtilityFunctions::$mComps
     * @uses   calendarComponent::copy()
     * @return bool
     */
    function setComponent($component, $arg1 = false, $arg2 = false)
    {
        $component->setConfig($this->getConfig(), false, true);
        if (!in_array($component->objName, ['valarm', 'vtimezone'])) {
            /* make sure dtstamp and uid is set */
            $dummy1 = $component->getProperty('dtstamp');
            $dummy2 = $component->getProperty('uid');
        }
        unset($component->propix, $component->compix);
        if (!$arg1) { // plain insert, last in chain
            $this->components[] = $component->copy();

            return true;
        }
        $argType = $index = null;
        if (ctype_digit((string)$arg1)) { // index insert/replace
            $argType = 'INDEX';
            $index   = (int)$arg1 - 1;
        } elseif (in_array(strtolower($arg1), iCalUtilityFunctions::$mComps)) {
            $argType = strtolower($arg1);
            $index   = (ctype_digit((string)$arg2)) ? ((int)$arg2) - 1 : 0;
        }
        // else if arg1 is set, arg1 must be an UID
        $cix1sC = 0;
        foreach ($this->components as $cix => $component2) {
            if (empty($component2)) {
                continue;
            }
            if (('INDEX' == $argType) && ($index == $cix)) { // index insert/replace
                $this->components[$cix] = $component->copy();

                return true;
            } elseif ($argType == $component2->objName) { // component Type index insert/replace
                if ($index == $cix1sC) {
                    $this->components[$cix] = $component->copy();

                    return true;
                }
                $cix1sC++;
            } elseif (!$argType && ($arg1 == $component2->getProperty('uid'))) { // UID insert/replace
                $this->components[$cix] = $component->copy();

                return true;
            }
        }
        /* arg1=index and not found.. . insert at index .. .*/
        if ('INDEX' == $argType) {
            $this->components[$index] = $component->copy();
            ksort($this->components, SORT_NUMERIC);
        } else    /* not found.. . insert last in chain anyway .. .*/ {
            $this->components[] = $component->copy();
        }

        return true;
    }

    /**
     * sort iCal compoments
     *
     * ascending sort on properties (if exist) x-current-dtstart, dtstart,
     * x-current-dtend, dtend, x-current-due, due, duration, created, dtstamp, uid if called without arguments,
     * otherwise sorting on specific (argument) property values
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param string $sortArg
     *
     * @uses   vcalendar::$components
     * @uses   iCalUtilityFunctions::$otherProps
     * @uses   iCalUtilityFunctions::_setSortArgs()
     * @uses   iCalUtilityFunctions::_cmpfcn()
     * @return void
     */
    function sort($sortArg = false)
    {
        if (!is_array($this->components) || (2 > count($this->components))) {
            return;
        }
        if ($sortArg) {
            $sortArg = strtoupper($sortArg);
            if (!in_array($sortArg, iCalUtilityFunctions::$otherProps) && ('DTSTAMP' != $sortArg)) {
                $sortArg = false;
            }
        }
        foreach ($this->components as & $c) {
            iCalUtilityFunctions::_setSortArgs($c, $sortArg);
        }
        usort($this->components, ['iCalUtilityFunctions', '_cmpfcn']);
    }

    /**
     * parse iCal text/file into vcalendar, components, properties and parameters
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param mixed    $unparsedtext strict rfc2445 formatted, single property string or array of property strings
     * @param resource $context      PHP resource context
     *
     * @uses   iCalUtilityFunctions::convEolChar()
     * @uses   vcalendar::getConfig()
     * @uses   vcalendar::$components
     * @uses   calendarComponent::copy()
     * @uses   vevent::construct()
     * @uses   vfreebusy::construct()
     * @uses   vjournal::construct()
     * @uses   vtodo::construct()
     * @uses   vtimezone::construct()
     * @uses   vcalendar::$unparsed
     * @uses   iCalUtilityFunctions::_splitContent()
     * @uses   iCalUtilityFunctions::_strunrep()
     * @uses   vcalendar::setProperty()
     * @uses   calendarComponent::$unparsed
     * @uses   calendarComponent::parse()
     * @return bool FALSE if error occurs during parsing
     */
    function parse($unparsedtext = false, $context = null)
    {
        $nl = $this->getConfig('nl');
        if ((false === $unparsedtext) || empty($unparsedtext)) {
            /* directory+filename is set previously via setConfig url or directory+filename  */
            if (false === ($file = $this->getConfig('url'))) {
                if (false === ($file = $this->getConfig('dirfile'))) {
                    return false;
                }                 /* err 1 */
                if (!is_file($file)) {
                    return false;
                }                 /* err 2 */
                if (!is_readable($file)) {
                    return false;
                }                 /* err 3 */
            }
            /* READ FILE */
            if (!empty($context) && filter_var($file, FILTER_VALIDATE_URL) &&
                (false === ($rows = file_get_contents($file, false, $context)))
            ) {
                return false;
            }                 /* err 6 */
            elseif (false === ($rows = file_get_contents($file))) {
                return false;
            }                 /* err 5 */
        } // end if(( FALSE === $unparsedtext ) || empty( $unparsedtext ))
        elseif (is_array($unparsedtext)) {
            $rows = implode('\n' . $nl, $unparsedtext);
        } else {
            $rows = &$unparsedtext;
        }
        /* fix line folding */
        $rows = iCalUtilityFunctions::convEolChar($rows, $nl);
        /* skip leading (empty/invalid) lines (and remove leading BOM chars etc) */
        foreach ($rows as $lix => $line) {
            if (false !== stripos($line, 'BEGIN:VCALENDAR')) {
                $rows[$lix] = 'BEGIN:VCALENDAR';
                break;
            }
            unset($rows[$lix]);
        }
        $rcnt = count($rows);
        if (3 > $rcnt)                  /* err 10 */ {
            return false;
        }
        /* skip trailing empty lines and ensure an end row */
        $lix = array_keys($rows);
        $lix = end($lix);
        while (3 < $lix) {
            $tst = trim($rows[$lix]);
            if (('\n' == $tst) || empty($tst)) {
                unset($rows[$lix]);
                $lix--;
                continue;
            }
            if (false === stripos($rows[$lix], 'END:VCALENDAR')) {
                $rows[] = 'END:VCALENDAR';
            } else {
                $rows[$lix] = 'END:VCALENDAR';
            }
            break;
        }
        $comp    = &$this;
        $calsync = $compsync = 0;
        /* identify components and update unparsed data within component */
        $config = $this->getConfig();
        $endtxt = ['END:VE', 'END:VF', 'END:VJ', 'END:VT'];
        foreach ($rows as $lix => $line) {
            if ('BEGIN:VCALENDAR' == strtoupper(substr($line, 0, 15))) {
                $calsync++;
                continue;
            } elseif ('END:VCALENDAR' == strtoupper(substr($line, 0, 13))) {
                if (0 < $compsync) {
                    $this->components[] = $comp->copy();
                }
                $compsync--;
                $calsync--;
                break;
            } elseif (1 != $calsync) {
                return false;
            }                 /* err 20 */
            elseif (in_array(strtoupper(substr($line, 0, 6)), $endtxt)) {
                $this->components[] = $comp->copy();
                $compsync--;
                continue;
            }
            if ('BEGIN:VEVENT' == strtoupper(substr($line, 0, 12))) {
                $comp = new \vevent($config);
                $compsync++;
            } elseif ('BEGIN:VFREEBUSY' == strtoupper(substr($line, 0, 15))) {
                $comp = new \vfreebusy($config);
                $compsync++;
            } elseif ('BEGIN:VJOURNAL' == strtoupper(substr($line, 0, 14))) {
                $comp = new \vjournal($config);
                $compsync++;
            } elseif ('BEGIN:VTODO' == strtoupper(substr($line, 0, 11))) {
                $comp = new \vtodo($config);
                $compsync++;
            } elseif ('BEGIN:VTIMEZONE' == strtoupper(substr($line, 0, 15))) {
                $comp = new \vtimezone($config);
                $compsync++;
            } else { /* update component with unparsed data */
                $comp->unparsed[] = $line;
            }
        } // end foreach( $rows as $line )
        unset($config, $endtxt);
        /* parse data for calendar (this) object */
        if (isset($this->unparsed) && is_array($this->unparsed) && (0 < count($this->unparsed))) {
            /* concatenate property values spread over several lines */
            $propnames = ['calscale', 'method', 'prodid', 'version', 'x-'];
            $proprows  = [];
            for ($i = 0; $i < count($this->unparsed); $i++) { // concatenate lines
                $line = rtrim($this->unparsed[$i], $nl);
                while (isset($this->unparsed[$i + 1]) && !empty($this->unparsed[$i + 1]) && (' ' == $this->unparsed[$i + 1]{0})) {
                    $line .= rtrim(substr($this->unparsed[++$i], 1), $nl);
                }
                $proprows[] = $line;
            }
            foreach ($proprows as $line) {
                if ('\n' == substr($line, -2)) {
                    $line = substr($line, 0, -2);
                }
                /* get property name */
                $propname = '';
                $cix      = 0;
                while (false !== ($char = substr($line, $cix, 1))) {
                    if (in_array($char, [':', ';'])) {
                        break;
                    } else {
                        $propname .= $char;
                    }
                    $cix++;
                }
                /* skip non standard property names */
                if (('x-' != strtolower(substr($propname, 0, 2))) && !in_array(strtolower($propname), $propnames)) {
                    continue;
                }
                /* ignore version/prodid properties */
                if (in_array(strtolower($propname), ['version', 'prodid'])) {
                    continue;
                }
                /* rest of the line is opt.params and value */
                $line = substr($line, $cix);
                /* separate attributes from value */
                iCalUtilityFunctions::_splitContent($line, $propAttr);
                /* update Property */
                if (false !== strpos($line, ',')) {
                    $content = [0 => ''];
                    $cix     = $lix = 0;
                    while (false !== substr($line, $lix, 1)) {
                        if ((0 < $lix) && (',' == $line[$lix]) && ("\\" != $line[($lix - 1)])) {
                            $cix++;
                            $content[$cix] = '';
                        } else {
                            $content[$cix] .= $line[$lix];
                        }
                        $lix++;
                    }
                    if (1 < count($content)) {
                        foreach ($content as $cix => $contentPart) {
                            $content[$cix] = iCalUtilityFunctions::_strunrep($contentPart);
                        }
                        $this->setProperty($propname, $content, $propAttr);
                        continue;
                    } else {
                        $line = reset($content);
                    }
                    $line = iCalUtilityFunctions::_strunrep($line);
                }
                $this->setProperty($propname, rtrim($line, "\x00..\x1F"), $propAttr);
            } // end - foreach( $this->unparsed.. .
        } // end - if( is_array( $this->unparsed.. .
        unset($unparsedtext, $rows, $this->unparsed, $proprows);
        /* parse Components */
        if (is_array($this->components) && (0 < count($this->components))) {
            $ckeys = array_keys($this->components);
            foreach ($ckeys as $ckey) {
                if (!empty($this->components[$ckey]) && !empty($this->components[$ckey]->unparsed)) {
                    $this->components[$ckey]->parse();
                }
            }
        } else {
            return false;
        }                   /* err 91 or something.. . */

        return true;
    }
    /*********************************************************************************/
    /**
     * creates formatted output for calendar object instance
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @uses   vcalendar::$format
     * @uses   vcalendar::$nl
     * @uses   vcalendar::createVersion()
     * @uses   vcalendar::createProdid()
     * @uses   vcalendar::createCalscale()
     * @uses   vcalendar::createMethod()
     * @uses   vcalendar::createXprop()
     * @uses   vcalendar::$components
     * @uses   calendarComponent::setConfig()
     * @uses   vcalendar::getConfig()
     * @uses   calendarComponent::createComponent()
     * @uses   vcalendar::$xcaldecl
     * @return string
     */
    function createCalendar()
    {
        parent::_createFormat();
        $calendarInit = $calendarxCaldecl = $calendarStart = $calendar = '';
        switch ($this->format) {
            case 'xcal':
                $calendarInit  = '<?xml version="1.0" encoding="UTF-8"?>' . $this->nl .
                    '<!DOCTYPE vcalendar PUBLIC "-//IETF//DTD XCAL/iCalendar XML//EN"' . $this->nl .
                    '"http://www.ietf.org/internet-drafts/draft-ietf-calsch-many-xcal-01.txt"';
                $calendarStart = '>' . $this->nl . '<vcalendar';
                break;
            default:
                $calendarStart = 'BEGIN:VCALENDAR' . $this->nl;
                break;
        }
        $calendarStart .= $this->createVersion();
        $calendarStart .= $this->createProdid();
        $calendarStart .= $this->createCalscale();
        $calendarStart .= $this->createMethod();
        if ('xcal' == $this->format) {
            $calendarStart .= '>' . $this->nl;
        }
        $calendar .= $this->createXprop();
        foreach ($this->components as $component) {
            if (empty($component)) {
                continue;
            }
            $component->setConfig($this->getConfig(), false, true);
            $calendar .= $component->createComponent($this->xcaldecl);
        }
        if (('xcal' == $this->format) && (0 < count($this->xcaldecl))) { // xCal only
            $calendarInit .= ' [';
            $old_xcaldecl = [];
            foreach ($this->xcaldecl as $declix => $declPart) {
                if ((0 < count($old_xcaldecl)) &&
                    isset($declPart['uri']) && isset($declPart['external']) &&
                    isset($old_xcaldecl['uri']) && isset($old_xcaldecl['external']) &&
                    (in_array($declPart['uri'], $old_xcaldecl['uri'])) &&
                    (in_array($declPart['external'], $old_xcaldecl['external']))
                ) {
                    continue;
                } // no duplicate uri and ext. references
                if ((0 < count($old_xcaldecl)) &&
                    !isset($declPart['uri']) && !isset($declPart['uri']) &&
                    isset($declPart['ref']) && isset($old_xcaldecl['ref']) &&
                    (in_array($declPart['ref'], $old_xcaldecl['ref']))
                ) {
                    continue;
                } // no duplicate element declarations
                $calendarxCaldecl .= $this->nl . '<!';
                foreach ($declPart as $declKey => $declValue) {
                    switch ($declKey) {                    // index
                        case 'xmldecl':                       // no 1
                            $calendarxCaldecl .= $declValue . ' ';
                            break;
                        case 'uri':                           // no 2
                            $calendarxCaldecl      .= $declValue . ' ';
                            $old_xcaldecl['uri'][] = $declValue;
                            break;
                        case 'ref':                           // no 3
                            $calendarxCaldecl      .= $declValue . ' ';
                            $old_xcaldecl['ref'][] = $declValue;
                            break;
                        case 'external':                      // no 4
                            $calendarxCaldecl           .= '"' . $declValue . '" ';
                            $old_xcaldecl['external'][] = $declValue;
                            break;
                        case 'type':                          // no 5
                            $calendarxCaldecl .= $declValue . ' ';
                            break;
                        case 'type2':                         // no 6
                            $calendarxCaldecl .= $declValue;
                            break;
                    }
                }
                $calendarxCaldecl .= '>';
            }
            $calendarxCaldecl .= $this->nl . ']';
        } // end if(( 'xcal'...
        switch ($this->format) {
            case 'xcal':
                $calendar .= '</vcalendar>' . $this->nl;
                break;
            default:
                $calendar .= 'END:VCALENDAR' . $this->nl;
                break;
        }

        return $calendarInit . $calendarxCaldecl . $calendarStart . $calendar;
    }

    /**
     * a HTTP redirect header is sent with created, updated and/or parsed calendar
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param bool $utf8Encode
     * @param bool $gzip
     * @param bool $cdType TRUE : Content-Disposition: attachment... (default), FALSE : ...inline...
     *
     * @uses   vcalendar::getConfig()
     * @uses   vcalendar::createCalendar()
     * @uses   vcalendar::$headers
     * @uses   vcalendar::$format
     * @return bool TRUE on success, FALSE on error
     */
    function returnCalendar($utf8Encode = false, $gzip = false, $cdType = true)
    {
        $filename = $this->getConfig('filename');
        $output   = $this->createCalendar();
        if ($utf8Encode) {
            $output = utf8_encode($output);
        }
        $fsize = null;
        if ($gzip) {
            $output = gzencode($output, 9);
            $fsize  = strlen($output);
            header(self::$headers[0]);
            header(self::$headers[1]);
        } else {
            if (false !== ($temp = tempnam(sys_get_temp_dir(), 'iCr'))) {
                if (false !== file_put_contents($temp, $output)) {
                    $fsize = @filesize($temp);
                }
                unlink($temp);
            }
        }
        if (!empty($fsize)) {
            header(sprintf(self::$headers[2], $fsize));
        }
        if ('xcal' == $this->format) {
            header(self::$headers[3]);
        } else {
            header(self::$headers[4]);
        }
        $cdType = ($cdType) ? 5 : 6;
        header(sprintf(self::$headers[$cdType], $filename));
        header(self::$headers[7]);
        echo $output;

        return true;
    }

    /**
     * save content in a file
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @uses   vcalendar::createComponent()
     * @uses   vcalendar::getConfig()
     * @return bool TRUE on success, FALSE on error
     */
    function saveCalendar()
    {
        $output = $this->createCalendar();
        if (false === ($dirfile = $this->getConfig('url'))) {
            $dirfile = $this->getConfig('dirfile');
        }

        return (false === file_put_contents($dirfile, $output, LOCK_EX)) ? false : true;
    }

    /**
     * if recent version of calendar file exists (default one hour), an HTTP redirect header is sent
     * else FALSE is returned
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     *
     * @param int  $timeout default 3600 sec
     * @param bool $cdType  TRUE : Content-Disposition: attachment... (default), FALSE : ...inline...
     *
     * @uses   vcalendar::getConfig()
     * @uses   vcalendar::$headers
     * @uses   vcalendar::$format
     * @return bool TRUE on success, FALSE on error
     */
    function useCachedCalendar($timeout = 3600, $cdType = true)
    {
        if (false === ($dirfile = $this->getConfig('url'))) {
            $dirfile = $this->getConfig('dirfile');
        }
        if (!is_file($dirfile) || !is_readable($dirfile)) {
            return false;
        }
        if (time() - filemtime($dirfile) > $timeout) {
            return false;
        }
        clearstatcache();
        $dirfile  = $this->getConfig('dirfile');
        $fsize    = @filesize($dirfile);
        $filename = $this->getConfig('filename');
        if ('xcal' == $this->format) {
            header(self::$headers[3]);
        } else {
            header(self::$headers[4]);
        }
        if (!empty($fsize)) {
            header(sprintf(self::$headers[2], $fsize));
        }
        $cdType = ($cdType) ? 5 : 6;
        header(sprintf(self::$headers[$cdType], $filename));
        header(self::$headers[7]);
        if (false === ($fp = @fopen($dirfile, 'r'))) {
            return false;
        }
        fpassthru($fp);
        fclose($fp);

        return true;
    }
}
