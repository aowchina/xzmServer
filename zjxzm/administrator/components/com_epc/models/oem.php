<?php

defined('_JEXEC') or die;

class EpcModelOem extends JModelAdmin{
	protected $text_prefix = 'COM_EPC';

	public function getTable($type = 'Oem', $prefix = 'EpcTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_epc.oem', 'oem', array('control'=>'jform', 'load_data'=>$loadData));

		if(empty($form)){
			return false;
		}

		return $form;
	}

	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_epc.edit.oem.data', array());

		if(empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}

	//保存
	// public function save($data){
	// 	$id = trim($data['id']);
	// 	$name = trim($data['name']);
	// 	$tid = trim($data['tpid']);
	// 	include_once(JPATH_ROOT.'/Minfo.php');
	// 	$mf = new Minfo;

	// 	$db = JFactory::getDBO();

	// 	//验证名称
	// 	if(!$mf->isExp($name, 1)){
	// 		$this->setError("：类别名称格式不符合要求!");
	// 		return false;
	// 	}

	// 	//名称验重
	// 	if($id > 0){
	// 		$query = "select count(*) from #__goods_type where name = '$name' and id <> $id";
	// 	}else{
	// 		$query = "select count(*) from #__goods_type where name = '$name'";
	// 	}
	// 	$db->setQuery($query);
	// 	$count = $db->loadResult();
	// 	if($count > 0){
	// 		$this->setError("：同一类别名称存在重复记录!");
	// 		return false;
	// 	}
	// 	if($tid != 0)
	// 	{
	// 		$data['parentid'] = $tid;
	// 	}
	// 	$data['intime'] = time();
	// 	if(parent::save($data)){
	// 		return true;
	// 	}else{
	// 		return false;
	// 	}
	// }

	// //实现删除功能
	// public function delete(&$data){
	// 	$db = JFactory::getDBO();
	// 	for($i = 0; $i < count($data); $i++){
	// 		$id = $data[$i];

	// 		$query = "select count(*) from #__goods_type where id = ".$id;
	// 		$db->setQuery($query);
	// 		$result = $db->loadResult();

	// 		if($result != 1){
	// 			$this->setError("记录不存在或已被删除!");
	// 			return false;
	// 		}

	// 		$query = "select count(*) from #__goods where typeid = ".$id;
	// 		$db->setQuery($query);
	// 		$result = $db->loadResult();

	// 		if($result > 0){
	// 			$this->setError("该类别包含产品，不允许删除!");
	// 			return false;
	// 		}

	// 		$query = "select count(*) from #__goods_type where parentid = $id";
	// 		$db->setQuery($query);
	// 		$result = $db->loadResult();
	// 		if($result > 0){
	// 			$this->setError("该类别存在子分类!");
	// 			return false;
	// 		}

	// 		$table = $this->getTable();
	// 		if(!$table->delete($id)){
	// 			$this->setError("删除失败，请重新操作!");
	// 			return false;
	// 		}
	// 	}
	// 	return true;
	// }
}
