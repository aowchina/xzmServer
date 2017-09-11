<?php

defined('_JEXEC') or die;

class WborderModelWborder extends JModelAdmin{

	public function getTable($type = 'Wborder', $prefix = 'WborderTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_wborder.wborder', 'wborder', array('control'=>'jform', 'load_data'=>$loadData));

		if(empty($form)){

			return false;
		}

		return $form;
	}

	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_wborder.edit.wborder.data', array());

		if(empty($data)){
			$data = $this->getItem();

			$data=get_object_vars($data);
			$appuid= $data['appuid'];
			$db = JFactory::getDbo();
			$sql = "select username,tel from #__appuser where appuid='$appuid'";
			$db->setQuery($sql);
			$res = $db->loadAssoc();
			array_unshift($data, $res);

		}
	return $data;

	}

	public function save($data)
	{
		//时间验证
		$paytime = strtotime($data['paytime']);

		//入库格式转时间戳
		$data['paytime'] = $paytime;

		//判断保存数据是否成功
		if(parent::save($data)){
			return true;
		}else{
			$this->setError("：保存数据失败，请重新提交！");
			return false;
		}
	}


}
