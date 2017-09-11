<?php

defined('_JEXEC') or die;

class EpcTableEpc extends JTable{

    public function __construct(&$db){
        parent::__construct('#__epc', 'epcid', $db);
    }

	/**
	 * data保存到db之前的准备
	 */
    public function bind($array, $ignore = ''){
	   return parent::bind($array, $ignore);
    }

	/**
	 * 提交表单时，write db的方法
	 */
    public function store($updateNulls = false){
	   return parent::store($updateNulls);
    }
}
