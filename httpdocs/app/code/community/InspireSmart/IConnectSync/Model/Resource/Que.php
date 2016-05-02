<?php

/**
 *
 *
 * @category   InspireSmart
 * @package    InspireSmart_IConnectSync
 * @author     Viacheslav Galanov (vgalanov@inspiresmart.com)
 */


class InspireSmart_IConnectSync_Model_Resource_Que extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init("iconnectsync/que", "id");
    }
}