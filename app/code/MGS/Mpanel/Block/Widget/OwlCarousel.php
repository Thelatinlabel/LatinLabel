<?php
namespace MGS\Mpanel\Block\Widget;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Filesystem\DirectoryList;

class OwlCarousel extends Template
{
	protected $_filesystem;
	
	protected $_file;
	
	public function __construct(
		Template\Context $context,
		\Magento\Framework\Filesystem\Driver\File $file,
		array $data = []
	){
        parent::__construct($context, $data);
		$this->_filesystem = $context->getFilesystem();
		$this->_file = $file;
    }
	
	public function getImages(){
		$result = [];
		if($this->hasData('images') && ($this->getData('images')!='')){
			$images = $this->getData('images');
			$images = explode(',',$images);
			if(count($images)>0){
				foreach($images as $image){
					$filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('wysiwyg/slider/') . $image;
					if ($this->_file->isExists($filePath))  {
						$result[] = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).'wysiwyg/slider/'.$image;
					}
				}
			}
		}
		return $result;
	}
	
	public function getLinks(){ 
		$links = [];
		if($this->hasData('links') && ($this->getData('links')!='')){
			$links = $this->getData('links');
			$links = explode(',',$links);
		}
		return $links;
	}
	public function getAllImages(){ 
		$result = [];
		if($this->hasData('all_images') && ($this->getData('all_images')!='')){
			$images = $this->getData('all_images');
			$images = explode(',',$images);
			if(count($images)>0){
				foreach($images as $image){
					$filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('wysiwyg/slider/') . $image;
					if ($this->_file->isExists($filePath))  {
						$result[] = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).'wysiwyg/slider/'.$image;
					}
				}
			}
		}
		return $result;
	}
	
	public function getBannerLinks(){ 
		$links = [];
		$newLink = [];
		if($this->hasData('links') && ($this->getData('links')!='')){
			$links = $this->getData('links');
			$links = explode(',',$links);
		}
		foreach($links as $key => $value) {
			if(empty($value)){
				unset($links[$key]); 	
			}else{
				$newLink[] = $value;
			}
        	
		}        
    
		
		return $newLink;
	}

	public function getAnimateSlider()
	{
		$animated = $this->getData('effect');
		$result = [];
		switch ($animated) {
            case 1:
                $result = array('out' => 'fadeOut', 'in' => 'fadeIn');
                break;
            case 2:
                $result = array('out' => 'fadeOutDown', 'in' => 'fadeInDown');
                break;
            case 3:
                $result = array('out' => 'fadeOut', 'in' => 'slideInDown');
                break;
            case 4:
                $result = array('out' => 'slideOutRight', 'in' => 'slideInLeft');
                break;
        }
		return $result;
	}
	
	public function getAutoSpeed()
	{
		$autoSpeed = 3000;
		if($this->getData('speed') != ""){
			$autoSpeed = $this->getData('speed');
		}
		return $autoSpeed;
	}
	
	public function getAutoPlay()
	{
		if($this->getData('autoplay') != "" && $this->getData('autoplay') == 1){
			return 'true';
		}
		return 'false';
	}
	
	
	public function getControlNav()
	{
		if($this->getData('navigation') != "" && $this->getData('navigation') == 1){
			return 'true';
		}
		return 'false';
	}
	
	public function getControlDots()
	{
		if($this->getData('pagination') != "" && $this->getData('pagination') == 1){
			return 'true';
		}
		return 'false';
	}
	
	public function getRightToLeft()
	{
		if($this->getData('rtl') != "" && $this->getData('rtl') == 1){
			return 'true';
		}
		return 'false';
	}
	
	public function getLoop()
	{
		if($this->getData('loop') != "" && $this->getData('loop') == 1){
			return 'true';
		}
		return 'false';
	}
	
	public function checkFull()
	{
		if($this->getData('fullscreen') != "" && $this->getData('fullscreen') == 1){
			return true;
		}
		return false;
	}
}