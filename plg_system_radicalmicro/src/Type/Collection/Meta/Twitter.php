<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\System\RadicalMicro\Type\Collection\Meta;

defined('_JEXEC') or die;

use Joomla\Plugin\System\RadicalMicro\Helper\UtilityHelper;
use Joomla\Plugin\System\RadicalMicro\Type\InterfaceType;

class Twitter implements InterfaceType
{
    /**
     * @var string
     * @since 0.2.2
     */
    private $uid = 'radicalmicro.meta.twitter';

    /**
     * @param $item
     * @param $priority
     *
     * @return array
     *
     * @since 0.2.2
     */
    public function execute($item, $priority)
    {
        $item = (object) array_merge($this->getConfig(), (array) $item);

        $data['uid']                 = $this->uid;
        $data['twitter:card']        = 'summary_large_image';
        $data['twitter:title']       = $item->title ? htmlspecialchars($item->title) : '';
        $data['twitter:description'] = $item->description ? UtilityHelper::prepareText($item->description, 200) : '';
        $data['twitter:site']        = $item->site ?? '';
        $data['twitter:creator']     = $item->creator ?? '';
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
     * @since 0.2.2
     */
    public function getConfig($addUid = true)
    {
        $config = [
            'title'       => '',
            'description' => '',
            'image'       => '',
            'creator'     => '',
            'site'        => ''
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }
}