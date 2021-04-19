<?php
/**
 * @category    Ubertheme
 * @package     Ubertheme_UbDatamigration
 * @author      Ubertheme.com
 * @copyright   Copyright 2009-2016 Ubertheme
 */
namespace Mobikasa\Newsletters\Helper;
/**
 * Cache helper
 */
class Newsletter extends \Magento\Framework\App\Helper\AbstractHelper {
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    protected $_objectManager = null;
    protected $_categoryFactory;
    protected $_category;
    protected $_productCollectionFactory;
    protected $eavConfig;
    protected $productRepository;
    protected $_subscriber;

    /**
     * Constructor.
     * 
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct
    (   
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Newsletter\Model\Subscriber $subscriber
    )
    {   
        $this->jsonHelper      = $jsonHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->eavConfig = $eavConfig;
        $this->_categoryFactory = $categoryFactory;
        $this->productRepository = $productRepository;
        $this->_subscriber= $subscriber;
    }

    public function checkNewsletter($customerEmail)
    {
      $checkSubscriber = $this->_subscriber->loadByEmail($customerEmail);
      if ($checkSubscriber->isSubscribed()) {
          return 1;
      } else {
          return 0;
      }
    }
}
