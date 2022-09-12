<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Types\Collections\Schema\Extra;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use RadicalMicro\Helpers\UtilityHelper;
use RadicalMicro\Types\InterfaceTypes;

/**
 * @package     RadicalMicro\Types\Collections\Schema\Extra
 *
 * @source      https://developers.google.com/search/docs/advanced/structured-data/logo
 *
 * @since       __DEPLOY_VERSION__
 */
class Organization implements InterfaceTypes
{
    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private $uid = 'radicalmicro.schema.organization';

    /**
     * @param $item
     * @param $priority
     *
     * @return array
     *
     * @since __DEPLOY_VERSION__
     */
    public function execute($item, $priority)
    {
        if (is_array($item))
        {
            $item = (object) $item;
        }

        $data = [
            'uid'      => $this->uid,
            'priority' => $priority,
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'url'      => Uri::root(),
            'logo'     => UtilityHelper::prepareLink($item->image),
            'name'     => $item->title,
            'hasMap'   => $item->hasMap
        ];

        if ($item->addressCountry || $item->addressLocality || $item->addressRegion || $item->streetAddress || $item->postalCode || $item->postOfficeBoxNumber)
        {
            $data['address'] = [
                '@type' => 'PostalAddress'
            ];

            if ($item->addressCountry)
            {
                $data['address']['addressCountry'] = $item->addressCountry;
            }

            if ($item->addressLocality)
            {
                $data['address']['addressLocality'] = $item->addressLocality;
            }

            if ($item->addressRegion)
            {
                $data['address']['addressRegion'] = $item->addressRegion;
            }

            if ($item->streetAddress)
            {
                $data['address']['streetAddress'] = $item->streetAddress;
            }

            if ($item->postalCode)
            {
                $data['address']['postalCode'] = $item->postalCode;
            }

            if ($item->postOfficeBoxNumber)
            {
                $data['address']['postOfficeBoxNumber'] = $item->postOfficeBoxNumber;
            }
        }

        // Add contact point
        if ($item->phone || $item->contactType)
        {
            $data['contactPoint'] = [
                '@type' => 'ContactPoint'
            ];

            if ($item->phone)
            {
                $data['address']['phone'] = $item->phone;
            }

            if ($item->contactType)
            {
                $data['address']['contactType'] = $item->contactType;
            }
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
     * @since __DEPLOY_VERSION__
     */
    public function getConfig($addUid = true)
    {
        $config = [
            'image'               => '',
            'title'               => '',
            'addressCountry'      => '',
            'addressLocality'     => '',
            'addressRegion'       => '',
            'streetAddress'       => '',
            'postalCode'          => '',
            'postOfficeBoxNumber' => '',
            'hasMap'              => '',
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

}