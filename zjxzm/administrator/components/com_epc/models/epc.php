<?php
defined('_JEXEC') or die;
class EpcModelEpc extends JModelAdmin
{
	protected $text_prefix = 'COM_EPC';
	public function getTable($type = 'Epc', $prefix = 'EpcTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_epc.epc', 'epc',array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	//获取修改记录信息
	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_epc.edit.epc.data', array());
		if (empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}
    
    
     public function save($data)
     {
     	$epctid = trim($data['epctid']);
     	$epcid=$data['epcid'];
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
	 	
         $sql =  "select count(*) from #__epc where epctid='$epctid' and epcid != '$epctid'";
   
         $db->setQuery($sql);
	  	$result = $db->loadResult();
		 if($result > 0)
		 {
	  		$this->setError("EPC号已存在，请重新输入！");
		 	return false;
		 }
		

		 //判断保存数据是否成功
	  	if(parent::save($data)){
	  		
	  		//每当新增EPC数据是,向配件分类(zj_pt)表中插入一条相应的数据
			$epcid = mysql_insert_id();
			$sql = "insert zj_pt(name,typeid,epcid) values('".$data['epcname']."',".$data['typeid'].",".$epcid.")";
			$db->execute($db->setQuery($sql));

		 	return true;
		 }else{
		 	$this->setError("：保存数据失败，请重新提交！");
		 	return false;
		 }
     }
  


	

}






















