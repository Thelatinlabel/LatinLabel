<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Model\Parser;

use Magefan\WebP\Api\HtmlParserInterface;
use Magefan\WebP\Api\CreateWebPImageInterface;
use Magefan\WebP\Api\GetWebPUrlInterface;
use Magefan\WebP\Api\GetOriginWebPUrlInterface;
use Magefan\WebP\Model\Config;

class Html implements HtmlParserInterface
{
    /**
     * @var CreateWebPImageInterface
     */
    private $createWebPImage;

    /**
     * @var GetWebPUrlInterface
     */
    private $getWebPUrl;

    /**
     * @var GetOriginWebPUrlInterface
     */
    private $getOriginWebPUrl;

    /**
     * @var Config
     */
    private $config;

    /**
     * Html Parser constructor.
     * @param CreateWebPImageInterface $createWebPImage
     * @param GetWebPUrlInterface $getWebPUrl
     * @param GetOriginWebPUrlInterface $getOriginWebPUrl
     * @param Config $config
     */
    public function __construct(
        CreateWebPImageInterface $createWebPImage,
        GetWebPUrlInterface $getWebPUrl,
        GetOriginWebPUrlInterface $getOriginWebPUrl,
        Config $config
    ) {
        $this->createWebPImage = $createWebPImage;
        $this->getWebPUrl = $getWebPUrl;
        $this->getOriginWebPUrl = $getOriginWebPUrl;
        $this->config = $config;
    }

    /**
     * @param string $output
     * @return string
     */
    public function execute(string $output): string
    {

        if (false !== strpos($output, 'amp-page-wrapper')) {
            return $output;
        }

        if ($this->config->convertExistingPictureTag()) {
            $output = $this->processExistingPictureTags($output);
        }

        if ($this->config->convertBackgroundImages()) {
            $output = $this->processBackgroundImages($output);
        }

        $matches = [];
        $regex = '/<([^<]+)\s+(src|data-src)=[\"\']([^\"\']+)\.(png|jpg|jpeg|PNG|JPG|JPEG|svg|webp|gif|GIF)([^>]+)>/mi';
        if (preg_match_all($regex, $output, $matches, PREG_OFFSET_CAPTURE) === false) {
            return $output;
        }

        $position = 0;

        foreach ($matches[0] as $i => $match) {
            $offset = $match[1] + $position;
            $htmlTag = $matches[0][$i][0];

            if (0 !== stripos($htmlTag, '<img')) {
                continue;
            }

            $imageUrl = $matches[3][$i][0] . '.' . $matches[4][$i][0];

            $newHtmlTag = $this->getNewHtmlTag($imageUrl, $htmlTag);
            if (!$newHtmlTag) {
                continue;
            }

            $output = substr_replace($output, $newHtmlTag, $offset, strlen($htmlTag));
            $position = $position + (strlen($newHtmlTag) - strlen($htmlTag));
        }

        /* Disable Owl Slider LazyLoad *
        $output = str_replace(
            ['"lazyLoad":true,', '&quot;lazyLoad&quot;:true,', 'owl-lazy'],
            ['"lazyLoad":false,', '&quot;lazyLoad&quot;:false,', ''],
            $output
        );
        */

        return $output;
    }

    /**
     * Return Picture Tag (Html) with original and webp images
     * @param string $imageUrl
     * @param string $htmlTag
     * @return string
     */
    public function getNewHtmlTag(string $imageUrl, string $htmlTag): string
    {
        if (false !== strpos($htmlTag, 'data-webpconverted')) {
            return '';
        }

        /* Disable WebP for jQuery Plugin for Revolution Background Slider */
        if (false !== strpos($htmlTag, 'rev-slidebg')) {
            return '';
        }

        /* Fix for lazy load part 1 */
        $originImageUrl = $imageUrl;
        if (strpos($htmlTag, 'Magefan_LazyLoad/images/pixel.jpg') === false) {

            $hasLazyLoad = false;
            foreach (['data-original=', 'data-src=', 'data-lazyload='] as $lazyAttribute) {
                if (strpos($htmlTag, $lazyAttribute) !== false) {
                    $p1 = strpos($htmlTag, $lazyAttribute);

                    if ($p1 !== false) {
                        $p1 += strlen($lazyAttribute) + 1;
                        $p2 = strpos($htmlTag, '"', $p1);
                        if ($p2 !== false) {
                            $imageUrl = substr($htmlTag, $p1, $p2 - $p1);
                            $hasLazyLoad = true;
                            break;
                        }
                    }
                }
            }

            if ($hasLazyLoad) {
                foreach (['src='] as $lazyAttribute) {
                    if (strpos($htmlTag, $lazyAttribute) !== false) {
                        $p1 = strpos($htmlTag, $lazyAttribute);

                        if ($p1 !== false) {
                            $p1 += strlen($lazyAttribute) + 1;
                            $p2 = strpos($htmlTag, '"', $p1);
                            if ($p2 !== false) {
                                $originImageUrl = substr($htmlTag, $p1, $p2 - $p1);
                                break;
                            }
                        }
                    }
                }
            }

        }
        /* End Fix */

        $webpUrl = '';
        $webPOriginImageUrl = $imageUrl;

        $extension = explode('.', $imageUrl);
        $extension = strtolower($extension[count($extension) - 1]);

        if ($extension) {
            if ($extension == 'svg') {
                return '';
            }

            if ($extension == 'webp') {
                /* Fix for media galery jpg & webp load when webp is not supported */
                $_webPOriginImageUrl = $this->getOriginWebPUrl->execute($imageUrl);
                if ($_webPOriginImageUrl) {
                    $webPOriginImageUrl = $_webPOriginImageUrl;
                    $htmlTag = str_replace($imageUrl, $webPOriginImageUrl, $htmlTag);
                    $webpUrl = $imageUrl;
                } else {
                    return '';
                }
            }
        }

        if (!$webpUrl) {
            $success = $this->createWebPImage->execute($imageUrl);
            if (!$success) {
                return '';
            }

            $webpUrl = $this->getWebPUrl->execute($imageUrl);
        }

        $htmlTagAttributes = preg_replace(
            '/\s+(?:src|data-src|data-lazyload|data-src-retina|data-original|type|srcset|alt)\s*=\s*(?:"[^"]*"|\'[^\']*\')/i',
            '',
            $htmlTag
        );
        $htmlTagAttributes = trim($htmlTagAttributes, '<img />');
        $htmlTagAttributes = str_replace('owl-lazy', '', $htmlTagAttributes);

        /* If will need to change class="mfwebp", need to check if it does not use somewhere else */
        $html =
            Config::PICTURE_WITH_MFWEBP_CLASS.'
                <source ' . $htmlTagAttributes . ' type="image/webp" srcset="'.$webpUrl.'">

                 '.
            str_replace(['<img ', '<IMG '], '<img data-webpconverted="1" ', $htmlTag)
            .'</picture>';

        /* Fix for lazy load part 2 */
        if ($originImageUrl != $imageUrl) {

            $tmpSrc = 'TMP_SRC';
            $pixelSrc = 'srcset="' . $originImageUrl . '"';

            $html = str_replace($pixelSrc, $tmpSrc, $html);

            $html = preg_replace('#<source\s+([^>]*)(?:srcset="([^"]*)")([^>]*)?>#isU', '<source ' . $pixelSrc .
                ' data-originalset="$2" data-srcset="$2" data-src="$2"  $1 $3/>', $html);

            $html = str_replace($tmpSrc, $pixelSrc, $html);
        }
        /* End Fix */

        return $html;
    }

    /**
     * Parse HTML and if the picture's source tag contains images that can be converted to webp add new source tags with webp to the current picture tag
     * @param string $output
     * @return string
     */
    private function processExistingPictureTags($output)
    {

        if (false === strpos($output, '<picture>')) {
            return $output;
        }

        $pictureTagsMatches = [];

        $regex = '/<picture>(.*?)<\/picture>/smi';

        if (preg_match_all($regex, $output, $pictureTagsMatches, PREG_OFFSET_CAPTURE) === false) {
            return $output;
        }

        $position = 0;

        foreach ($pictureTagsMatches[0] as $picture) {

            $pictureTag = $picture[0];

            if (false !== strpos($pictureTag, Config::PICTURE_WITH_MFWEBP_CLASS)) {
                continue;
            }

            $offset = $picture[1] + $position;
            $sourceMatches = [];
            $newSourceTags = '';
            $imagesToConver = [];

            $regex = '/<source(.*?)>/smi';

            if (preg_match_all($regex, $pictureTag, $sourceMatches, PREG_OFFSET_CAPTURE) === false) {
                continue;
            }

            foreach ($sourceMatches[0] as $source) {

                $newSourceTag = '';
                $src = [];
                $regex = '/set *= *["\']?([^"|^\']*)/smi';

                if (preg_match_all($regex, $source[0], $src, PREG_OFFSET_CAPTURE) === false) {
                    continue;
                }

                foreach ($src[1] as $value) {

                    $srcs = explode(' ', trim($value[0]));

                    if (!is_array($srcs)) {
                        continue;
                    }

                    foreach ($srcs as $key => $value) {
                        if (false !== strpos($value, '.') && strlen($value) > 5) {

                            $imageUrl = $value;

                            if (!$this->createWebPImage->execute($imageUrl)) {
                                continue;
                            }

                            $count = 0;
                            $source[0] = str_replace($value, $this->getWebPUrl->execute($imageUrl), $source[0], $count);
                            $newSourceTag = ($count) ? $source[0] : '';
                        }
                    }
                }

                $newSourceTag = str_replace('<source ', '<source type="image/webp" ', $newSourceTag);
                $newSourceTags .= $newSourceTag;
            }

            $count = 0;
            $newHtmlTag = str_replace('<picture>', '<picture>' . $newSourceTags, $pictureTag, $count);

            if (!$count) {
                continue;
            }

            /* Parse <img> tag inside picture */
            $newHtmlTagContent = str_replace(['<picture>', Config::PICTURE_WITH_MFWEBP_CLASS, '</picture>'], '', $newHtmlTag);
            $newHtmlTagWebPImageContent = $this->execute($newHtmlTagContent);
            $newHtmlTagWebPImageContent = str_replace(['<picture>', Config::PICTURE_WITH_MFWEBP_CLASS, '</picture>'], '', $newHtmlTagWebPImageContent);

            if ($newHtmlTagContent != $newHtmlTagWebPImageContent) {
                $newHtmlTag = str_replace($newHtmlTagContent, $newHtmlTagWebPImageContent, $newHtmlTag);
            }
            /* End parse <img> tag */

            $output = substr_replace($output, $newHtmlTag, $offset, strlen($pictureTag));
            $position = $position + (strlen($newHtmlTag) - strlen($pictureTag));
        }

        return $output;
    }

    /**
     * Replace current background image on webp
     * @param $output
     * @return string|string[]
     */
    private function processBackgroundImages($output)
    {

        if (false === strpos($output, ' background') && false === strpos($output, ';background')) {
            return $output;
        }

        $matches = [];

        $regex = '/style=[\"\']([^\"\']+)?background(-image)?:[ ]?url\([\'"]?(.*?\.(?:png|jpg|jpeg|PNG|JPG|JPEG|gif|GIF))([^"]*)"/mi';

        if (preg_match_all($regex, $output, $matches, PREG_OFFSET_CAPTURE) === false) {
            return $output;
        }

        $elementStyles = [];

        for ($i = 0; $i < count($matches[0]); $i++) {

            $imageUrl = $matches[3][$i][0];

            if ($this->createWebPImage->execute($imageUrl)) {

                $webpUrl = $this->getWebPUrl->execute($imageUrl);
                $elementStyle = $matches[0][$i][0];
                $imgHash = sha1($imageUrl);
                $output = str_replace($elementStyle, ' mf-img-id="' . $imgHash . '"', $output);
                $elementStyle = str_replace('style=', '', $elementStyle);
                $elementStyle = trim($elementStyle, '"');
                $elementStyle = trim($elementStyle, "'");
                $elementStyles[$imgHash]['origin'] = $elementStyle;
                $elementStyles[$imgHash]['webp'] = str_replace($imageUrl, $webpUrl, $elementStyle);
            }
        }

        $style = '<style>';

        foreach ($elementStyles as $imgHash => $elementStyle) {
            $style .= 'body.no-webp [mf-img-id="' . $imgHash .'"]  {'. $elementStyle['origin'] .'}';
            $style .= 'body.webp-supported [mf-img-id="' . $imgHash .'"]  {'. $elementStyle['webp'] .'}';
        }

        $style .= '</style>';

        $output = str_replace('</header>', '</header>' . $style, $output);

        return $output;
    }
}
