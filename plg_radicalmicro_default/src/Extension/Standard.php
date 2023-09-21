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
            'onRadicalMicroProvider' => 'onRadicalMicroProvider'
        ];
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
        if ($this->params->get('title') !== 'none')
        {
            $object->title = $document->getTitle();
        }

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

        // Set site name
        if ($this->params->get('site_name'))
        {
            $object->site_name = $this->params->get('site_name');
        }

        // Set locale
        if ($this->params->get('locale'))
        {
            $language = Factory::getApplication()->getLanguage();
            $tag      = $language->getTag();
            list($locale) = explode('-', $tag);
            $object->locale = $locale;
        }

        // Set twitter site
        if ($this->params->get('site'))
        {
            $object->site = $this->params->get('site');
        }

        // Set twitter creator
        if ($this->params->get('creator'))
        {
            $object->creator = $this->params->get('creator');
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