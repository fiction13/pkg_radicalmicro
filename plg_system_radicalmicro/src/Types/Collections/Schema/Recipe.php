<?php
/*
 * @package   pkg_radicalmicro
 * @version   0.2.1
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
 * @source      https://developers.google.com/search/docs/advanced/structured-data/recipe
 *
 * @since       __DEPLOY_VERSION__
 */
class Recipe implements InterfaceTypes
{
    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private $uid = 'radicalmicro.schema.page';

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
        $item = (object) array_merge($this->getConfig(), (array) $item);

        $data = [
            'uid'                => $this->uid,
            'priority'           => $priority,
            '@context'           => 'https://schema.org',
            '@type'              => 'Recipe',
            'name'               => $item->title ? UtilityHelper::prepareText($item->title, 110) : '',
            'description'        => $item->description ? UtilityHelper::prepareText($item->description, 5000) : '',
            'datePublished'      => $item->datePublished ? UtilityHelper::prepareDate($item->datePublished) : '',
            'prepTime'           => $item->prepTime ? 'PT' . $item->prepTime . 'M' : '',
            'cookTime'           => $item->cookTime ? 'PT' . $item->cookTime . 'M' : '',
            'totalTime'          => $item->totalTime ? 'PT' . $item->totalTime . 'M' : '',
            'calories'           => $item->calories ?? '',
            'recipeCategory'     => $item->recipeCategory ?? '',
            'recipeYield'        => $item->recipeYield ?? '',
            'recipeCuisine'      => $item->recipeCuisine ?? '',
            'recipeInstructions' => $item->recipeInstructions ?? '',
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

        // Ingredients
        if (isset($item->recipeIngredient) && !empty($item->recipeIngredient))
        {
            $data['recipeIngredient'] = UtilityHelper::getArrayFromText($item->recipeIngredient);
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
            'title'       => '',
            'description' => '',
            'image'       => '',
            'author'      => '',
            'datePublished'      => '',
            'prepTime'           => '',
            'cookTime'           => '',
            'totalTime'          => '',
            'calories'           => '',
            'recipeCategory'     => '',
            'recipeYield'        => '',
            'recipeCuisine'      => '',
            'recipeInstructions' => '',
            'recipeIngredient'  => ''
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

}