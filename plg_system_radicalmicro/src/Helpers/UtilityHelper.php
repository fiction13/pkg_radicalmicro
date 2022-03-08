<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Helpers;

defined('_JEXEC') or die;

use DateTimeZone;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;

class UtilityHelper
{
    /**
     * @param   string  $text
     * @param   int     $limit
     *
     *
     * @since 1.0.0
     */
    public static function prepareText(string $text, $limit = 0)
    {
        // Remove <script> tags
        $text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $text);

        // Strip HTML tags/comments and minify
        $text = strip_tags($text);

        // Truncate text
        if ($limit > 0)
        {
            $text = StringHelper::substr($text, 0, $limit);
        }

        return $text;
    }

    /**
     * @param $date
     *
     * @return mixed|Date|string
     *
     * @since 1.0.0
     */
    public static function prepareDate($date)
    {
        $date = is_string($date) ? trim($date) : $date;

        if (empty($date) || is_null($date) || $date == '0000-00-00 00:00:00')
        {
            return $date;
        }

        // Skip if date is already in ISO8601 format
        if (strpos($date, 'T') !== false)
        {
            return $date;
        }

        try
        {
            $timeZone = new DateTimeZone(Factory::getConfig()->get('offset', 'UTC'));

            $date = new Date($date, $timeZone);

            return $date->toISO8601(true);

        }
        catch (\Exception $e)
        {
            return $date;
        }
    }

    /**
     * @param   int  $userId
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function prepareUser(int $userId)
    {
        $user = Factory::getUser($userId);

        if ($user)
        {
            return $user->name;
        }

        return Text::_('PLG_SYSTEM_RADICALMICRO_NO_USER');
    }


    /**
     * @param   string  $url
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function prepareLink(string $url)
    {
        if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false)
        {
            return $url;
        }

        return rtrim(Uri::root(), '/') . '/' . ltrim($url, '/');
    }

    /**
     * @param   string  $string
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function cleanText(string $string)
    {
        // Remove spaces
        $string = str_replace(' ', '-', $string);

        // Remove special characters
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

        // Remove doubled dash
        return preg_replace('/-+/', '-', $string);
    }
}