<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Model\Serialize\Factory;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Class UnserializeResolver
 *
 * @package Aheadworks\Rma\Model
 */
class UnserializeResolver
{
    /**
     * @var Factory;
     */
    private $factory;

    /**
     * @var Serialize
     */
    private $phpSerializer;

    /**
     * @param Factory $factory
     * @param Serialize $phpSerializer
     */
    public function __construct(
        Factory $factory,
        Serialize $phpSerializer
    ) {
        $this->factory = $factory;
        $this->phpSerializer = $phpSerializer;
    }

    /**
     * Unserialize the given string
     *
     * @param string $string
     * @return string|int|float|bool|array|null
     * @throws \InvalidArgumentException
     */
    public function unserialize($string)
    {
        $result = $this->unserializeString($string);
        return $result === false ? $this->jsonDecodeString($string) : $result;
    }

    /**
     * Unserialize string with unserialize method
     *
     * @param $string
     * @return array|bool
     */
    private function unserializeString($string)
    {
        try {
            $result = $this->phpSerializer->unserialize($string);
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * Unserialize string with json_decode method
     *
     * @param $string
     * @return array
     */
    private function jsonDecodeString($string)
    {
        $serializer = $this->factory->create();
        return $serializer->unserialize($string);
    }
}
