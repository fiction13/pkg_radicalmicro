<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\System\RadicalMicro\Type\Collection\Schema;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\Plugin\System\RadicalMicro\Helper\UtilityHelper;
use Joomla\Plugin\System\RadicalMicro\Type\InterfaceType;

class Product implements InterfaceType
{
    /**
     * @var string
     * @since 0.2.2
     */
    private $uid = 'radicalmicro.schema.page';

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

        $data = [
            'uid'         => $this->uid,
            'priority'    => $priority,
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $item->title ? UtilityHelper::prepareText($item->title, 110) : '',
            'description' => $item->description ? UtilityHelper::prepareText($item->description, 5000) : '',
        ];

        // Sku
        if (isset($item->sku))
        {
            $data['sku'] = $item->sku;
        }

        // MPN
        if (isset($item->mpn))
        {
            $data['sku'] = $item->sku;
        }

        // Brand
        if (isset($item->brand) && !empty($item->brand))
        {
            $data['brand'] = [
                '@type' => 'Brand',
                'name'  => $item->brand
            ];
        }

        // Image
        if (isset($item->image) && !empty($item->image))
        {
            $data['image'] = [
                '@type' => 'ImageObject',
                'url'   => UtilityHelper::prepareLink($item->image)
            ];
        }

        // Offer
        if (isset($item->price) && !empty($item->price) && isset($item->currency) && !empty($item->currency))
        {
            $data['offers'] = [
                '@type'         => 'Offer',
                'url'           => Uri::current(),
                'priceCurrency' => $item->currency,
                'price'         => (float) $item->price
            ];
        }

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
            'sku'         => '',
            'mpn'         => '',
            'brand'       => '',
            'currency'    => '',
            'price'       => '',
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

}