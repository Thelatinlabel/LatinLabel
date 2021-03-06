<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Plugin\Magento\ConfigurableProduct\Helper;

use Magefan\WebP\Api\CreateWebPImageInterface;
use Magefan\WebP\Api\GetWebPUrlInterface;
use Magefan\WebP\Model\Config;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\ConfigurableProduct\Helper\Data as BaseImage;

/**
 * Convert configurable product images to webp
 */
class Data
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
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Data constructor.
     * @param CreateWebPImageInterface $createWebPImage
     * @param GetWebPUrlInterface $getWebPUrl
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CreateWebPImageInterface $createWebPImage,
        GetWebPUrlInterface $getWebPUrl,
        Config $config,
        CollectionFactory $collectionFactory
    ) {
        $this->createWebPImage = $createWebPImage;
        $this->getWebPUrl = $getWebPUrl;
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param BaseImage $subject
     * @param $images
     * @return \Magento\Framework\Data\Collection
     * @throws \Exception
     */
    public function afterGetGalleryImages(BaseImage $subject, $images)
    {
        if (!$this->config->isEnabled()) {
            return $images;
        }

        if (!$images) {
            return $images;
        }

        $newImages = $this->collectionFactory->create();

        foreach ($images as $image) {
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
}
