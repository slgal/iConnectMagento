<?php
/**
 * Sample Widget Helper
 */
class InspireSmart_IConnectSync_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isSyncPaused()
    {       
        return (bool)Mage::getStoreConfig('iconnectsync/general/pause_sync');
    }	
	
	public function orderSyncStatus()
	{
		return Mage::getStoreConfig('iconnectsync/general/sync_order_on_status');
	}
	
	public function getDefaultAttributeSetId()
	{
		$entityTypeId = Mage::getModel('eav/entity')
                ->setType('catalog_product')
                ->getTypeId();
		$attributeSetName   = 'Default';
		$attributeSetId     = Mage::getModel('eav/entity_attribute_set')
                    ->getCollection()
                    ->setEntityTypeFilter($entityTypeId)
                    ->addFieldToFilter('attribute_set_name', $attributeSetName)
                    ->getFirstItem()
                    ->getAttributeSetId();
					
		return $attributeSetId;
	}
	
	public function getData($api, $page=NULL, $version=NULL)
	{
		$token = Mage::getStoreConfig('iconnectsync/general/iconnect_api_token');
		$host = Mage::getStoreConfig('iconnectsync/general/iconnect_api_url');
		$args = array_slice(func_get_args(),1);		
		array_push($args,$token);
		if( $curl = curl_init()) {
			curl_setopt_array($curl, array(
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_URL => vsprintf($host.$api,$args)
			));
			$out = curl_exec($curl);
			curl_close($curl);
			return $out;
		}
		return "";
	}

	public function sendData($api, $postfields)
	{
		$token = Mage::getStoreConfig('iconnectsync/general/iconnect_api_token');
		$host = Mage::getStoreConfig('iconnectsync/general/iconnect_api_url');		
		
		if( $curl = curl_init()) {
			curl_setopt_array($curl, array(
					CURLOPT_HTTPHEADER => array(                                                                          
						'Content-Type: application/json',                                                                                
						'Content-Length: '.strlen($postfields)),
					CURLOPT_RETURNTRANSFER => true,
    				CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => $postfields,
					CURLOPT_URL => vsprintf($host.$api,$token)
			));
			$out = curl_exec($curl);
			curl_close($curl);
			return $out;
		}
		return "";
	}
	public function getFileData($productId)
	{
		$token = Mage::getStoreConfig('iconnect/general/iconnect_api_token');
		$host = Mage::getStoreConfig('iconnect/general/iconnect_api_url');
		$api = self::ICONNECT_GET_PRODUCT_FILE_IMAGE_API;
		return @file_get_contents(sprintf($host.$api,$productId,$token));
	}
	
	public function NewGuid() 
	{ 
		$s = strtoupper(md5(uniqid(rand(),true))); 
		$guidText = 
			substr($s,0,8) . '-' . 
			substr($s,8,4) . '-' . 
			substr($s,12,4). '-' . 
			substr($s,16,4). '-' . 
			substr($s,20); 
		return $guidText;
	}
}
