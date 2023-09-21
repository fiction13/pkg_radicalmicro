<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\System\RadicalMicro\Helper;

defined('_JEXEC') or die;

use DateTimeZone;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use stdClass;

class UtilityHelper
{
    /**
     * Method for prepare text
     *
     * @param   string  $text
     * @param   int     $limit
     *
     * @return string
     *
     * @since 0.2.2
     */
    public static function prepareText(string $text, $limit = 0)
    {
        // Remove <script> tags
        $text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $text);

        // Strip HTML tags/comments and minify
        $text = str_replace(array("\r\n", "\n", "\r"), ' ', trim(strip_tags($text)));

        // Truncate text
        if ($limit > 0)
        {
            if (strlen($text) > $limit)
            {
                $text = StringHelper::substr($text, 0, $limit) . '...';
            }
            else
            {
                $text = StringHelper::substr($text, 0, $limit);
            }
        }

        return $text;
    }

    /**
     * Method for prepare date
     *
     * @param $date
     *
     * @return mixed|Date|string
     *
     * @since 0.2.2
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
            $date     = strtotime($date);
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
     * Method for prepare user
     *
     * @param   int|string  $user
     *
     * @return string
     *
     * @since 0.2.2
     */
    public static function prepareUser($user)
    {
        if (!is_numeric($user))
        {
            return $user;
        }

        $userObject = Factory::getUser($user);

        if ($userObject)
        {
            return $userObject->name;
        }

        return Text::_('PLG_SYSTEM_RADICALMICRO_NO_USER');
    }

    /**
     * Method for prepare link
     *
     * @param   string  $url
     *
     * @return string
     *
     * @since 0.2.2
     */
    public static function prepareLink($url, $relative = false)
    {
        if (empty($url))
        {
            return '';
        }

        // No prepare direct links
        if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false)
        {
            return $url;
        }

        // Clean image providers
        if ($pos = strpos($url, '#'))
        {
            $url = substr($url, 0, $pos);
        }

        if ($relative)
        {
            return $url;
        }

        return rtrim(Uri::root(), '/') . '/' . ltrim($url, '/');
    }

    /**
     * Method for get image size
     *
     * @param   string  $src  Image url
     *
     * @return stdClass
     *
     * @since __DEPLOY_VERSION__
     */
    public static function getImageSize($src)
    {
        $result         = new \stdClass();
        $result->width  = '';
        $result->height = '';

        if (empty($src) || strpos($src, 'com_ajax') !== false)
        {
            return $result;
        }

        // Clean image providers
        if (strpos($src, '#') !== false)
        {
            $imgParams      = explode('#', $src);
            $uri            = new Uri($imgParams[1]);
            $result->width  = $uri->getVar('width');
            $result->height = $uri->getVar('height');

            return $result;
        }

        try
        {
            $src = str_replace(Uri::root(), JPATH_ROOT . '/', $src);
            $imageSize = getimagesize($src);
            $result->width  = $imageSize[0];
            $result->height = $imageSize[1];
        }
        catch (\Exception $e)
        {
            // noop
        }

        return $result;
    }

    /**
     * Method for clean text
     *
     * @param   string  $string
     *
     * @return string
     *
     * @since 0.2.2
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

    /**
     * Get breadcrumbs
     *
     * @since 0.2.2
     */
    public static function getBreadCrumbs($home = true)
    {
        $app     = Factory::getApplication();
        $pathway = $app->getPathway();
        $items   = $pathway->getPathWay();
        $menu    = $app->getMenu();
        $lang    = $app->getLanguage();
        $count   = count($items);

        // We don't use $items here as it references JPathway properties directly
        $crumbs = [];

        for ($i = 0; $i < $count; $i++)
        {
            // Skip null link and empty name properties
            if (is_null($items[$i]->link) || !$items[$i]->name)
            {
                continue;
            }

            $crumbs[$i]       = new stdClass;
            $crumbs[$i]->name = trim(stripslashes(htmlspecialchars($items[$i]->name, ENT_COMPAT, 'UTF-8')));
            $crumbs[$i]->link = Route::_($items[$i]->link, true, 0, true);
        }

        // Add home menu
        if ($home)
        {
            // Get home menu
            $home = \JLanguageMultilang::isEnabled() ? $menu->getDefault($lang->getTag()) : $menu->getDefault();

            $item       = new stdClass;
            $item->name = htmlspecialchars(Text::_('PLG_SYSTEM_RADICALMICRO_BREADCRUMBS_HOME'));
            $item->link = self::prepareLink(Route::_('index.php?Itemid=' . $home->id, true, 0, true));

            array_unshift($crumbs, $item);
        }

        // Fix last item's missing URL
        end($crumbs);
        if (empty($crumbs->link))
        {
            $crumbs[key($crumbs)]->link = Uri::current();
        }

        return $crumbs;
    }

    /**
     * @param   Registry  $params
     * @param   string    $name  - for example 'radicalmicro.schema.article.9' for page
     * @param   string    $body
     *
     * @return boolean
     *
     * @since 0.2.2
     */
    public static function checkSchema(Registry $params, string $name, $body)
    {
        // If empty name
        if (!$name)
        {
            return false;
        }

        $path = explode('.', $name);
        $type = $schemaType = $path[2];

        // Page schema type
        if (count($path) > 3)
        {
            $type = 'page';
        }

        // Check enable param
        if (!$params->get('schema_enable_type_' . $type, 1))
        {
            return false;
        }

        // If don't need to check current microdata
        if (!$params->get('extra_check_current'))
        {
            return true;
        }

        // We need check json-ld and microdata in the body

        // Check JSON-LD in the body
        if (strpos($body, '//schema.org/') !== false)
        {
            $regex = '/<script type="?application\/ld\+json"?>(.*?)<\/script>/msi';

            preg_match_all($regex, $body, $matches, PREG_SET_ORDER, 0);

            // Maybe no json-ld on page
            if ($matches)
            {
                foreach ($matches as $match)
                {
                    // Check current schema type
                    if (preg_match('/"@type"\s*:\s*"' . $schemaType . '"/si', $match[0]))
                    {
                        return false;
                    }
                }
            }
        }

        // Check microdata
        if (strpos($body, 'itemtype') !== false)
        {
            $regex = '/(itemscope)? itemtype=(\'|")?http(s?):\/\/(www.)?schema.org\/' . $schemaType . '(\'|")?/msi';

            if (preg_match($regex, $body))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * @param   Registry  $params
     * @param   string    $name  - for example 'og:image'
     * @param   string    $attribute
     * @param   string    $body
     *
     * @return boolean
     *
     * @since 0.2.2
     */
    public static function checkMeta(Registry $params, string $name, string $attribute, $body)
    {
        // If empty name
        if (!$name)
        {
            return false;
        }

        list($collection,) = explode(':', $name);

        // Check enable param
        if (!$params->get('meta_enable_' . $collection, 0))
        {
            return false;
        }

        // If don't need to check current microdata
        if (!$params->get('extra_check_current'))
        {
            return true;
        }

        // We need check meta tags in the body
        if (strpos($body, $attribute) !== false)
        {
            $regex = '/<meta.*' . $attribute . '="' . $name . '".*content="(.*)".*>/';

            if (preg_match($regex, $body))
            {
                return false;
            }
        }

        return true;
    }


    /**
     * @param $text
     *
     * @return mixed|string|void
     *
     * @since 0.2.2
     */
    public static function getFirstImage($text)
    {
        if (empty($text))
        {
            return;
        }

        preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $text, $img);

        if (!empty($img) && isset($img['src']))
        {
            return $img['src'];
        }

        return '';
    }

    /**
     * @param $text
     *
     * @return array
     *
     * @since 0.2.2
     */
    public static function getArrayFromText($text)
    {
        if (empty($text))
        {
            return [];
        }

        if (is_string($text))
        {
            $result = preg_split('/\r\n|\r|\n/', $text);
            $result = array_values(array_filter($result));
            $result = array_map('strip_tags', $result);

            return $result;
        }

        return $text;
    }
}