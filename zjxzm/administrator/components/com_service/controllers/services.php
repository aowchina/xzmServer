<?php

defined('_JEXEC') or die;

//通告 组件名.Controller
class ServiceControllerServices extends JControllerAdmin{

    public function getModel($name = 'Service', $prefix = 'ServiceModel', $config = array('ignore_request'=>true)){
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
}
