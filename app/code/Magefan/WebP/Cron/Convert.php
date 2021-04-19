<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Cron;

use Magefan\WebP\Model\Filesystem\PubFolder;
use Magefan\WebP\Model\Config\Source\CreationOptions;
use Magefan\WebP\Model\Config;

/**
 * Class GenerateWebPByCron
 */
class Convert
{
    /**
     * @var PubFolder
     */
    private $pubFolder;

    /**
     * GenerateWebPByCron constructor.
     * @param PubFolder $pubFolder
     */
    public function __construct(
        PubFolder $pubFolder,
        Config $config
    ) {
        $this->pubFolder = $pubFolder;
        $this->config = $config;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!in_array(
            $this->config->getGenerationOption(),
            [
                CreationOptions::CRON,
                CreationOptions::PAGE_LOAD_AND_CRON
            ]
        )
        ) {
            return;
        }

        $this->pubFolder->convertFiles($this->pubFolder->getFilesFromFolder(), CreationOptions::CRON);
    }
}
