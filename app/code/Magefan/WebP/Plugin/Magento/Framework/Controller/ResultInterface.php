<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\WebP\Plugin\Magento\Framework\Controller;

use Magefan\WebP\Api\AmpHtmlParserInterface as AmpHtmlParser;
use Magefan\WebP\Model\Config;
use Magento\Framework\App\ResponseInterface;

class ResultInterface
{

    /**
     * ResultInterface constructor.
     * @param AmpHtmlParser $ampHtmlParser
     * @param Config $config
     */
    public function __construct(
        AmpHtmlParser $ampHtmlParser,
        Config $config
    ) {
        $this->ampHtmlParser = $ampHtmlParser;
        $this->config = $config;
    }

    /**
     * @param \Magento\Framework\Controller\ResultInterface $subject
     * @param \Magento\Framework\Controller\ResultInterface $result
     * @param ResponseInterface $response
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function afterRenderResult(
        \Magento\Framework\Controller\ResultInterface $subject,
        \Magento\Framework\Controller\ResultInterface $result,
        ResponseInterface $response
    ) {
        $html = $response->getBody();
        if ($this->config->isEnabled() && false !== strpos($html, 'amp-page-wrapper')) {
            $response->setBody($this->ampHtmlParser->execute($html));
        }
        return $result;
    }
}
