<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\System\RadicalMicro\Type\Collection\Schema\Extra;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\Plugin\System\RadicalMicro\Helper\UtilityHelper;
use Joomla\Plugin\System\RadicalMicro\Type\InterfaceType;

class Breadcrumblist implements InterfaceType
{
    /**
     * @var string
     * @since 0.2.2
     */
    private $uid = 'radicalmicro.schema.breadcrumblist';

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
        if (is_array($item))
        {
            $item = (object) $item;
        }

        $breadCrumbs = UtilityHelper::getBreadCrumbs();

        $breadCrumbsData = [];

        foreach ($breadCrumbs as $key => $value)
        {
            $breadCrumbsData[] = [
                '@type'    => 'ListItem',
                'position' => ($key + 1),
                'name'     => $value->name,
                'item'     => $value->link
            ];
        }

        $data = [
            'uid'             => $this->uid,
            'priority'        => $priority,
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $breadCrumbsData
        ];

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
        $config = [];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

}