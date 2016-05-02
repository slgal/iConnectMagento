<?php

/**
 * Cron functions
 *
 * @category   InspireSmart
 * @package    InspireSmart_IConnectSync
 * @author     Viacheslav Galanov (vgalanov@inspiresmart.com)
 */
class InspireSmart_IConnectSync_Model_Cron
{

    /**
     * Sync via cron schedule
     *
     * @param object $schedule
     * @return void
     */
    public static function syncOrders($schedule)
    {
		$helper = Mage::helper('iconnectsync');
		
		if ($helper->isSyncPaused()) {
            return;
        }
		
		$status = $helper->orderSyncStatus();
        try {
			
            $queueCollection = Mage::getModel('iconnectsync/que')
                ->getCollection()
				->join(array('order' => 'sales/order'), 'main_table.entity_id=order.entity_id')				
                ->addFieldToFilter('synced_at', array('null' => true))
				->addFieldToFilter('order.status', array('eq' => $status));
				
            InspireSmart_IConnectSync_Model_Que::doSync($queueCollection);
			
        } catch (Exception $e) {
            // save any errors.
            Mage::logException($e);
            return $e->getMessage();
        }
    }

	public static function syncProducts($schedule)
    {    
		$helper = Mage::helper('iconnectsync');
		
		if ($helper->isSyncPaused()) {
            return;
        }
			
		try {						
			//sync products			
			$processes = Mage::getSingleton('index/indexer')->getProcessesCollection();
			$processes -> walk('setMode', array(Mage_Index_Model_Process::MODE_MANUAL));
			$processes -> walk('save');
									
			InspireSmart_IConnectSync_Model_Product::runSyncing();
					
			$processes->walk('reindexAll');
			$processes->walk('setMode', array(Mage_Index_Model_Process::MODE_REAL_TIME));
			$processes->walk('save');
		} 
		catch (Exception $e) {
            // save any errors.
            Mage::logException($e);
            return $e->getMessage();
        }
    }
	
    /**
     * Clean old records on schedule
     *
     * @param object $schedule
     * @return void
     */
    public static function clean($schedule)
    {
        try {
            $syncModel = Mage::getModel('iconnectsync/que')->getCollection()
                ->addFieldToFilter(
                    'created_at',
                    array('lteq' => $schedule->getExecutedAt())
                )
                ->addFieldToFilter('synced_at', array('notnull' => true));
            foreach ($syncModel as $key => $sync) {
               $sync->delete();
            }
        } catch (Exception $e) {
            // save any errors.
            Mage::logException($e);
            return $e->getMessage();
        }
    }

}