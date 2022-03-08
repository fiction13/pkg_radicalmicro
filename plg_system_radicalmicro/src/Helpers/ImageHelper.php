<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use RadicalMicro\Helpers\UtilityHelper;

final class ImageHelper
{
    /**
     * @var
     * @since 1.0.0
     */
    protected static $instance;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    Registry
     * @since  1.0.0
     */
    protected $params;


    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->app    = Factory::getApplication();
        $this->params = ParamsHelper::getInstance()->getParams();
    }

    /**
     *
     * @return mixed|ImageHelper
     *
     * @since 1.0.0
     */
    public static function getInstance()
    {
        if (is_null(static::$instance))
        {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Get image from settings or generate
     *
     * @return mixed|\stdClass|string
     *
     * @since 1.0.0
     */
    public function getImage($imageData)
    {
        if (is_object($imageData))
        {
            $imageData = (array) $imageData;
        }

        $imagetype = $this->params->get('meta_imagetype', 'image');

        if ($imagetype === 'image')
        {
            return $this->params->get('meta_image');
        }

        if ($imagetype === 'generate')
        {
            $file = $this->getCachePath(false) . '/' . $this->getCacheFile();

            if (file_exists(JPATH_ROOT . '/' . $file))
            {
                return UtilityHelper::prepareLink($file);
            }
            else
            {
                $fileJSON = $this->getCacheFile(null);

                // Save data to cache
                $this->saveDataForCache($fileJSON, $imageData);

                return UtilityHelper::prepareLink('/index.php?' . http_build_query([
                        'option' => 'com_ajax',
                        'plugin' => 'radicalmicro',
                        'group'  => 'system',
                        'task'   => 'image',
                        'file'   => $fileJSON,
                        'format' => 'raw',
                    ]));
            }
        }

        return '';
    }

    /**
     * Generate image
     *
     * $imageData - request data array. $imageData['title'] is required
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function generate()
    {
        $file = $this->app->input->get('file', '');

        if (empty($file))
        {
            $this->showDefaultImage();
        }

        $local    = $this->getCachePath(true, $file);
        $path     = JPATH_ROOT . '/' . $local;
        $pathJSON = $path . '/' . 'json';
        $data     = [];

        if (file_exists($path . '/' . $file . '.jpg'))
        {
            $this->app->redirect($local . '/' . $file . '.jpg');
        }

        // Check json file
        if (!file_exists($pathJSON . '/' . $file . '.json'))
        {
            $this->showDefaultImage();
        }
        else
        {
            $data = json_decode(file_get_contents($pathJSON . '/' . $file . '.json'), JSON_OBJECT_AS_ARRAY);

            if ($data === null || count($data) === 0 || !isset($data['title']))
            {
                $this->showDefaultImage();
            }

        }

        // Check access on folder
        if (!is_writable($path))
        {
            $this->showDefaultImage();
        }

        // Generate image
        $backgroundType           = $this->params->get('meta_imagetype_generate_background', 'fill');
        $backgroundImage          = $this->params->get('meta_imagetype_generate_background_image');
        $backgroundColor          = $this->params->get('meta_imagetype_generate_background_color', '#000000');
        $backgroundTextBackground = $this->params->get('meta_imagetype_generate_background_text_background', '#000000');
        $backgroundTextColor      = $this->params->get('meta_imagetype_generate_background_text_color', '#ffffff');
        $backgroundTextFontSize   = (int) $this->params->get('meta_imagetype_generate_background_text_fontsize', 20);
        $backgroundTextMargin     = (int) $this->params->get('meta_imagetype_generate_background_text_margin', 10);
        $backgroundTextPadding    = (int) $this->params->get('meta_imagetype_generate_background_text_padding', 10);
        $fontCustom               = $this->params->get('meta_imagetype_generate_background_text_font', '');

        // Check background type
        if ($backgroundType == 'fill')
        {
            $img = imagecreatetruecolor(1200, 630);
            $bg  = $this->hexColorAllocate($img, $backgroundColor);
            imagefilledrectangle($img, 0, 0, 1200, 630, $bg);
        }
        else
        {
            // If bg image and no image set
            if (empty($backgroundImage))
            {
                $this->showDefaultImage();
            }

            $backgroundImage = JPATH_ROOT . '/' . ltrim($backgroundImage, '/');
            $img             = imagecreatefromstring(file_get_contents($backgroundImage));
        }

        $colorForText = $this->hexColorAllocate($img, $backgroundTextColor);
        $font         = JPATH_ROOT . '/' . implode('/', ['media', 'plg_system_radicalmicro', 'fonts', 'roboto.ttf']);
        if (!empty($fontCustom))
        {
            $font = JPATH_ROOT . '/' . ltrim($fontCustom, '/');
        }

        $width  = imagesx($img);
        $height = imagesy($img);

        $maxWidth          = imagesx($img) - (($backgroundTextMargin + $backgroundTextPadding) * 2);
        $fontSizeWidthChar = $backgroundTextFontSize / 2;
        $countForWrap      = (int) ((imagesx($img) - (($backgroundTextMargin + $backgroundTextPadding) * 2)) / $fontSizeWidthChar);

        // Set title
        $txt         = $data['title'];
        $text        = explode("\n", wordwrap($txt, $countForWrap));
        $text_width  = 0;
        $text_height = 0;

        foreach ($text as $line)
        {
            $dimensions         = imagettfbbox($backgroundTextFontSize, 0, $font, $line);
            $text_width_current = max([$dimensions[2], $dimensions[4]]) - min([$dimensions[0], $dimensions[6]]);
            $text_height        = $dimensions[3] - $dimensions[5];

            if ($text_width < $text_width_current)
            {
                $text_width = $text_width_current;
            }
        }

        $delta_y = 0;
        if (count($text) > 1)
        {
            $delta_y = $backgroundTextFontSize * -1;
            foreach ($text as $line)
            {
                $delta_y += ($dimensions[3] + $backgroundTextFontSize * 1.5);
            }
            $delta_y -= $backgroundTextFontSize * 1.5 - $backgroundTextFontSize;
        }


        $centerX = $backgroundTextPadding;
        $centerY = $height / 2;

        $centerRectX2 = $text_width > $maxWidth ? $maxWidth : $text_width;
        $centerRectY1 = $centerY - $delta_y / 2 - $backgroundTextPadding;
        $centerRectY2 = $centerY + $backgroundTextPadding * 2 + $delta_y / 2;
        $centerRectX2 += $backgroundTextPadding * 2 + $backgroundTextMargin;

        $colorForBackground = $this->hexColorAllocate($img, $backgroundTextBackground);
        imagefilledrectangle($img, $backgroundTextMargin, $centerRectY1, $centerRectX2, $centerRectY2, $colorForBackground);

        $y = $centerRectY1 + $backgroundTextPadding * 2;

        $delta_y = 0;
        foreach ($text as $line)
        {
            imagettftext($img, $backgroundTextFontSize, 0, $backgroundTextMargin + $backgroundTextPadding, $y + $delta_y, $colorForText, $font, $line);
            $delta_y += ($dimensions[3] + $backgroundTextFontSize * 1.5);
        }

        imagejpeg($img, $path . '/' . $file . '.jpg');

        //delete cache json
        if (file_exists($pathJSON . '/' . $file . '.json'))
        {
            File::delete($pathJSON . '/' . $file . '.json');
        }

        //redirect to image
        $this->app->redirect($local . '/' . $file . '.jpg', 302);
    }

    /**
     * @param $im
     * @param $hex
     *
     * @return false|int
     *
     * @since 1.0.0
     */
    private function hexColorAllocate($im, $hex)
    {
        $hex = ltrim($hex, '#');
        $a   = hexdec(substr($hex, 0, 2));
        $b   = hexdec(substr($hex, 2, 2));
        $c   = hexdec(substr($hex, 4, 2));

        return imagecolorallocate($im, $a, $b, $c);
    }

    /**
     * If an error occurred during generation, then show the default picture
     *
     * @since 1.0.0
     */
    private function showDefaultImage()
    {
        $img = $this->params->get('meta_imagetype_generate_image_for_error', '');

        if (!empty($img))
        {
            $this->app->redirect($img, 302);
        }
        else
        {
            $this->app->redirect('media/plg_system_radicalmicro/images/default.png', 302);
        }

    }

    /**
     * Get cache path for image
     *
     * @param   bool  $checkPath
     *
     * @return string
     *
     * @since 1.0.0
     */
    private function getCachePath($checkPath = false, $file = '')
    {
        $folder = $this->params->get('meta_imagetype_generate_cache', 'images');
        $path   = implode('/', [$folder, 'radicalmicro']);

        // Add subfolder
        if ($this->params->get('meta_imagetype_generate_cache_subfolder', 0))
        {
            $file      = $file ? $file . '.jpg' : $this->getCacheFile();
            $md5path   = md5($file);
            $subfolder = substr($md5path, 0, 2);
            $path      = $path . '/' . $subfolder;
        }

        if ($checkPath)
        {
            if (!file_exists(JPATH_ROOT . '/' . $path))
            {
                Folder::create(JPATH_ROOT . '/' . $path);
            }
        }

        return $path;
    }

    /**
     * @param   string  $exs
     *
     * @return string
     *
     * @since 1.0.0
     */
    private function getCacheFile($exs = 'jpg')
    {
        $file = trim(preg_replace("#\?.*?$#isu", '', $this->app->input->server->get('REQUEST_URI')), '/#');
        $file = str_replace('/', '-', $file);

        if (empty($file))
        {
            $file = 'main';
        }

        if ($exs === null)
        {
            return $file;
        }
        else
        {
            return $file . '.' . $exs;
        }
    }


    /**
     * @param  $text
     *
     * @return string|void
     *
     * @since 1.0.0
     */
    public function getImageFromText($text)
    {
        preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $text, $matches);

        if (isset($matches[1]) && $matches[1] !== '')
        {
            return UtilityHelper::prepareLink($matches[1]);
        }

        return;
    }

    /**
     * @param   string  $file
     * @param   array   $data
     *
     * @since 1.0.0
     */
    private function saveDataForCache($file = '', $data = [])
    {
        $path     = $this->getCachePath(true) . '/' . 'json';
        $pathFull = JPATH_ROOT . '/' . $path;

        if (!file_exists($pathFull))
        {
            Folder::create($pathFull);
        }

        file_put_contents($pathFull . '/' . $file . '.json', json_encode($data));

        return;
    }
}