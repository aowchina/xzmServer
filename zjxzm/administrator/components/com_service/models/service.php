<?php
defined('_JEXEC') or die;
class ServiceModelService extends JModelAdmin
{
	protected $text_prefix = 'COM_SERVICE';
	public function getTable($type = 'Service', $prefix = 'ServiceTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_service.service', 'service',array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	//获取修改记录信息
	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_service.edit.service.data', array());
		if (empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}

	/*
         @description:添加和修改
         @param:提交的数据
         @author:zhangqin
         @date:2017-2-13
         */
	 public function save($data){
	 	$time=time();
	 	$data['addtime']=$time;
	 	if($data['cservice']){
	 		$service = $data['cservice'];
	 	}
	 	$id = $data['id'];
	 	$type=$data['type'];

	 	// 连接数据库
	 	if(JFactory::getDBO())
	 	{
	 		$db = JFactory::getDBO();
	 	}
	 	else
	 	{
	 		$this->setError("服务器繁忙，请重试！");
	 		return false;
	 	}



	 	if(isset($tel)){
	 		//是否为电话号码
	 		if(!preg_match("/^13[0-9]{1}[0-9]{8}$|14[57]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$/", $tel)){
	 			$this->setError("请输入正确的电话号码！");
	 			return false;
	 		}
	 	}


         //根据id判断是否为修改
	 	if($id!= 0){
	 		if($service){
	 			// 客服人员是否存在
	 			$sql = "select count('id') from `#__service` where cservice='$service' and id!='$id'";
	 			$db->setQuery($sql);
	 			$count = $db->loadResult();
	 			if($count ==1){
	 				$this->setError("客服人员已存在，请重新填写！");
	 				return false;
	 			}
	 		}

	 		//手机号是否存在
	 		$sql = "select haoma from `#__service` where haoma='$tel' and type=1 and id!='$id' ";
	 		$db->setQuery($sql);
	 		$count = $db->loadResult();
	 		if($count ==1){
	 			$this->setError("手机号已存在，请重新填写！");
	 			return false;
	 		}

	 	}else{
	 		if($service){
	 			// 客服人员是否存在
	 			$sql = "select count('id') from `#__service` where cservice='$service'";
	 			$db->setQuery($sql);
	 			$count = $db->loadResult();
	 			if($count ==1){
	 				$this->setError("客服人员已存在，请重新填写！");
	 				return false;
	 			}
	 		}

	 		//手机号是否存在
             if($tel){
	 			$sql = "select count('id') from `#__service` where haoma='$haoma' and type=1";
	 			$db->setQuery($sql);
	 			$count = $db->loadResult();

	 			if($count ==1){
	 				$this->setError("手机号已存在，请重新填写！");
	 				return false;
	 			}
	 		}
	 	}

         // 是否保存成功
	 	if(parent::save($data))
	 		return true;
	 	else
	 		return false;

	 }


	public function delete(&$data)
	{
		if(JFactory::getDBO())
		{
			$db = JFactory::getDBO();
		}else
		{
			$this->setError("服务器繁忙，请重试！");
			return false;
		}

		foreach ($data as $k => $v)
		{
			$sql1 = "select picture from #__service where id= '$v'";
			$db->setQuery($sql1);
			$con= $db->loadAssocList();

			// 删除硬盘上的文件
			unlink(JPATH_ROOT.'/'.$con[$k]['picture']);

			$sql = "delete from #__service where id='$v'";
			$db->setQuery($sql);
			$db->loadResult();

		}

	}


}