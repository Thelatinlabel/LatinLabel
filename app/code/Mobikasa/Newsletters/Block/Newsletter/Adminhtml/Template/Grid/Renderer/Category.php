<?php
namespace Mobikasa\Newsletters\Block\Newsletter\Adminhtml\Template\Grid\Renderer;
use Magento\Framework\DataObject;
class Category extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
    	if($row->getData('type')==1){
            return ($row->getData('category')!=''?$row->getData('category'):'----');
        }else{
            return ($row->getData('category')!=''?$row->getData('category'):'----');
        }
    }
}