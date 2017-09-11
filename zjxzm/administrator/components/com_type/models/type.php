
<?php
defined('_JEXEC') or die;
class TypeModelType extends JModelAdmin
{
	protected $text_prefix = 'COM_TYPE';
	public function getTable($type = 'Type', $prefix = 'TypeTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_type.type', 'type',array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	//获取修改记录信息
	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_type.edit.type.data', array());
		if (empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}
    
    
     public function save($data)
     {
     	$tname = trim($data['tname']);
     	$typeid = $data['typeid'];
		 $time = time();
		 $data['addtime']=$time;


          // 实例化数据库是否成功
		 if(JFactory::getDBO())
		 {
		 	$db = JFactory::getDBO();
		 }else
		 {
		 	$this->setError("服务器繁忙，请重试！");
	  		return false;
	  	}
	 	
         $sql =  "select count(*) from #__type where tname='$tname' and typeid != '$typeid'";
   
         $db->setQuery($sql);
	  	$result = $db->loadResult();
		 if($result > 0)
		 {
	  		$this->setError("商品类别名称已存在，请重新输入！");
		 	return false;
		 }
		

		 //判断保存数据是否成功
	  	if(parent::save($data)){
		 	return true;
		 }else{
		 	$this->setError("：保存数据失败，请重新提交！");
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

		//判断该分类下是否存在子分类
		foreach($data as $id)
		{
			$sql="select count(*) from #__pt where typeid=".$id;
			// $sql="select count(*) from #__type a left join #__pt b on a.typeid=b.typeid where a.typeid='$id' ";
			$db->setQuery($sql);
			$result = $db->loadResult();
			if($result != 0 )
			{
				$this->setError("该商品分类下存在配件分类！");
				return false;
			}
		}
		if(parent::delete($data))
			return true;
		else
			return false;
	}
	

}






















