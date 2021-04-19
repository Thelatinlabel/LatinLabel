<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor\CustomerInfoProcessor;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Aheadworks\Rma\Api\Data\RequestPrintLabelInterface;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Resolver\ObjectType;

/**
 * Class AddressRenderer
 *
 * @package Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor\CustomerInfoProcessor
 */
class AddressRenderer
{
    /**
     * @var AddressConfig
     */
    private $addressConfig;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @var ObjectType
     */
    private $objectTypeResolver;

    /**
     * @param AddressConfig $addressConfig
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param ObjectType $objectTypeResolver
     */
    public function __construct(
        AddressConfig $addressConfig,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        ObjectType $objectTypeResolver
    ) {
        $this->addressConfig = $addressConfig;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->objectTypeResolver = $objectTypeResolver;
    }

    /**
     * Render customer address
     *
     * @param AddressInterface|OrderAddressInterface $address
     *
     * @return string
     */
    public function render($address)
    {
        $formatType = $this->addressConfig->getFormatByCode('html');
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }

        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $formatType->getRenderer();
        $flatAddressArray = $this->convertAddressToArray($address);

        return empty($flatAddressArray) ? '' : $renderer->renderArray($flatAddressArray);
    }

    /**
     * Convert address to flat array
     *
     * @param AddressInterface|OrderAddressInterface|RequestPrintLabelInterface $address
     * @return array
     */
    private function convertAddressToArray($address)
    {
        $dataObjectType = $this->objectTypeResolver->getClass($address);

        if (!empty($dataObjectType)) {
            $flatAddressArray = $this->extensibleDataObjectConverter->toFlatArray(
                $address,
                [],
                $dataObjectType
            );

            return $this->prepareAddressData($flatAddressArray, $address);
        }

        return [];
    }

    /**
     * Prepare address data
     *
     * @param array $flatAddressArray
     * @param AddressInterface|OrderAddressInterface $address
     * @return mixed
     */
    private function prepareAddressData($flatAddressArray, $address)
    {
        $street = $address->getStreet();
        if (!empty($street) && is_array($street)) {
            // Unset flat street data
            $streetKeys = array_keys($street);
            foreach ($streetKeys as $key) {
                unset($flatAddressArray[$key]);
            }
            //Restore street as an array
            $flatAddressArray[AddressInterface::STREET] = $street;
        }

        return $flatAddressArray;
    }
}
