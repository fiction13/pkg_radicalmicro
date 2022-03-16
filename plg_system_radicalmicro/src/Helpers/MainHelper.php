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

use RadicalMicro\Helpers\Tree\OGHelper;
use RadicalMicro\Helpers\Tree\SchemaHelper;

class MainHelper
{
    /**
     * Method to build JSON-LD schema.org
     *
     * @param $body
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function buildSchema(&$body, $params)
    {
        $jsonLd = array();

        // Get data from tree
        $schemaData = SchemaHelper::getInstance()->getBuild('root');

        foreach ($schemaData as $key => $schema)
        {
            if (UtilityHelper::checkSchema($params, $key, $body))
            {
                $jsonLd[] = '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</script>';
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
     * @since 1.0.0
     */
    public static function buildOpengraph(&$body, $params)
    {
        $meta = [];

        // Get data from tree
        $metaData = OGHelper::getInstance()->getBuild('root');

        foreach ($metaData as $og)
        {
            foreach ($og as $property => $content)
            {
                if (!empty($content) && UtilityHelper::checkMeta($params, $property, $body))
                {
                    $meta[] = '<meta property="' . $property . '" content="' . $content . '" />';
                }
            }
        }

        return implode("\n", $meta);
    }

}