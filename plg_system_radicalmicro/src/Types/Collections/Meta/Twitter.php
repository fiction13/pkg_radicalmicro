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

class Twitter implements InterfaceTypes
{

    /**
     * @var string
     * @since 1.0.0
     */
    private $uid = 'radicalmicro.meta.twitter';


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

        $data['uid']                 = $this->uid;
        $data['twitter:card']        = 'summary';
        $data['twitter:title']       = $item->title ? UtilityHelper::prepareText($item->title, 60) : ParamsHelper::getInstance()->getDefaultSiteName();
        $data['twitter:description'] = $item->description ? UtilityHelper::prepareText($item->description, 200) : ParamsHelper::getInstance()->getDefaultSiteDescription();
        $data['twitter:site']        = $item->site ?? Uri::root();
        $data['twitter:image']       = (isset($item->image) && !empty($item->image)) ? UtilityHelper::prepareLink($item->image) : ImageHelper::getInstance()->getImage($item);
        $data['priority']            = $priority;

        return $data;
    }


    /**
     * Get config for JForm and Yootheme Pro elements
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
            'site'        => ''
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }
}