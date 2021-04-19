<?php
namespace MyModule\CheckoutSummary\Plugin\Checkout\Model;
 
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ProductRepository as ProductRepository;
 
class ConfigProviderPlugin extends \Magento\Framework\Model\AbstractModel
{
    protected $checkoutSession;
 
    protected $_productRepository;
 
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductRepository $productRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->_productRepository = $productRepository;
    }
 
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject, 
        array $result
    ) {
        $items = $result['totalsData']['items'];
        foreach ($items as $index => $item) {
            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
            $product = $this->_productRepository->getById($quoteItem->getProduct()->getId());
            $result['quoteItemData'][$index]['brand'] = $product->getResource()->getAttribute('mgs_brand')->getFrontend()->getValue($product);
        }
        return $result;
    }
}