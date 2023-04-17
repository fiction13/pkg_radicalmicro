<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalMicro\Standard\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\System\RadicalMicro\Helper\PathHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\Tree\OGHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\TypesHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\UtilityHelper;

class Standard extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    bool
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepareForm'   => 'onContentPrepareForm',
            'onRadicalMicroProvider' => 'onRadicalMicroProvider'
        ];
    }

    /**
     * Adds forms for override
     *
     * @param   Event  $event  Event.
     *
     * @return  boolean
     *
     * @since   0.2.2
     */
    public function onContentPrepareForm(Event $event)
    {
        $form = $event->getArgument(0);
        $data = $event->getArgument(1);
        $app  = Factory::getApplication();

        // Check current plugin form edit
        if ($app->isClient('administrator') && $form->getName() == 'com_plugins.plugin')
        {
            $plugin = PluginHelper::getPlugin('radicalmicro', 'default');

            if ($app->getInput()->getInt('extension_id') === (int) $plugin->id)
            {
                // Create simple xml element
                $element = new \SimpleXMLElement('<field />');

                $element->addAttribute('name', 'title');
                $element->addAttribute('label', Text::_('PLG_RADICALMICRO_DEFAULT_PARAM_TITLE'));
                $element->addAttribute('type', 'text');
                $element->addAttribute('default', Text::_('PLG_RADICALMICRO_DEFAULT_PARAM_TITLE_VALUE'));
                $element->addAttribute('disabled', true);

                $form->setField($element, null, true, 'basic');
            }
        }

        return true;
    }

    /**
     * OnRadicalMicroProvider event
     *
     * @param   Event  $event  Event.
     *
     * @return  void
     *
     * @since   0.2.2
     */
    public function onRadicalMicroProvider(Event $event)
    {
        $params   = $event->getArgument('params');
        $document = Factory::getApplication()->getDocument();

        // Data object
        $object = new \stdClass();

        // Set title
        $object->title = $document->getTitle();

        // Set description
        if ($this->params->get('description') !== 'none')
        {
            $object->description = $document->getDescription();
        }

        // Set image
        if ($this->params->get('image_choice') === 'static')
        {
            $object->image = $this->params->get('image');
        }
        else if ($this->params->get('image_choice') === 'body')
        {
            list(, $body) = explode('<body', $this->app->getBody());
            $object->image = UtilityHelper::getFirstImage($body);
        }

        // Get and set opengraph data
        $collections = PathHelper::getInstance()->getTypes('meta');

        foreach ($collections as $collection)
        {
            $ogData = TypesHelper::execute('meta', $collection, $object, 0.4);
            OGHelper::getInstance()->addChild('root', $ogData);
        }
    }
}