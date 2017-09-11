
<?php
defined('_JEXEC') or die;
class UstyleModelUstyle extends JModelAdmin
{
	protected $text_prefix = 'COM_USTYLE';
	public function getTable($type = 'Ustyle', $prefix = 'UstyleTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_ustyle.ustyle', 'ustyle',array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	//获取修改记录信息
	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_ustyle.edit.ustyle.data', array());
		if (empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}


	//保存
	public function save($data){

		$id = trim($data['carid']);
		$cname = trim($data['cname']);

		$db = JFactory::getDBO();

		//车款名称验重
		if($id > 0){
			$query = "select count(*) from #__car where cname = '$cname' and carid <> ".$id;

		}else{
			$query = "select count(*) from #__car where cname = '$cname'";
		}
		$db->setQuery($query);
		$count = $db->loadResult();
		if($count > 0){
			$this->setError("：车款名称存在重复记录!");
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
			$sql1 = "select cimage from #__car where carid= '$v'";
			$db->setQuery($sql1);
			$con= $db->loadAssocList();

			// 删除硬盘上的文件
			unlink(JPATH_ROOT.'/'.$con[$k]['cimage']);

			$sql = "delete from #__car where carid='$v'";
			$db->setQuery($sql);
			$db->loadResult();

		}

	}




}






















