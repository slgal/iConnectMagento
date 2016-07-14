<?php 
class InspireSmart_IConnectSync_Block_Adminhtml_System_Store_Edit_Form extends Mage_Adminhtml_Block_System_Store_Edit_Form{
    protected function _prepareForm(){
        parent::_prepareForm();
        if (Mage::registry('store_type') == 'website'){
            $websiteModel = Mage::registry('store_data');
          
			$fieldset = $this->getForm()->getElement('website_fieldset');
			
			$fieldset->addField('iconnect_location_id', 'text', array(
                'name'      => 'website[iconnect_location_id]',
                'label'     => Mage::helper('core')->__('iConnect Location Id'),
                'value'     => $websiteModel->getData('iconnect_location_id'), 
                'required'  => false
            ));	
			
			$fieldset->addField('synchronize_location', 'hidden', array(
                'name'      => 'website[synchronize_location]',                 							
				'value'     => (int)$websiteModel->getData('synchronize_location'),                 
            ));				
        }
        return $this;
    }
}