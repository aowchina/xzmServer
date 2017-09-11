<?php

defined('_JEXEC') or die;

//通告 组件名.Controller
class PcenterControllerPcenters extends JControllerAdmin{

    public function getModel($name = 'Pcenter', $prefix = 'PcenterModel', $config = array('ignore_request'=>true)){
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

}
