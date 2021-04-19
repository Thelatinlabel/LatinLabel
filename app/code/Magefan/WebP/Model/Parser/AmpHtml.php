<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\WebP\Model\Parser;

use Magefan\WebP\Api\AmpHtmlParserInterface;
use Magefan\WebP\Api\CreateWebPImageInterface;
use Magefan\WebP\Api\GetWebPUrlInterface;
use Magefan\WebP\Api\GetOriginWebPUrlInterface;

class AmpHtml implements AmpHtmlParserInterface
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
     * Amp Html Parser constructor.
     * @param CreateWebPImageInterface $createWebPImage
     * @param GetWebPUrlInterface $getWebPUrl
     * @param GetOriginWebPUrlInterface $getOriginWebPUrl
     */
    public function __construct(
        CreateWebPImageInterface $createWebPImage,
        GetWebPUrlInterface $getWebPUrl,
        GetOriginWebPUrlInterface $getOriginWebPUrl
    ) {
        $this->createWebPImage = $createWebPImage;
        $this->getWebPUrl = $getWebPUrl;
        $this->getOriginWebPUrl = $getOriginWebPUrl;
    }

    /**
     * @param string $output
     * @return string
     */
    public function execute(string  $output): string
    {
        $matches = [];
        //$regex = '/<([^<]+)\ (src|data-src)=[\"\']([^\"\']+)\.(png|jpg|jpeg|PNG|JPG|JPEG|svg|webp|gif|GIF)([^>]+)>\s+<\/amp-img>/mi';
        $regex = '/<([^<]+)\ (src|data-src)=[\"\']([^\"\']+)\.(png|jpg|jpeg|PNG|JPG|JPEG|svg|webp|gif|GIF)([^>]+)>|>\s+<\/amp-img>/mi';
        if (preg_match_all($regex, $output, $matches, PREG_OFFSET_CAPTURE) === false) {
            return $output;
        }

        $position = 0;

        foreach ($matches[0] as $i => $match) {
            $offset = $match[1] + $position;
            $htmlTag = $matches[0][$i][0];

            $imageUrl = $matches[3][$i][0] . '.' . $matches[4][$i][0];

            $newHtmlTag = $this->getNewHtmlTag($imageUrl, $htmlTag);
            if (!$newHtmlTag) {
                continue;
            }

            $output = substr_replace($output, $newHtmlTag, $offset, strlen($htmlTag));
            $position = $position + (strlen($newHtmlTag) - strlen($htmlTag));
        }

        return $output;
    }

    /**
     * Return amp-image Tag (Html) with original and webp images
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
                $webPOriginImageUrl = $this->getOriginWebPUrl->execute($imageUrl);
                if ($webPOriginImageUrl) {
                    $webPOriginImageUrl = $webPOriginImageUrl;
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
            '/\s+(?:src|data-src|data-lazyload|data-src-retina|data-original|type|srcset)\s*=\s*(?:"[^"]*"|\'[^\']*\')/i',
            '',
            $htmlTag
        );

        $htmlTagAttributes = trim($htmlTagAttributes, '<amp-img />');
        $htmlTagAttributes = str_replace('>', '', $htmlTagAttributes);
        $htmlTagAttributes = str_replace('owl-lazy', '', $htmlTagAttributes);
        $htmlTag = str_replace('<amp-img', '<amp-img fallback ', $htmlTag);

        $html =
            '<amp-img  srcset="'.$webpUrl.'" ' . $htmlTagAttributes . ' data-webpconverted="1">'
                . $htmlTag .
            '</amp-img>';

        return $html;
    }
}
