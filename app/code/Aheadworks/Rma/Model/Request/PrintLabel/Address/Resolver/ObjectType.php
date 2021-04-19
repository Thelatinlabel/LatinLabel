<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Address\Resolver;

use Aheadworks\Rma\Api\Data\RequestPrintLabelInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Class ObjectType
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Address\Resolver
 */
class ObjectType
{
    /**
     * Retrieve class for getting address data
     *
     * @param mixed $address
     * @return string|null
     */
    public function getClass($address)
    {
        $dataObjectType = null;
        switch (true) {
            case $address instanceof AddressInterface:
                $dataObjectType = AddressInterface::class;
                break;
            case $address instanceof OrderAddressInterface:
                $dataObjectType = OrderAddressInterface::class;
                break;
            case $address instanceof RequestPrintLabelInterface:
                $dataObjectType = RequestPrintLabelInterface::class;
                break;
        }

        return $dataObjectType;
    }
}
