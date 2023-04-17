<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die('Restricted access');

use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\Tree\OGHelper;

$collections = PathHelper::getInstance()->getTypes('meta');

if ($collections)
{
    foreach ($collections as $collection)
    {
        $ogData = TypesHelper::execute('meta', $collection, $props, 0.9);
        OGHelper::getInstance()->addChild('root', $ogData);
    }
}