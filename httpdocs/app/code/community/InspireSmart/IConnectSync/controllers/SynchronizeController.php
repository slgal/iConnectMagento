<?php
	class InspireSmart_IConnectSync_SynchronizeController extends Mage_Adminhtml_Controller_Action
	{			
		public function indexAction ()
		{		
			$this->loadLayout();		
			try {
						 
			// run product sync
			InspireSmart_IConnectSync_Model_Cron::syncOrders(123);    				

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
