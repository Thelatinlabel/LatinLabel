<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Controller\Adminhtml\WebP;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magefan\WebP\Model\Config;

/**
 * Remove all existing webp images copies
 */
class Clean extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magefan_WebP::config';

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var WriteInterface
     */
    private $deleteDirectory;

    /**
     * Clean constructor.
     * @param Context $context
     * @param Filesystem $fileSystem
     * @param Config $config
     */
    public function __construct(
        Context $context,
        Filesystem $fileSystem,
        Config $config
    ) {
        parent::__construct($context);
        $this->fileSystem = $fileSystem;
        $this->config = $config;
    }

    /**
     * Clean Generated WebP images
     */
    public function execute()
    {
        if ($this->config->isEnabled()) {
            $this->directory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
            if ($this->directory->delete('mf_webp')) {
                $this->messageManager->addSuccessMessage(
                    __('Cache has been cleaned successfully')
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __("Can't delete folder pub/media/mf_webp, please try to remove this folder manually")
                );
            }
        } else {
            $this->messageManager->addErrorMessage(
                __(strrev('PbeW > snoisnetxE nafegaM > noitarugifnoC > serotS ni noisnetxe eht elbane esaelP'))
            );
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
