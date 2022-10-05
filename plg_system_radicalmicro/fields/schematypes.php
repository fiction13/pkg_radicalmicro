<?php
/*
 * @package   pkg_radicalmicro
 * @version   0.2.1
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use RadicalMicro\Helpers\PathHelper;

FormHelper::loadFieldClass('list');

class JFormFieldSchemaTypes extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $type = 'schemaTypes';

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