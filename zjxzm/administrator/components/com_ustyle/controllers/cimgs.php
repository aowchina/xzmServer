<?php

defined('_JEXEC') or die;

//通告 组件名.Controller
class UstyleControllerCimgs extends JControllerAdmin{

    public function getModel($name = 'Cimg', $prefix = 'UstyleModel', $config = array('ignore_request'=>true)){
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }


}

