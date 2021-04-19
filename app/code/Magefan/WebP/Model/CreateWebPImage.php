<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Model;

use Magento\Framework\Filesystem\DirectoryList;
use WebPConvert\WebPConvert;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magefan\WebP\Model\Converter\MagefanConversionService;
use Magefan\WebP\Model\Converter\Gifsicle;
use Magefan\WebP\Model\Config\Source\CreationOptions;
use Magefan\WebP\Model\Config;

use Magefan\WebP\Api\GetWebPUrlInterface;
use Magefan\WebP\Api\CreateWebPImageInterface;

/**
 * Methods to convert images to WebP
 */
class CreateWebPImage implements CreateWebPImageInterface
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var string
     */
    private $pubFolder = '';

    /**
     * @var string
     */
    private $rootFolder = '';

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * @var string
     */
    private $mediaBaseUrl;

    /**
     * @var string
     */
    private $staticBaseUrl;

    /**
     * @var bool
     */
    private $stopConverting;

    /**
     * @var MagefanConversionService
     */
    private $magefanConversionService;

    /**
     * @var Gifsicle
     */
    private $gifsicle;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var GetWebPUrlInterface
     */
    private $getWebPUrl;

    /**
     * CreateWebPImage constructor.
     * @param DirectoryList $directoryList
     * @param File $fileDriver
     * @param StoreManagerInterface $storeManager
     * @param AssetRepository $assetRepository
     * @param MagefanConversionService $magefanConversionService
     * @param Gifsicle $gifsicle
     * @param \Magefan\WebP\Model\Config $config
     * @param GetWebPUrlInterface $getWebPUrl
     */
    public function __construct(
        DirectoryList $directoryList,
        File $fileDriver,
        StoreManagerInterface $storeManager,
        AssetRepository $assetRepository,
        MagefanConversionService $magefanConversionService,
        Gifsicle $gifsicle,
        Config $config,
        GetWebPUrlInterface $getWebPUrl
    ) {
        $this->directoryList = $directoryList;
        $this->rootFolder = $this->directoryList->getRoot();
        $this->pubFolder = $this->rootFolder . '/pub';
        $this->fileDriver = $fileDriver;
        $this->storeManager = $storeManager;
        $this->assetRepository = $assetRepository;
        $this->magefanConversionService = $magefanConversionService;
        $this->gifsicle = $gifsicle;
        $this->config = $config;
        $this->getWebPUrl = $getWebPUrl;
        $this->stopConverting = false;
    }

    /**
     * Convert image to WebP using its URL. Return true if converted successfully or WebP image already exists.
     * @param string $imageUrl
     * @param int|null $mode
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(string $imageUrl, ?int $mode = null): bool
    {
        if (null === $mode) {
            $mode = CreationOptions::PAGE_LOAD;
        }

        $image = ($mode == CreationOptions::PAGE_LOAD) ? $this->getPathFromUrl($imageUrl) : $imageUrl;

        if ($this->needSkip($image)) {
            return false;
        }

        if (!$this->fileDriver->isExists($image)) {
            return false;
        }

        $webpImageUrl = $this->getWebPUrl->execute($imageUrl);
        $webpImage = $this->getPathFromUrl($webpImageUrl);

        if ($this->fileDriver->isExists($webpImage) && !$this->isNewerThan($image, $webpImage)) {
            return true;
        }

        if ($this->stopConverting) {
            return false;
        }

        if ($mode == CreationOptions::PAGE_LOAD
            && in_array(
                $this->config->getGenerationOption(),
                [CreationOptions::CRON, CreationOptions::MANUAL]
            )) {
            return false;
        }

        $quality = $this->config->getQuality();

        try {
            if ($this->config->useMagefanConversionService()) {
                /* Use Magefan WebP Conversion Service */
                /* To enable need to
                    1. add to env.php

                    'system' => [
                        'default' => [
                            'mfwebp' => [
                                'general' => [
                                    'magefan_conversion_service' => '1'
                                ]
                            ]
                        ]
                    ]

                    2. Contact Magefan to enable Conversion Service for your account
                    3. Run bin/magento app:config:import
                */
                return $this->magefanConversionService->convert($image, $webpImage, $quality);
            } else {
                if (preg_match('/\.(gif|GIF)$/i', $image)) {
                    return $this->gifsicle->convert($image, $webpImage, $quality);
                } else {
                    WebPConvert::convert(
                        $image,
                        $webpImage,
                        [
                            'quality' => $quality,
                            'max-quality' => $quality,
                            'converters' => ['cwebp', 'gd', 'imagick', 'wpc', 'ewww'],
                            //'size-in-percentage' => 90
                        ]
                    );

                    return true;
                }
            }
        } catch (\Exception $e) {
            if ($this->config->useMagefanConversionService()) {
                $this->stopConverting = true;
            }
        }

        return false;
    }

    /**
     * Check if image path contain skipped folder
     * @param string $image
     * @return bool
     */
    private function needSkip(string $image): bool
    {
        /**
         * Check XML_PATH_EXTENSION_SKIP_MEDIA_FOLDERS on folder and image to skip
         */
        foreach ($this->config->getSkipFolders(Config::XML_PATH_EXTENSION_SKIP_MEDIA_FOLDERS) as $folder) {

            if ($this->endsWith($image, trim($folder))) {
                return true;
            }

            $pathToFolder = $this->pubFolder . '/' . trim(trim($folder), '/') . '/';

            if (strpos($image, $pathToFolder) === 0) {
                return true;
            }
        }

        /**
         * Check XML_PATH_EXTENSION_SKIP_STATIC_FOLDERS on folder to skip
         */
        if (strpos($image, $this->pubFolder . '/static/') === 0) {
            foreach ($this->config->getSkipFolders(Config::XML_PATH_EXTENSION_SKIP_STATIC_FOLDERS) as $folder) {
                $pathToFolder =  '/' . trim(trim($folder), '/') . '/';

                $pos = strpos($image, $pathToFolder);
                if (false !== $pos && $pos > 0) {
                    return true;
                }
            }
        }

        /**
         * Check XML_PATH_EXTENSION_SKIP_STATIC_FOLDERS on images to skip
         */
        foreach ($this->config->getSkipFolders(Config::XML_PATH_EXTENSION_SKIP_STATIC_FOLDERS) as $folder) {

            if ($this->endsWith($image, trim($folder))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return true if $string ends with $endString
     * @param string $string
     * @param string $endString
     * @return bool
     */
    private function endsWith(string $string, string $endString): bool
    {
        $len = strlen($endString);
        if ($len < 3) {
            return false;
        }
        return (bool) (substr($string, -$len) === $endString);
    }

    /**
     * Return image location on server
     * @param string $image
     * @return string
     */
    private function getPathFromUrl(string $imageUrl): string
    {
        $imageUrl = trim($imageUrl);

        $mediaBaseUrl = $this->getMediaBaseUrl();
        $staticBaseUrl = $this->getStaticBaseUrl();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        if (strlen($imageUrl) > 1
            && $imageUrl[0] == '/'
            && $imageUrl[1] != '/'
            && false === strpos($imageUrl, $mediaBaseUrl)
            && false === strpos($imageUrl, $staticBaseUrl)
        ) {
            /* Case when url defined without donain e.g. "/pub/media/wysiwyg/article_images/blue_bird.jpg" */
            $rootFolder = (strpos($imageUrl, '/pub/') === 0) ? $this->rootFolder : $this->pubFolder;
            $imagePath = $rootFolder . $imageUrl;
        } else {

            $imagePath = str_replace(
                [
                    $mediaBaseUrl,
                    $staticBaseUrl,
                    $baseUrl . 'pub/media/',
                    $baseUrl . 'pub/static/',
                ],
                [
                    $this->pubFolder . '/media/',
                    $this->pubFolder . '/static/',
                    $this->pubFolder . '/media/',
                    $this->pubFolder . '/static/',
                ],
                $imageUrl
            );
        }

        return $imagePath;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getMediaBaseUrl(): string
    {
        if (null === $this->mediaBaseUrl) {
            $this->mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );
        }

        return $this->mediaBaseUrl;
    }

    /**
     * @return string
     */
    private function getStaticBaseUrl(): string
    {
        if (null === $this->staticBaseUrl) {
            $staticBaseUrl = $this->assetRepository->getUrl('');
            $staticBaseUrl = explode('/', $staticBaseUrl);
            $staticBaseUrl = array_slice($staticBaseUrl, 0, count($staticBaseUrl) - 4);
            $staticBaseUrl = implode('/', $staticBaseUrl) . '/';

            $this->staticBaseUrl = $staticBaseUrl;
        }

        return $this->staticBaseUrl;
    }

    /**
     * Return true if image1 newer then image2
     * @param string $image
     * @param string $webpImage
     * @return bool
     */
    private function isNewerThan(string $image1, string $image2): bool
    {
        $image1ModificationTime = $this->fileDriver->stat($image1)['mtime'];
        if ($image1ModificationTime === 0) {
            return false;
        }

        $image2ModificationTime = $this->fileDriver->stat($image2)['mtime'];

        return (bool) ($image1ModificationTime > $image2ModificationTime);
    }
}
