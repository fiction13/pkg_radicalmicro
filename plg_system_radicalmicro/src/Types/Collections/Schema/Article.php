<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Types\Collections\Schema;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use RadicalMicro\Helpers\ImageHelper;
use RadicalMicro\Helpers\UtilityHelper;
use RadicalMicro\Types\InterfaceTypes;

class Article implements InterfaceTypes
{
    /**
     * @var string
     * @since 1.0.0
     */
    private $uid = 'radicalmicro.schema.article';

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

        $data = [
            'uid'              => $this->uid . '.' . $item->id,
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => $item->title ? UtilityHelper::prepareText($item->title, 110) : '',
            'description'      => $item->description ? UtilityHelper::prepareText($item->description, 5000) : '',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                'id'    => Uri::current()
            ],
            'datePublished'    => $item->datePublished ? UtilityHelper::prepareDate($item->datePublished) : '',
            'dateModified'     => $item->dateModified ? UtilityHelper::prepareDate($item->dateModified) : '',
            'author'           => [
                '@type' => 'Person',
                'name'  => $item->author ? UtilityHelper::prepareUser($item->author) : ''
            ]
        ];

        if (isset($item->image))
        {
            $data['image'] = [
                '@type' => 'ImageObject',
                'url'   => $item->image ? UtilityHelper::prepareLink($item->image) : ImageHelper::getInstance()->getImage($data)
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
     * @since 1.0.0
     */
    public function getConfig($addUid = true)
    {
        $config = [
            'title'         => '',
            'datePublished' => '',
            'description'   => '',
            'dateModified'  => '',
            'author'        => '',
            'image'         => ''
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

}