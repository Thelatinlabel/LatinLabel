<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class GenerateOptions
 */
class CreationOptions implements ArrayInterface
{
    const PAGE_LOAD = 0;
    const CRON = 1;
    const PAGE_LOAD_AND_CRON = 2;
    const MANUAL = 3;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::PAGE_LOAD,  'label' => __('Page Load')],
            ['value' => self::CRON,  'label' => __('Cron')],
            ['value' => self::PAGE_LOAD_AND_CRON,  'label' => __('Page Load & Cron')],
            ['value' => self::MANUAL,  'label' => __('Manually by CLI bin/magento magefan:webp:convert')],
        ];
    }
}
