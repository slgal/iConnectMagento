<?php
/**
 * Queue model
 *
 * @category   InspireSmart
 * @package    InspireSmart_IConnectSync
 * @author     Viacheslav Galanov (vgalanov@inspiresmart.com)
 */
class InspireSmart_IConnectSync_Model_Que extends Mage_Core_Model_Abstract
{
	const ICONNECT_PUT_ORDERS_API = "api/putOrder?token=%s";
		
    protected function _construct()
    {
       $this->_init("iconnectsync/que");
    }

    /**
     * Handle sync of data    
     *
     * @param InspireSmart_IConnectSync_Model_Resource_Que_Collection $syncModel
     */
      
    static public function doSync(InspireSmart_IConnectSync_Model_Resource_Que_Collection $syncModel) 
	{
        $helper = Mage::helper('iconnectsync');

        if ($helper->isSyncPaused()) {
            return;
        }

        foreach ($syncModel as $sync) {
            $order = Mage::getModel('sales/order')->load($sync->getEntityId());
            try {
				
				$fields = self::getOrderArray($order);				
				$results = $helper->sendData(self::ICONNECT_PUT_ORDERS_API,json_encode($fields));
				
				$failed = array_filter(json_decode($results), function($item)
				{			
					return !$item->IsSuccess;
				});
				
				$success = count($failed) == 0;
			
				if($success) {
					 $order->addStatusToHistory('synchronized');
					 
					 $sync->setSyncedAt(now())
						->save();							 
				}
				else {
					foreach($failed as $item){
						if($item->Message){
							$order->addStatusHistoryComment(								
								$helper->__($item->Message),
								'synchronization_error'
							);	
						}
					}															
				}		               
            } catch (Exception $e) {
                $order->addStatusHistoryComment(									
                    $helper->__('Order failed sync: %s', $e->getMessage()),
					'synchronization_error'
                );
            }
            $order->save();
        }
    }
	
	static private function getOrderItemsArray($order)
	{
		$orderItems = array();		
		foreach($order->getItemsCollection() as $item)
		{
			$row=array();
			$row['ProductID'] = -100001;
			$row['Name'] = $item->getName();
			$row['SKU'] =  $item->getSku();
			$row['CategoryName'] = 'Online Product';
			$row['Price'] = floatval($item->getPrice());
			$row['Cost'] = floatval($item->getCost());
			$row['Quantity'] = intval($item->getQtyOrdered());			
			$row['Tax'] = $item->getTaxAmount();
			$row['Discount'] = floatval($item->getDiscountAmount());			
			$row['AdditionalTaxes'] = array(
				/*array(
					'Id' => -100001,
					'Amount' => $item->getTaxAmount()
				)*/
			);
												
			$orderItems[]=$row;
		}
		return $orderItems;
	}
	
	static private function getOrderArray($order)
	{		
		$location_id = $order->getStore()->getWebsite()->getData('iconnect_location_id');
		$helper = Mage::helper('iconnectsync');
		
		$orderItems = self::getOrderItemsArray($order);
		//if shipping is more that 0 than add it as an item
		if($order->getShippingAmount()>0)
		{
			$row=array();
			
			$row['ProductID'] = -100001;
			$row['Name'] = 'Shipping';
			$row['CategoryName'] = 'Online Product';	
			$row['SKU'] = '000';			
			$row['Price'] = floatval($order->getShippingAmount());			
			$row['Quantity'] = 1;			
			$row['AdditionalTaxes'] = array();
		
			$orderItems[]=$row;				
		}
		
		$orderCompleteDate;	
		$commentCollection = $order->getStatusHistoryCollection();								
		foreach ($commentCollection as $comment) {    
		  if ($comment->getStatus() === $helper->orderSyncStatus()) {
			$orderCompleteDate = $comment->getCreatedAt();
		  }
		}
					  
		$orders = array(array(
				"LocationId" => $location_id,
				"OrderSourceId" => 4,
				"TemporaryOrderId" => $order->getTemporaryOrderId(),
				"CustomerId" => null,//guest customer
				"SubtotalInclTax" => $order->getSubtotal() + $order->getShippingAmount() + $order->getTaxAmount(),
				"SubtotalExclTax" => $order->getSubtotal() + $order->getShippingAmount(),
				"Discount"=> abs($order->getDiscountAmount()),				
				"Taxes" => $order->getTaxAmount(),
				"Total" => $order->getGrandTotal(),				
				"CurrencyCode" => $order->getOrderCurrencyCode(),
				"CreatedOn" => str_replace(' ','T', $orderCompleteDate),
				"SalesPersonID" => -100001, //hardcoded value to map company admin				
				"Version" => "magento-".Mage::getVersion(),						
				"Items"=> $orderItems,
				"Payments"=> array(				
				  array(
					"PaymentMethodID"=> 4,// outside
					"PaymentStatusID"=> 10,// paid
					"PaymentAmount"=> floatval($order->getTotalPaid()),
					"SalesPersonID"=> -100001, //hardcoded value to map company admin
					"CreatedOn"=> str_replace(' ','T',$orderCompleteDate)					
				  )
				),
				"ShippingDetailsData" => array(),
				"Discounts"=> array(),						
				"Tips"=> array(),
				"EmployeeCommisions"=> array(),			  
				"OrderTaxes"=> array(),
				"OrderTaskItems"=>array(),
				"OrderBookings"=> array(),			  
		  )
		);
		return $orders;
	}
}