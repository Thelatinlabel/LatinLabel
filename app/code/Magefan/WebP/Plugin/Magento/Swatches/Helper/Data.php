<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Plugin\Magento\Swatches\Helper;

use Magefan\WebP\Api\CreateWebPImageInterface;
use Magefan\WebP\Api\GetWebPUrlInterface;
use Magefan\WebP\Model\Config;
use Magento\Swatches\Helper\Data as BaseData;

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
     * Data constructor.
     * @param CreateWebPImageInterface $createWebPImage
     * @param GetWebPUrlInterface $getWebPUrl
     * @param Config $config
     */
    public function __construct(
        CreateWebPImageInterface $createWebPImage,
        GetWebPUrlInterface $getWebPUrl,
        Config $config
    ) {
        $this->createWebPImage = $createWebPImage;
        $this->getWebPUrl = $getWebPUrl;
        $this->config = $config;
    }

    /**
     * @param \Magento\Swatches\Helper\Data $subject
     * @param $result
     * @return mixed
     */
    public function afterGetProductMediaGallery(BaseData $subject, $result)
    {
        if (!$this->config->isEnabled()) {
            return $result;
        }

        $imageTypes = ['large', 'medium', 'small'];
        foreach ($imageTypes as $imageType) {
            if (isset($result[$imageType])) {
                $result[$imageType]  = $this->convertToWebp($result[$imageType]);
            }
        }

        if (isset($result['gallery']) && is_array($result['gallery'])) {
            foreach ($result['gallery'] as $key => $value) {
                foreach ($imageTypes as $imageType) {
                    if (isset($result[$imageType])) {
                        $result['gallery'][$key][$imageType] = $this->convertToWebp(
                            $result['gallery'][$key][$imageType]
                        );
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Replace original image on webp image
     * @param DataObject $image
     * @return DataObject
     */
    private function convertToWebp(string $image): string
    {
        return $this->createWebPImage->execute($image) ? $this->getWebPUrl->execute($image) : $image;
    }
}
