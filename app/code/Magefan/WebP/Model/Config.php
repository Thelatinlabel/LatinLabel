<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{

    const XML_PATH_EXTENSION_ENABLED                    = 'mfwebp/general/enabled';
    const XML_PATH_EXTENSION_IMAGE_QUALITY              = 'mfwebp/general/quality';
    const XML_PATH_EXTENSION_MAGEFAN_CONVERSION_SERVICE = 'mfwebp/general/magefan_conversion_service';
    const XML_PATH_EXTENSION_SKIP_MEDIA_FOLDERS         = 'mfwebp/general/skip_media_folders';
    const XML_PATH_EXTENSION_SKIP_STATIC_FOLDERS        = 'mfwebp/general/skip_static_folders';
    const XML_PATH_EXTENSION_WHEN_GENERATE_WEBP         = 'mfwebp/general/creation_options';
    const XML_PATH_EXTENSION_CONVERT_EXISTS_PICTURE     = 'mfwebp/advanced_settings/convert_exists_picture';
    const XML_PATH_EXTENSION_CONVERT_BACKGROUND_IMAGES  = 'mfwebp/advanced_settings/convert_background_images';

    const PICTURE_WITH_MFWEBP_CLASS                 = '<picture class="mfwebp">';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve true if webp module is enabled
     * @param null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED,
            $storeId
        );
    }

    /**
     * Retrieve store config value
     * @param string $path
     * @param null $storeId
     * @return mixed
     */
    private function getConfig(string $path, ?string $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return quality for convert
     * @param null $storeId
     * @return int
     */
    public function getQuality(?int $storeId = null): int
    {
        return (int) $this->getConfig(
            self::XML_PATH_EXTENSION_IMAGE_QUALITY,
            $storeId
        );
    }

    /**
     * Return quality for convert
     * @param null $storeId
     * @return int
     */
    public function useMagefanConversionService(?int $storeId = null): int
    {
        return (int) $this->getConfig(
            self::XML_PATH_EXTENSION_MAGEFAN_CONVERSION_SERVICE,
            $storeId
        );
    }

    /**
     * Return list of folders that shouldn't be converted
     * @param null $storeId
     * @return array
     */
    public function getSkipFolders(string $folderType, ?int $storeId = null): array
    {
        $skipFolders = (string) $this->getConfig($folderType, $storeId);
        $skipFolders = str_replace("\r", "\n", trim($skipFolders));
        return explode("\n", $skipFolders);
    }

    /**
     * @return mixed
     */
    public function getGenerationOption()
    {
        return $this->getConfig(self::XML_PATH_EXTENSION_WHEN_GENERATE_WEBP);
    }

    /**
     * Retrieve true if option is enabled
     * @param null $storeId
     * @return bool
     */
    public function convertExistingPictureTag(?int $storeId = null): bool
    {
        return (bool) $this->getConfig(
            self::XML_PATH_EXTENSION_CONVERT_EXISTS_PICTURE,
            $storeId
        );
    }

    /**
     * Retrieve true if option is enabled
     * @param null $storeId
     * @return bool
     */
    public function convertBackgroundImages(?int $storeId = null): bool
    {
        return (bool) $this->getConfig(
            self::XML_PATH_EXTENSION_CONVERT_BACKGROUND_IMAGES,
            $storeId
        );
    }
}
