<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Plugin\MagicToolbox\MagicZoomPlus\Classes;

use Magefan\WebP\Api\CreateWebPImageInterface;
use Magefan\WebP\Api\GetWebPUrlInterface;
use Magefan\WebP\Model\Config;
use Magento\Framework\View\Element\Context;

/**
 * Class MagicZoomPlusModuleCoreClass
 */
class MagicZoomPlusModuleCoreClass
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
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * MagicZoomPlusModuleCoreClass constructor.
     * @param CreateWebPImageInterface $createWebPImage
     * @param GetWebPUrlInterface $getWebPUrl
     * @param Config $config
     * @param Context $context
     */
    public function __construct(
        CreateWebPImageInterface $createWebPImage,
        GetWebPUrlInterface $getWebPUrl,
        Config $config,
        Context $context
    ) {
        $this->createWebPImage = $createWebPImage;
        $this->getWebPUrl = $getWebPUrl;
        $this->config = $config;
        $this->assetRepo = $context->getAssetRepository();
    }

    /**
     * Convert image url params to webp
     * @param mixed $subject
     * @param callable $proceed
     * @param array $params
     * @return array
     */
    public function aroundGetSelectorTemplate($subject, callable $proceed, $params)
    {
        return $this->convertHtml($proceed, $params);
    }

    /**
     * Convert image url params to webp
     * @param mixed $subject
     * @param callable $proceed
     * @param array $params
     * @return array
     */
    public function aroundGetMainTemplate($subject, callable $proceed, $params)
    {
        return $this->convertHtml($proceed, $params);
    }

    /**
     * Convert image url params to webp
     * @param callable $proceed
     * @param array $params
     * @return array
     */
    private function convertHtml(callable $proceed, $params)
    {
        if (!$this->config->isEnabled()) {
            return $proceed($params);
        }

        $origImg = !empty($params['img']) ? $params['img'] : '';

        $img = '';
        $id = '';

        /* Convert params to webp */
        $keys = ['img', 'thumb', 'thumb2x', 'medium', 'medium2x'];
        foreach ($keys as $key) {
            if (!empty($params[$key])) {
                $url = $params[$key];
                if ($this->createWebPImage->execute($url)) {
                    $url = $this->getWebPUrl->execute($url);
                    $params[$key] = $url;
                }
            }
        }
        /* End convert */

        extract($params);
        if (empty($id)) {
            $id = hash('md5', $img, false);
        }

        $html = $proceed($params);

        if ($html) {
            if ($origImg != $img) {
                $html = str_replace('src=', ' src="' . $this->assetRepo->getUrlWithParams('Magefan_WebP::images/p.jpg', []) . '" webpimg=', $html);
                $script = '<script>MagefanWebPMagicToolboxContainerFix();</script>';
                if (false !== strpos($html, '</a>')) {
                    $html = str_replace('</a>', $script . '</a>', $html);
                } else {
                    $html .= $script;
                }
            }

            $html = str_replace('src=', ' data-webpconverted src=', $html);
        }

        return $html;
    }
}
