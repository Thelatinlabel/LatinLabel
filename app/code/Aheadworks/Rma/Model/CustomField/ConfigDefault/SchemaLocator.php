<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\ConfigDefault;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir\Reader;

/**
 * Class SchemaLocator
 *
 * @package Aheadworks\Rma\Model\CustomField\ConfigDefault
 */
class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    private $schema;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    private $perFileSchema;

    /**
     * @param Reader $moduleReader
     */
    public function __construct(Reader $moduleReader)
    {
        $this->schema = $this->getSchemaPath($moduleReader);
        $this->perFileSchema = $this->schema;
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Get path to pre file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }

    /**
     * Retrieve schema path
     *
     * @param Reader $moduleReader
     * @return string
     */
    private function getSchemaPath($moduleReader)
    {
        return $moduleReader->getModuleDir('etc', 'Aheadworks_Rma') . DIRECTORY_SEPARATOR . 'rma_custom_fields.xsd';
    }
}
