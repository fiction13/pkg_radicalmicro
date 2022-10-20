<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Fields;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use RadicalMicro\Helpers\PathHelper;
use stdClass;

class RadicalmicroschematypesField extends ListField
{
    /**
     * The form field type.
     *
     * @var  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $type = 'radicalmicroschematypes';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @throws  Exception
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function getOptions()
    {
        $options = [];
        $types   = PathHelper::getInstance()->getTypes('schema');

        foreach ($types as $type)
        {
            $option        = new stdClass();
            $option->value = $type;
            $option->text  = ucfirst($type);
            $options[]     = $option;
        }

        return $options;
    }
}