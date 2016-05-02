<?php
    $installer = $this;
    $installer->startSetup();
	
	$_setup = new Mage_Eav_Model_Entity_Setup('core_setup');
    $_config = new Mage_Core_Model_Config();
    $_config->saveConfig('iconnectsync/general/iconnect_api_url','https://publicapi.iconnectpos.com/');  
	$_config->saveConfig('iconnectsync/general/iconnect_api_token','');  
	$_config->saveConfig('iconnectsync/general/pause_sync','1');  
	$_config->saveConfig('iconnectsync/general/sync_order_on_status','complete');
	//row versions
    $_config->saveConfig('iconnectsync/versions/products','0');
		
	//create product attribute to store iconnect product id	
	$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'iconnect_product_id',  array(
        'type'     => 'int',
        'label'    => 'iConnect Product Id',
        'input'    => 'text',
        'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'  => false,
        'required' => false
    ));
	//add column to order table to map iconnect orders and magento orders
	$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'temporary_order_id', 
													   array('type' =>  Varien_Db_Ddl_Table::TYPE_TEXT,  
															 'nullable'  => true,
															 'length' => 36,
															 'comment' => 'Temporary Order Id',
															 'default' => null															 
															 ));
	
	//add columns to website entity
	$webSiteTableName = $installer->getTable('core/website');
	
	$installer->getConnection()->addColumn($webSiteTableName,'iconnect_location_id',
														array('type' =>  Varien_Db_Ddl_Table::TYPE_INTEGER,  
															 'nullable'  => true,
															 'comment' => 'iConnect Location Id',
															 'default' => null															 
															 ));
	$installer->getConnection()->addColumn($webSiteTableName,'synchronize_location',
														array('type' =>  Varien_Db_Ddl_Table::TYPE_INTEGER,  
															 'nullable'  => false,
															 'comment' => 'Synchronize Location',
															 'default' => 0
															 ));

	$table = $installer->getConnection()
			->newTable($installer->getTable('iconnectsync_que'))
			->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
				'identity'  => true,
				'unsigned'  => true,
				'nullable'  => false,
				'primary'   => true,
				), 'Id')
			->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
				'nullable'  => true,
				), 'Order Increment Id')			
			->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
				'unsigned'  => true,
				'nullable'  => true,
				), 'Magento Order Id')
			->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
				'unsigned'  => true,
				'nullable'  => true,
				), 'Created')
			->addColumn('synced_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
				'unsigned'  => true,
				'nullable'  => true,
				), 'Synced');
			
				
	$installer->getConnection()->createTable($table);
		
	$statusTable = $installer->getTable('sales/order_status');
	$statusStateTable = $installer->getTable('sales/order_status_state');
	
	// Insert statuses
	$installer->getConnection()->insertArray(
		$statusTable,
		array(
			'status',
			'label'
		),
		array(
			array('status' => 'synchronized', 'label' => 'Synchronized'),
			array('status' => 'synchronization_error', 'label' => 'Sync Error')
		)
	);
	
	// Insert states and mapping of statuses to states
	$installer->getConnection()->insertArray(
		$statusStateTable,
		array(
			'status',
			'state',
			'is_default'
		),
		array(
			array(
				'status' => 'synchronized',
				'state' => 'complete',
				'is_default' => 0
			),
			array(
				'status' => 'synchronization_error',
				'state' => 'complete',
				'is_default' => 0
			)
		)
	);
    $installer->endSetup();
?>
