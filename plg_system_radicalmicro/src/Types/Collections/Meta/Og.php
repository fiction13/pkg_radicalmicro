<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Types\Collections\Meta;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use RadicalMicro\Helpers\ImageHelper;
use RadicalMicro\Helpers\ParamsHelper;
use RadicalMicro\Helpers\UtilityHelper;
use RadicalMicro\Types\InterfaceTypes;

class Og implements InterfaceTypes
{
    /**
     * @var string
     * @since 1.0.0
     */
    private $uid = 'radicalmicro.meta.og';

    /**
     * @param $item
     * @param $priority
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function execute($item, $priority)
    {
        if (is_array($item))
        {
            $item = (object) $item;
        }

        $data['uid']            = $this->uid;
        $data['og:title']       = $item->title ? UtilityHelper::prepareText($item->title, 60) : ParamsHelper::getInstance()->getDefaultSiteName();
        $data['og:description'] = $item->description ? UtilityHelper::prepareText($item->description, 200) : ParamsHelper::getInstance()->getDefaultSiteDescription();
        $data['og:type']        = $item->type ?? 'website';
        $data['og:url']         = Uri::current();
        $data['priority']       = $priority;
        $data['og:image']       = (isset($item->image) && !empty($item->image)) ? UtilityHelper::prepareLink($item->image) : ImageHelper::getInstance()->getImage($item);

        return $data;
    }

    /**
     * Get config for JForm and YOOtheme Pro elements
     *
     * @param   bool  $addUid
     *
     * @return string[]
     *
     * @since 1.0.0
     */
    public function getConfig($addUid = true)
    {
        $config = [
            'title'       => '',
            'description' => '',
            'image'       => '',
            'type'        => 'website'
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

}