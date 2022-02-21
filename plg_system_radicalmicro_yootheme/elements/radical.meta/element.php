<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace YOOtheme;

return [
    'transforms' => [
	    'render' => function ($node)
	    {
			unset(
				$node->props['animation'],
				$node->props['name'],
				$node->props['status'],
				$node->props['source']
			);

            return $node;
        },
    ]
];
