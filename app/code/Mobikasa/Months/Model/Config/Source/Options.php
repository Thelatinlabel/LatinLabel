<?php

namespace Mobikasa\Months\Model\Config\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;

/**
 * Custom Attribute Renderer
 *
 * @author      Webkul Core Team <support@webkul.com>
 */
class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @param OptionFactory $optionFactory
     */
    /*public function __construct(OptionFactory $optionFactory)
    {
        $this->optionFactory = $optionFactory;  
        //you can use this if you want to prepare options dynamically  
    }*/

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
    /* your Attribute options list*/
        $this->_options=[ ['label'=>'Month', 'value'=>''],
              ['label'=>'January', 'value'=>'1'],
              ['label'=>'February', 'value'=>'2'],
              ['label'=>'March', 'value'=>'3'],
              ['label'=>'April', 'value'=>'4'],
              ['label'=>'May', 'value'=>'5'],
              ['label'=>'June', 'value'=>'6'],
              ['label'=>'July', 'value'=>'7'],
              ['label'=>'August', 'value'=>'8'],
              ['label'=>'September', 'value'=>'9'],
              ['label'=>'October', 'value'=>'10'],
              ['label'=>'November', 'value'=>'11'],
              ['label'=>'December', 'value'=>'12'],
            ];
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Custom Attribute Options  ' . $attributeCode . ' column',
            ],
        ];
    }
}