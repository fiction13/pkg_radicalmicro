<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die('Restricted access');

use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Helpers\Tree\OGHelper;

$ogData = TypesHelper::execute('meta', $props['meta'], $props, 0.6);
OGHelper::getInstance()->addChild('root', $ogData);