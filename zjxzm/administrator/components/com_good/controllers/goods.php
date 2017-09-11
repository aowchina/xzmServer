<?php

defined('_JEXEC') or die;

//通告 组件名.Controller
class GoodControllerGoods extends JControllerAdmin{

    public function getModel($name = 'Good', $prefix = 'GoodModel', $config = array('ignore_request'=>true)){
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function publish(){
        $ids = $this->input->get('cid', array(), 'array');

        $values = array('archive' => 2,'publish' => 1, 'unpublish' =>0 );//(审核中,已发布,未通过)
        $task   = $this->getTask();
        $value  = JArrayHelper::getValue($values, $task, 0, 'int');

        $db = JFactory::getDbo();
        foreach($ids as $id){
            $sql = "update #__good set state = $value where goodid = $id";
            $db->setQuery($sql);
            $db->query();
            if($value == 1){
                $is_sj = 1;

            } else if($value == 2){

                $is_sj = 2;//(审核中)

            }else{

                $is_sj=0;//未通过

            }
            $sql = "update #__good set is_sj = $is_sj where goodid = $id";
            $db->setQuery($sql);
            $db->execute();

        }

        if($value == 1){
            $ntext = '数据发布成功！';//COM_PRODUCT_N_ITEMS_PUBLISHED
        }
        else{
            $ntext = '数据发布失败！';//COM_PRODUCT_N_ITEMS_UNPUBLISHED
        }

        $this->setMessage(JText::plural($ntext, count($ids)));
        $this->setRedirect(JRoute::_('index.php?option=com_good&view=goods', false));
    }
    
}

