<?php
defined('_JEXEC') or die;
class PtModelPt extends JModelAdmin
{
	protected $text_prefix = 'COM_PT';
	public function getTable($type = 'Pt', $prefix = 'PtTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_pt.pt', 'pt',array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	//获取修改记录信息
	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_pt.edit.pt.data', array());
		if (empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}

	public function save($data){
		$id = trim($data['id']);
		$name = trim($data['name']);
		$tid = 0;


		$db = JFactory::getDBO();

		//名称验重
		if($id > 0){
			$query = "select count(*) from #__pt where name = '$name' and id <> $id";
		}else{
			$query = "select count(*) from #__pt where name = '$name'";
		}
		$db->setQuery($query);
		$count = $db->loadResult();
		if($count > 0){
			$this->setError("：同一类别名称存在重复记录!");
			return false;
		}
		if($tid != 0)
		{
			$data['parentid'] = $tid;
		}
		if(parent::save($data)){
			return true;
		}else{
			return false;
		}
	}
	
	public function delete(&$data){
		if(JFactory::getDBO())
		{
			$db = JFactory::getDBO();
		}else
		{
			$this->setError("服务器繁忙，请重试！");
			return false;
		}

		//判断是否存在子分类
		//判断该分类下是否存在商品
		foreach($data as $id)
		{
			$sql="select count(*) from #__good a left join #__pt b on a.ptid=b.id where b.id='$id' ";
			$db->setQuery($sql);
			$result = $db->loadResult();
			if($result != 0 )
			{
				$this->setError("该配件分类下存在商品！");
				return false;
			}
		}
		if(parent::delete($data))
			return true;
		else
			return false;
	}
}