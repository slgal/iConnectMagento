<?php
/**
 *
 * @category   InspireSmart
 * @package    InspireSmart_IConnectSync
 * @author     Viacheslav Galanov (vgalanov@inspiresmart.com)
 */

class InspireSmart_IConnectSync_Model_Observer
{
	const ICONNECT_PUT_ORDERS_API = "api/putOrder?token=%s";
	 /**
     * Event to set temporary order id
     *
     * @param Varien_Event_Observer $observer
     * @return InspireSmart_IConnectSync_Model_Observer
     */
    public function save_temporary_order_id(Varien_Event_Observer $observer)
    {
		$helper = Mage::helper('iconnectsync');		
        $order = $observer->getEvent()->getOrder();     
	    $order->setTemporaryOrderId($helper->NewGuid());       
    }
    /**
     * Event to save order to que
     *
     * @param Varien_Event_Observer $observer
     * @return InspireSmart_IConnectSync_Model_Observer
     */
    public function sales_order_place_after(Varien_Event_Observer $observer)
    {	
        $order = $observer->getEvent()->getOrder();
		$location_id = $order->getStore()->getWebsite()->getData('iconnect_location_id');
		if(!$location_id)
			 return $this;
        try {
            $syncModel = Mage::getModel('iconnectsync/que');
            $data = array(
                'increment_id'=>$order->getIncrementId(),
                'entity_id'=> $order->getId(),				
                'created_at'=> now(),
            );
            $syncModel->setData($data);
            $syncModel->save();
        } catch (Exception $e) {
            Mage::log("Couldn't place order into sync queue! " . $e->getMessage());				
        }
        return $this;
    }
	
}