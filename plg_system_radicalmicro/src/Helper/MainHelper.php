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

use Joomla\Plugin\System\RadicalMicro\Helper\Tree\SchemaHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\Tree\OGHelper;

class MainHelper
{
    /**
     * Method to build JSON-LD schema.org
     *
     * @param $body
     *
     * @return string
     *
     * @since 0.2.2
     */
    public static function buildSchema(&$body, $params)
    {
        $jsonLd = array();

        // Get data from tree
        $schemaData = SchemaHelper::getInstance()->getBuild('root');

        // Trigger event
        EventHelper::beforeBuild((object) self::class, 'schema', $schemaData);

        foreach ($schemaData as $key => $schema)
        {
            if (UtilityHelper::checkSchema($params, $key, $body))
            {
                 $jsonLd[] = "\n<script type=\"application/ld+json\">" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</script>";
            }
        }

        return implode("\n", $jsonLd);

    }

    /**
     * Method to build opengraph metatags
     *
     * @param $body
     *
     * @return string
     *
     * @since 0.2.2
     */
    public static function buildOpengraph(&$body, $params)
    {
        $meta = [];

        // Get data from tree
        $metaData = OGHelper::getInstance()->getBuild('root');

        // Trigger event
        EventHelper::beforeBuild((object) self::class, 'meta', $metaData);

        foreach ($metaData as $key => $og)
        {
            if ($key === 'radicalmicro.meta.og')
            {
                $attribute = 'property';
            }
            else
            {
                $attribute = 'name';
            }

            foreach ($og as $property => $content)
            {
                if (!empty($content) && UtilityHelper::checkMeta($params, $property, $attribute, $body))
                {
                    $meta[] = '<meta ' . $attribute . '="' . $property . '" content="' . $content . '" />';
                }
            }
        }

        return implode("\n", $meta);
    }

}