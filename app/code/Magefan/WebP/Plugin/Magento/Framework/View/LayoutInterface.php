<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Plugin\Magento\Framework\View;

use Magento\Framework\View\LayoutInterface as LayoutInterfaceBase;
use Magefan\WebP\Model\Config;
use Magefan\WebP\Api\HtmlParserInterface as HtmlParser;

/**
 * Class LayoutInterface
 */
class LayoutInterface
{
    /**
     * @var HtmlParser
     */
    private $htmlParser;

    /**
     * LayoutInterface constructor.
     * @param Config $config
     * @param HtmlParser $htmlParser
     */
    public function __construct(
        Config $config,
        HtmlParser $htmlParser
    ) {
        $this->config = $config;
        $this->htmlParser = $htmlParser;
    }

    /**
     * @param LayoutInterfaceBase $layout
     * @param $output
     * @return mixed
     */
    public function afterGetOutput(LayoutInterfaceBase $layout, string  $output): string
    {
        if (!$this->config->isEnabled()) {
            return $output;
        }

        $handles = $layout->getUpdate()->getHandles();
        $blackListHandles = [
            'sales_email_order_invoice_items'
        ];

        if (empty($handles) || array_intersect($blackListHandles, $handles)) {
            return $output;
        }

        return $this->htmlParser->execute($output);
    }
}
