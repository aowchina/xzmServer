<?php

defined('_JEXEC') or die;

//类别 组件名.Controller
class AppuserControllerAppusers extends JControllerAdmin{

	public function getModel($name = 'Appuser', $prefix = 'AppuserModel', $config = array('ignore_request'=>true)){
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	 public function getOp(){
	 	$pid = $_GET['pid'];
	 	$db = JFactory::getDbo();

	 	$sql = "select areaname as name, id from #__area where parentid = $pid";
	 	$db->setQuery($sql);

	 	$re = $db->loadObjectList();
	 	$json_re = json_encode($re);
	 	$new_re = json_decode($json_re, true);
	 	array_unshift($new_re, array('name'=>'无', 'id'=>0));

	 	echo json_encode($new_re);
	 	exit;
	 }

	// //跳转至责任区列表
	// public function addarea(){
	// 	$ids = $this->input->get('cid', array(), 'array');
	// 	if(count($ids) < 1){
	// 		JError::raiseWarning(500, '请选择至少一条记录');
	// 		$this->setRedirect(JRoute::_('index.php?option=com_appuser&view=appusers', false));
	// 		return false;
	// 	}

	// 	$id = $ids[0];
		
	// 	$se = JFactory::getSession();
	// 	$se->set('appuserid', $id);

	// 	$this->setRedirect(JRoute::_('index.php?option=com_appuser&view=areas', false));
	// }
}