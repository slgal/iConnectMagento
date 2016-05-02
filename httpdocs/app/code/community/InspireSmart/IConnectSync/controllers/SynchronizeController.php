<?php
	class InspireSmart_IConnectSync_SynchronizeController extends Mage_Adminhtml_Controller_Action
	{			
		public function indexAction ()
		{		
			$this->loadLayout();		
			try {
				
			$processes = Mage::getSingleton('index/indexer')->getProcessesCollection();
			$processes->walk('setMode', array(Mage_Index_Model_Process::MODE_MANUAL));
			$processes->walk('save');		
					   
			// run product sync
			InspireSmart_IConnectSync_Model_Product::runSyncing();    	

			$processes->walk('setMode', array(Mage_Index_Model_Process::MODE_REAL_TIME));
			$processes->walk('save');

			$processes->walk('reindexAll');
			$processes->walk('reindexEverything');

			$block = $this->getLayout()
				->createBlock('core/text', 'example-block')
				->setText('<h1>Job is done!</h1>');

			$this->_addContent($block);
			
			} catch (Exception $e) {
				 $errorBlock = $this->getLayout()
					->createBlock('core/text', 'example-block')
					->setText($e->getMessage());

				$this->_addContent($errorBlock);
			}
			$this->renderLayout();	
		}	
	}
?>
