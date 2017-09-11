<?php
defined('_JEXEC') or die;
class UstyleModelBrand extends JModelAdmin
{
	protected $text_prefix = 'COM_USTYLE';
	public function getTable($type = 'Brand', $prefix = 'UstyleTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_ustyle.brand', 'brand',array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_ustyle.edit.brand.data', array());
		if (empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}

//保存
	public function save($data){
		$id = trim($data['brandid']);
		$bname = trim($data['bname']);


		$db = JFactory::getDBO();

		//品牌名称验重
		if($id > 0){
			$query = "select count(*) from #__brand where bname = '$bname' and brandid <> ".$id;
		}else{
			$query = "select count(*) from #__brand where bname = '$bname'";
		}
		$db->setQuery($query);
		$count = $db->loadResult();
		if($count > 0){
			$this->setError("：品牌名称存在重复记录!");
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
			$sql1 = "select blogo from #__brand where brandid= '$v'";
			$db->setQuery($sql1);
			$con= $db->loadAssocList();

			// 删除硬盘上的文件
			unlink(JPATH_ROOT.'/'.$con[$k]['blogo']);

			$sql = "delete from #__brand where brandid='$v'";
			$db->setQuery($sql);
			$db->loadResult();

		}

	}

}

