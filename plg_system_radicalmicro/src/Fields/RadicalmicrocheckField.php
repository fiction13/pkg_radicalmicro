<?php
/*
 * @package   pkg_radicalmicro
 * @version   0.2.4
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Fields;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Plugin\PluginHelper;
use RadicalMicro\Helpers\UtilityHelper;

class RadicalmicrocheckField extends FormField
{
    /**
     * @var array
     *
     * @since 0.2.2
     */
    protected $params = [];

    /**
     * The form field type.
     *
     * @var  string
     *
     * @since  0.2.2
     */
    protected $type = 'radicalmicrocheck';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  0.2.2
     */
    protected $layout = 'plugins.system.radicalmicro.fields.radicalmicrocheck';

    /**
     * Hide the label when rendering the form field.
     *
     * @var    boolean
     * @since  0.2.2
     */
    protected $hiddenLabel = true;

    /**
	 * Method to instantiate the form field object.
	 *
	 * @param   Form  $form  The form to attach to the form field object.
	 *
	 * @since   0.2.2
	 */
    public function __construct($form = null)
    {
        parent::__construct($form);

        Factory::getApplication()->getLanguage()->load('plg_system_radicalmicro', JPATH_ADMINISTRATOR, null, true);
    }

    /**
     * Method to get the field input markup fora grouped list.
     * Multiselect is enabled by using the multiple attribute.
     *
     * @return  string  The field input markup.
     *
     * @since   0.2.2
     */
    protected function getInput()
    {
        $data             = $this->getLayoutData();
        $data['isEnabled'] = $this->checkPlugin();

        return $this->getRenderer($this->layout)->render($data);
    }

    /**
     * Method to check Radical Micro plugin enable
     *
     * @return  string  The field input markup.
     *
     * @since   0.2.2
     */
    protected function checkPlugin()
    {
        return PluginHelper::isEnabled('system', 'radicalmicro');
    }
}