<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace YOOtheme;

defined('_JEXEC') or die('Restricted access');

return [
    'transforms' => [
	    'render' => static function ($node)
	    {
            $node->props['id'] = $node->id;

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
