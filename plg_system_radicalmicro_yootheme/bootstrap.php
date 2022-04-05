<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

use RadicalMicroYootheme\Helpers\YooHelper;
use YOOtheme\Builder;
use YOOtheme\Path;

return [

	// Add events
	'events' => [

		'builder.type' => [
			YooHelper::class => ['initSource', 50],
		],

        'customizer.init' => [
            YooHelper::class => ['initCustomizer', -10],
        ]
	],

	// Add builder elements
	'extend' => [

		Builder::class => function (Builder $builder) {
			$builder->addTypePath(Path::get('./elements/*/element.json'));
		}

	]
];
