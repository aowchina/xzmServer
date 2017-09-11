<?php
defined('_JEXEC') or die;
class UstyleModelUstype extends JModelAdmin
{
	protected $text_prefix = 'COM_USTYLE';
	public function getTable($type = 'Ustype', $prefix = 'UstyleTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_ustyle.ustype', 'ustype',array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_ustyle.edit.ustype.data', array());
		if (empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}

//保存
	public function save($data){
//		var_dump($data);die;

		$id = trim($data['serialid']);
		$sname = trim($data['sname']);

		$db = JFactory::getDBO();

		//车系名称验重
		if($id > 0){
			$query = "select count(*) from #__serial where sname = '$sname' and serialid <> ".$id;

		}else{
			$query = "select count(*) from #__serial where sname = '$sname'";
		}
		$db->setQuery($query);
		$count = $db->loadResult();
		if($count > 0){
			$this->setError("：车系名称存在重复记录!");
			return false;
		}

		$data['addtime'] = time();

		if(parent::save($data)){
			return true;
		}else{
			return false;
		}
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
			$sql1 = "select simage from #__serial where serialid= '$v'";
			$db->setQuery($sql1);
			$con= $db->loadAssocList();

			// 删除硬盘上的文件
			unlink(JPATH_ROOT.'/'.$con[$k]['simage']);

			$sql = "delete from #__serial where serialid='$v'";
			$db->setQuery($sql);
			$db->loadResult();

		}

	}


}

