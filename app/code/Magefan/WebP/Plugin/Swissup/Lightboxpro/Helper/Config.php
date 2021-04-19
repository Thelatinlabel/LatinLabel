<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\WebP\Plugin\Swissup\Lightboxpro\Helper;

use Magefan\WebP\Api\CreateWebPImageInterface;
use Magefan\WebP\Api\GetWebPUrlInterface;
use Magefan\WebP\Model\Config as WebPConfig;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;

class Config
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
     * @var WebPConfig
     */
    private $config;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Config constructor.
     * @param CreateWebPImageInterface $createWebPImage
     * @param GetWebPUrlInterface $getWebPUrl
     * @param WebPConfig $config
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CreateWebPImageInterface $createWebPImage,
        GetWebPUrlInterface $getWebPUrl,
        WebPConfig $config,
        CollectionFactory $collectionFactory
    ) {
        $this->createWebPImage = $createWebPImage;
        $this->getWebPUrl = $getWebPUrl;
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param mixed $subject
     * @param $images
     * @return mixed
     */
    public function afterGetGalleryImages($subject, $images)
    {
        if (!$this->config->isEnabled()) {
            return $images;
        }

        $newImages = $this->collectionFactory->create();

        foreach ($images as $image) {

            if ($this->checkIfWebPAlreadyExist($image)) {
                return $images;
            }

            $newImages->addItem(
                $this->convertToWebp($image)
            );
        }

        return $newImages;
    }

    /**
     * Replace original image on webp image
     * @param DataObject $image
     * @return DataObject
     */
    private function convertToWebp(DataObject $image)
    {
        $imageTypes = ['small_image_url', 'medium_image_url', 'large_image_url'];
        foreach ($imageTypes as $imageType) {
            $imageUrl = $image->getData($imageType);
            if ($this->createWebPImage->execute($imageUrl)) {
                $webpUrl = $this->getWebPUrl->execute($imageUrl);
                $image->setData($imageType, $webpUrl);
            }
        }

        return $image;
    }

    /**
     * @param DataObject $image
     * @return bool
     */
    private function checkIfWebPAlreadyExist(DataObject $image)
    {
        $imageTypes = ['small_image_url', 'medium_image_url', 'large_image_url'];

        foreach ($imageTypes as $imageType) {
            if (false !== strpos($image->getData($imageType), '.webp')) {
                return true;
            }
        }

        return false;
    }
}
