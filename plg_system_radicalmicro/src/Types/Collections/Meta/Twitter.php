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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
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
        $item = (object) array_merge($this->getConfig(), (array) $item);

        $data['uid']                 = $this->uid;
        $data['twitter:card']        = 'summary_large_image';
        $data['twitter:title']       = $item->title;
        $data['twitter:description'] = $item->description ? UtilityHelper::prepareText($item->description, 200) : '';
        $data['twitter:site']        = $item->site ?? Uri::root();
        $data['twitter:image']       = UtilityHelper::prepareLink($item->image);
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
            'site'        => Uri::root()
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }
}