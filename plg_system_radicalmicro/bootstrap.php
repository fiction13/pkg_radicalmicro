<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

use RadicalMicro\Helpers\YooHelper;
use YOOtheme\Builder;
use YOOtheme\Config;
use YOOtheme\Path;
use YOOtheme\View;
use YOOtheme\Builder\UpdateTransform;
use YOOtheme\Builder\ElementTransform;

//include_once __DIR__ . '/yootranslatetransform.php';

return [

	// Add events
    'events' => [

        'builder.type' => [
            YooHelper::class => ['initSource', 50],
        ],

    ],

	// Add builder elements
    'extend' => [

        Builder::class => function (Builder $builder) {
            $builder->addTransform('preload', YooHelper::getInstance());
			$builder->addTypePath(Path::get('./src/Elements/*/element.json'));
        }

    ]
];
