<?php

defined('_JEXEC') or die;

//通告 组件名.Controller
class GoodControllerGoods extends JControllerAdmin{

    public function getModel($name = 'Good', $prefix = 'GoodModel', $config = array('ignore_request'=>true)){
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
}

