<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Class Website
 *
 * @package Aheadworks\Rma\Model\Source
 */
class Website implements ArrayInterface
{
    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @param SystemStore $systemStore
     */
    public function __construct(
        SystemStore $systemStore
    ) {
        $this->systemStore = $systemStore;
    }

    /**
     * Return array of websites
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->systemStore->getWebsiteValuesForForm();
    }
}
