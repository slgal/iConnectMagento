<?php
/**
 * Product model
 *
 * @category   InspireSmart
 * @package    InspireSmart_IConnectSync
 * @author     Viacheslav Galanov (vgalanov@inspiresmart.com)
 */
class InspireSmart_IConnectSync_Model_Product extends Mage_Core_Model_Abstract {
	
	const ICONNECT_GET_PRODUCTS_API =   "api/getProductServicesByCompany/%d?version=%d&token=%s&type=false&sellOnline=true";
	
	protected function _construct()
    {
       $this->_init("iconnectsync/product");
    }
	
	static public function runSyncing() {
	  
	  $config = new Mage_Core_Model_Config();
	  $version = $newversion = Mage::getStoreConfig('iconnectsync/versions/products');
	  $page = 0;
	  $numberOfPages = 0;
	  $helper = Mage::helper('iconnectsync');
		 
	  $attributeSetId = $helper->getDefaultAttributeSetId();
	  
	  do
	  {	  			
	    // Get products from iConnect. To resync all products, set product row version to 0 in cms
	    $result = $helper->getData(self::ICONNECT_GET_PRODUCTS_API,$page,$version);
	    $result = json_decode($result);

	    if($result)
	    {	    	
	      // Look through the result set and create new products as needed
	      foreach($result->data as $entity)
	      {
	      	// There is a dummy 'service' product in iConnect that we don't want with sku 002
	      	if($entity->Sku == '000' || $entity->Sku == '001' || $entity->Sku == '002')
	      		continue;
	      		      
	        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

	        try{	
				self::createProduct($entity,$attributeSetId);			 					
	        }
	        catch(Exception $e){						
	          Mage::log($e->getMessage());
	        }	      	       
	      }

	      $page++;
	      $numberOfPages = $result->pages;

	      if($version == $newversion)
	        $newversion = $result->version;
	    }
	    else{
	    	// Nebo edit: We've had an issue where no data gets collected from iConnect in the product api call
	    	die('No result');
	    }
	  }
	  while($page < $numberOfPages);

		// Saves the 'row version' in Magento to prevent resync. 
		$config->saveConfig('iconnectsync/versions/products',$newversion);

		return true;
	}

	static private function createProduct($entity,$attributeSetId)
	{		
		$website = Mage::getModel('core/website')->load($entity->LocationId,'iconnect_location_id');	
		//if we have no such website skip that product		
		if(!$website->getId())
			return;					
		$store_id = $website->getDefaultStore()->getStoreId();
		
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$entity->Sku);
		
		// Does this product exist?
		if(!$product){				
			// Product doesn't exist -- create it
			$product = Mage::getModel('catalog/product')
				->setStoreId($store_id)
				->setWebsiteIds(array($website->getId())) //website ID the product is assigned to, as an array				
				->setAttributeSetId($attributeSetId) //ID of a attribute set named 'default'
				->setTypeId('simple') //product type
				->setCreatedAt(strtotime('now')) //product creation time
				->setStatus(1) //product status (1 - enabled, 2 - disabled)
				->setTaxClassId(0) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
				->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //catalog and search visibility
				->setWeight(1.0000)
				->setSku($entity->Sku)
				->setName(ucwords(strtolower($entity->Name))) 
				->setPrice($entity->Price)
				->setCost($entity->Cost)									
				->setStockData(array('use_config_manage_stock' => 1)) //'Use config settings' checkbox													
				->setData('iconnect_product_id',$entity->Id);
					
		}
		else
		{		
			$website_ids = $product->getWebsiteIds();		
			if (!in_array($website->getId(),$website_ids))  
				array_push($website_ids,$website->getId());	
			
			$product->setWebsiteIds($website_ids);
			//set iconnect product id
			$product->setWebsiteIdId($website->getId());
			$product->setStoreId($store_id);
			$product->setData('iconnect_product_id',$entity->Id);					
		}		
					
		$product->setIsMassupdate(true)->setExcludeUrlRewrite(true);
		$product->save();		

		return $product;
	}
}
