<?php

defined('_JEXEC') or die;

//通告 组件名.Controller
class ShopControllerShops extends JControllerAdmin{

    public function getModel($name = 'Shop', $prefix = 'ShopModel', $config = array('ignore_request'=>true)){
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
}
