<?php

namespace Mobikasa\Days\Model\Config\Source;

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
        $this->_options=[ ['label'=>'Day', 'value'=>''],
              ['label'=>'1', 'value'=>'1'],
              ['label'=>'2', 'value'=>'2'],
              ['label'=>'3', 'value'=>'3'],
              ['label'=>'4', 'value'=>'4'],
              ['label'=>'5', 'value'=>'5'],
              ['label'=>'6', 'value'=>'6'],
              ['label'=>'7', 'value'=>'7'],
              ['label'=>'8', 'value'=>'8'],
              ['label'=>'9', 'value'=>'9'],
              ['label'=>'10', 'value'=>'10'],
              ['label'=>'11', 'value'=>'11'],
              ['label'=>'12', 'value'=>'12'],
              ['label'=>'13', 'value'=>'13'],
              ['label'=>'14', 'value'=>'14'],
              ['label'=>'15', 'value'=>'15'],
              ['label'=>'16', 'value'=>'16'],
              ['label'=>'17', 'value'=>'17'],
              ['label'=>'18', 'value'=>'18'],
              ['label'=>'19', 'value'=>'19'],
              ['label'=>'20', 'value'=>'20'],
              ['label'=>'21', 'value'=>'21'],
              ['label'=>'22', 'value'=>'22'],
              ['label'=>'23', 'value'=>'23'],
              ['label'=>'24', 'value'=>'24'],
              ['label'=>'25', 'value'=>'25'],
              ['label'=>'26', 'value'=>'26'],
              ['label'=>'27', 'value'=>'27'],
              ['label'=>'28', 'value'=>'28'],
              ['label'=>'29', 'value'=>'29'],
              ['label'=>'30', 'value'=>'30'],
              ['label'=>'31', 'value'=>'31']
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