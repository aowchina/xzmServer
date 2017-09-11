<?php

defined('_JEXEC') or die;

//通告 组件名.Controller
class AdControllerAds extends JControllerAdmin{

    public function getModel($name = 'Ad', $prefix = 'AdModel', $config = array('ignore_request'=>true)){
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
}
