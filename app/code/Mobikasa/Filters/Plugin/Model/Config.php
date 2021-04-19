<?php 
namespace Mobikasa\Filters\Plugin\Model;
use Magento\Store\Model\StoreManagerInterface;
class Config
{
    protected $_storeManager;

public function __construct(
    StoreManagerInterface $storeManager
) {
    $this->_storeManager = $storeManager;

}

/**
 * Adding custom options and changing labels
 *
 * @param \Magento\Catalog\Model\Config $catalogConfig
 * @param [] $options
 * @return []
 */
public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
{
    $store = $this->_storeManager->getStore();
    $currencySymbol = $store->getCurrentCurrency()->getCurrencySymbol();

    //Remove specific default sorting options
    unset($options['position']);
    unset($options['name']);
    unset($options['price']);

    $customOption[''] = __('SORT BY');
    //Changing label
    $customOption['position'] = __('Recommended');

    //New sorting options
    $customOption['price_desc'] = __('Price - High To Low');
    $customOption['price_asc'] = __('Price - Low To High');

    //Merge default sorting options with custom options
    $options = array_merge($customOption, $options);
    return $options;
}
}