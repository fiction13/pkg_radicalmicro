<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string  $autocomplete   Autocomplete attribute for the field.
 * @var   boolean $autofocus      Is autofocus enabled?
 * @var   string  $class          Classes for the input.
 * @var   string  $description    Description of the field.
 * @var   boolean $disabled       Is this field disabled?
 * @var   string  $group          Group the field belongs to. <fields> section in form XML.
 * @var   boolean $hidden         Is this field hidden in the form?
 * @var   string  $hint           Placeholder for the field.
 * @var   string  $id             DOM id of the field.
 * @var   string  $label          Label of the field.
 * @var   string  $labelclass     Classes to apply to the label.
 * @var   boolean $multiple       Does this field support multiple values?
 * @var   string  $name           Name of the input field.
 * @var   string  $onchange       Onchange attribute for the field.
 * @var   string  $onclick        Onclick attribute for the field.
 * @var   string  $pattern        Pattern (Reg Ex) of value of the form field.
 * @var   boolean $readonly       Is this field read only?
 * @var   boolean $repeat         Allows extensions to duplicate elements.
 * @var   boolean $required       Is this field required?
 * @var   integer $size           Size attribute of the input.
 * @var   boolean $spellcheck     Spellcheck state for the form field.
 * @var   string  $validate       Validation rules to apply.
 * @var   string  $value          Value attribute of the field.
 * @var   array   $groups         Groups of options available for this field.
 * @var   string  $dataAttribute  Miscellaneous data attributes preprocessed for HTML output
 * @var   array   $dataAttributes Miscellaneous data attribute for eg, data-*
 * @var bool      $isEnabled      Enable or disable main plugin
 */

?>

<div class="alert alert-<?php echo $isEnabled ? 'success' : 'danger'; ?>">
    <h4 class="alert-heading"><?php echo Text::_('PLG_SYSTEM_RADICALMICRO_FIELD_CHECK_HEADING'); ?></h4>
    <div><?php echo Text::_('PLG_SYSTEM_RADICALMICRO_FIELD_CHECK_TITLE'); ?></div>
    <hr>
    <div><?php echo $isEnabled ? Text::_('PLG_SYSTEM_RADICALMICRO_FIELD_CHECK_RESULT_ENABLED') : Text::_('PLG_SYSTEM_RADICALMICRO_FIELD_CHECK_RESULT_DISABLED'); ?></div>
</div>