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

use Joomla\CMS\Uri\Uri;
use Joomla\Plugin\System\RadicalMicro\Helper\UtilityHelper;
use Joomla\Plugin\System\RadicalMicro\Type\InterfaceType;

class Og implements InterfaceType
{
    /**
     * @var string
     * @since 0.2.2
     */
    private $uid = 'radicalmicro.meta.og';

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

        $data['uid']            = $this->uid;
        $data['og:title']       = $item->title ? htmlspecialchars($item->title) : '';
        $data['og:description'] = $item->description ? UtilityHelper::prepareText($item->description, 200) : '';
        $data['og:type']        = $item->type ?? 'website';
        $data['og:url']         = Uri::current();
        $data['og:image']       = UtilityHelper::prepareLink($item->image);
        $data['priority']       = $priority;

        return $data;
    }

    /**
     * Get config for JForm and YOOtheme Pro elements
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
            'type'        => 'website'
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

}