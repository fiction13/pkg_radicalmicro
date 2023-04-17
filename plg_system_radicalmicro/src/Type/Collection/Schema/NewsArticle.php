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

class NewsArticle implements InterfaceType
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
            'uid'              => $this->uid,
            'priority'         => $priority,
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'url'              => Uri::current(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => Uri::current()
            ],
            'headline'         => $item->title ? UtilityHelper::prepareText($item->title, 110) : '',
            'articleBody'      => $item->description ? UtilityHelper::prepareText($item->description, 5000) : '',
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
     * @since 0.2.2
     */
    public function getConfig($addUid = true)
    {
        $config = [
            'title'         => '',
            'description'   => '',
            'datePublished' => '',
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