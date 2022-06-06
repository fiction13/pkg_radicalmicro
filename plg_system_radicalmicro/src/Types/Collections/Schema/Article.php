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
use RadicalMicro\Helpers\UtilityHelper;
use RadicalMicro\Types\InterfaceTypes;

/**
 * @package     RadicalMicro\Types\Collections\Schema
 *
 * @source      https://developers.google.com/search/docs/advanced/structured-data/article
 *
 * @since       1.0.0
 */
class Article implements InterfaceTypes
{
    /**
     * @var string
     * @since 1.0.0
     */
    private $uid = 'radicalmicro.schema.page';

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

        $data = [
            'uid'              => $this->uid,
            'priority'         => $priority,
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => $item->title ? UtilityHelper::prepareText($item->title, 110) : '',
            'description'      => $item->description ? UtilityHelper::prepareText($item->description, 5000) : '',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => Uri::current()
            ],
            'datePublished'    => $item->datePublished ? UtilityHelper::prepareDate($item->datePublished) : '',
            'dateModified'     => $item->dateModified ? UtilityHelper::prepareDate($item->dateModified) : '',
        ];

        // Author
        if ($item->author && !empty($item->author))
        {
            $data['publisher'] = [
                '@type' => 'Person',
                'name'  => UtilityHelper::prepareUser($item->author)
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