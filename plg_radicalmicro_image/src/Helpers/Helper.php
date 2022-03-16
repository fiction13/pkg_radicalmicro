<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Registry\Registry;
use RadicalMicro\Helpers\Tree\OGHelper;
use RadicalMicro\Helpers\UtilityHelper;


/**
 * @package     pkg_radicalmicro
 *
 * @since       1.0.0
 */
class plgRadicalMicroImageHelper
{
    /**
     * @var array
     *
     * @since 1.0.0
     */
    protected $params = [];

    /**
     * @param   Registry  $params
     *
     * @throws Exception
     */
    public function __construct(Registry $params)
    {
        $this->params = $params;
        $this->app    = Factory::getApplication();
    }

    /**
     * Method get provider data
     *
     * @return object|void
     *
     * @since 1.0.0
     */
    public function getProviderData()
    {
        // Data object
        $object        = new stdClass();
        $object->image = $this->getImage();

        return $object;
    }

    /**
     * Get image from settings or generate
     *
     * @return void|string
     *
     * @since 1.0.0
     */
    public function getImage()
    {
        $imagetype = $this->params->get('imagetype', 'image');

        if ($imagetype === 'image')
        {
            return $this->params->get('image') ?? $this->showDefaultImage(false);
        }

        if ($imagetype === 'generate')
        {
            $fileName = $this->getCacheFile();
            $file     = $this->getCachePath() . '/' . $fileName . '.jpg';

            if (file_exists(JPATH_ROOT . '/' . $file))
            {
                return UtilityHelper::prepareLink($file);
            }
            else
            {
                // Get title from OG build
                $build = OgHelper::getInstance()->getBuild('root');

                if (!isset($build['radicalmicro.meta.og']))
                {
                    return $this->showDefaultImage(false);
                }

                $title = $build['radicalmicro.meta.og']->{'og:title'} ?? '';

                if (empty($title))
                {
                    return $this->showDefaultImage(false);
                }

                $hash = md5($fileName . ':' . $this->params->get('imagetype_generate_secret_key'));

                return UtilityHelper::prepareLink('/index.php?' . http_build_query([
                        'option' => 'com_ajax',
                        'plugin' => 'radicalmicroimage',
                        'task'   => 'generate',
                        'title'  => $title,
                        'file'   => $fileName,
                        'hash'   => $hash,
                        'format' => 'raw',
                    ]));
            }
        }

        return '';
    }

    /**
     * Generate image
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function generate()
    {
        // Check file
        $file  = $this->app->input->get('file', '', 'raw');
        $title = $this->app->input->get('title', '', 'raw');
        $hash  = $this->app->input->get('hash', '', 'raw');

        // Check hash, title and file
        if ($hash != md5($file . ':' . $this->params->get('imagetype_generate_secret_key')) || empty($title) || empty($file))
        {
            $this->showDefaultImage();
        }

        $local = $this->getCachePath($file);
        $path  = JPATH_ROOT . '/' . $local;

        if (file_exists($path . '/' . $file . '.jpg'))
        {
            $this->app->redirect($local . '/' . $file . '.jpg');
        }

        // Check access on folder
        if (!is_writable($path))
        {
            $this->showDefaultImage();
        }

        // Generate image
        $backgroundType           = $this->params->get('imagetype_generate_background', 'fill');
        $backgroundImage          = $this->params->get('imagetype_generate_background_image');
        $backgroundColor          = $this->params->get('imagetype_generate_background_color', '#000000');
        $backgroundTextBackground = $this->params->get('imagetype_generate_background_text_background', '#000000');
        $backgroundTextColor      = $this->params->get('imagetype_generate_background_text_color', '#ffffff');
        $backgroundTextFontSize   = (int) $this->params->get('imagetype_generate_background_text_fontsize', 20);
        $backgroundTextMargin     = (int) $this->params->get('imagetype_generate_background_text_margin', 10);
        $backgroundTextPadding    = (int) $this->params->get('imagetype_generate_background_text_padding', 10);
        $fontCustom               = $this->params->get('imagetype_generate_background_text_font', '');

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
        $font         = JPATH_ROOT . '/' . implode('/', ['media', 'plg_radicalmicro_image', 'fonts', 'roboto.ttf']);

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
        $txt         = $title;
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
    private function showDefaultImage($redirect = true)
    {
        $img = $this->params->get('imagetype_generate_image_for_error', '');

        if (empty($img))
        {
            $img = 'media/plg_radicalmicro_image/images/default.png';
        }

        if ($redirect)
        {
            $this->app->redirect('media/plg_radicalmicro_image/images/default.png', 302);
        }

        return $img;

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
    private function getCachePath($file = null)
    {
        $folder = $this->params->get('imagetype_generate_cache', 'images');
        $path   = implode('/', [$folder, 'radicalmicro']);

        // Add subfolder
        if ($this->params->get('imagetype_generate_cache_subfolder', 0))
        {
            $file      = $file ? $file : $this->getCacheFile();
            $md5path   = md5($file);
            $subfolder = substr($md5path, 0, 2);
            $path      = $path . '/' . $subfolder;
        }

        if (!file_exists(JPATH_ROOT . '/' . $path))
        {
            Folder::create(JPATH_ROOT . '/' . $path);
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
    private function getCacheFile()
    {
        $file = trim(preg_replace("#\?.*?$#isu", '', $this->app->input->server->get('REQUEST_URI')), '/#');
        $file = str_replace('/', '-', $file);

        if (!$file)
        {
            $file = 'home';
        }

        return $file;
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
}