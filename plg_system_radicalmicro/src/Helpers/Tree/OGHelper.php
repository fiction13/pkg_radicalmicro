<?php
/*
 * @package   pkg_radicalmicro
 * @version   0.2.1
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Helpers\Tree;

defined('_JEXEC') or die;

class OGHelper extends UnversalTreeHelper
{

    public static function getInstance($name = 'opengrapgh')
    {
        return parent::getInstance($name);
    }

    public function getBuild($uid = null): array
    {
        $output = [];
        $map    = &$this->map;

        $override = &$this->override;
        $this->findNode($map, $map, '', static function (&$element) use (&$output, $uid, $override) {

            foreach ($element as $key => $value)
            {
                if ($key === 'child')
                {
                    continue;
                }

                $item[$key] = $value;
            }

            if (isset($item['uid'], $override[$item['uid']]))
            {
                $override = $override[$element['uid']];

                foreach ($override as $key => $value)
                {
                    $item[$key] = $value;
                }

            }

            if (
                isset($item['uid']) &&
                $item['uid'] === $uid
            )
            {
                return;
            }

            // Check priority and collapse old array and new array
            if (isset($output[$item['uid']]))
            {
                // If current priority less
                if ($output[$item['uid']]->priority >= $item['priority'])
                {
                    $output[$item['uid']] = (object) array_merge(array_filter($item), (array) $output[$item['uid']]);
                }
                else
                {
                    $output[$item['uid']] = (object) array_merge((array) $output[$item['uid']], array_filter($item));
                }
            }
            else
            {
                $output[$item['uid']] = (object) array_filter($item);
            }

        }, false);

        if ($output)
        {
            foreach ($output as $item)
            {
                unset(
                    $item->priority,
                    $item->uid
                );
            }
        }

        return $output;
    }

}