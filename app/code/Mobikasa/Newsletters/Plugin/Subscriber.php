<?php
namespace Mobikasa\Newsletters\Plugin;

use Magento\Framework\App\Request\Http;

class Subscriber {
    protected $request;
    public function __construct(Http $request){
        $this->request = $request;
    }

    public function aroundSubscribe($subject, \Closure $proceed, $email) { 

        if ($this->request->isPost() && $this->request->getPost('category')) { 

            $category = $this->request->getPost('category');
            

            $subject->setCategory($category);
            
            $result = $proceed($email);

            try {
                $subject->save();
            }catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
            return $result;
        }
        
    }
}