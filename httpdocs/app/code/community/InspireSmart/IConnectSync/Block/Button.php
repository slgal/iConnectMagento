<?php

class InspireSmart_IConnectSync_Block_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('iconnect/synchronize');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Synchronize...')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}

?>