<?php
namespace Mobikasa\Newsletters\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface as Request;

class Updatenewsletterajax implements ObserverInterface
{
    
    public $request;
    public $subscriber;

    public function __construct(
        Request $request,
        \Magento\Newsletter\Model\Subscriber $subscriber
        
    ) {
        $this->request = $request;
        $this->subscriber = $subscriber;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        $postData = $this->request->getPost();
        $this->request->getParam('is_subscribed');
        
        $email = $observer->getEvent()->getEmail();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        $customerId = $customerSession->getCustomer()->getId();

        if($this->request->getParam('is_subscribed') == 1){
            $this->subscriber->subscribeCustomerById($customerId);  
        }else{
            $this->subscriber->unsubscribeCustomerById($customerId);
        }
    }
}