<?php
defined('_JEXEC') or die;
class PcenterModelPcenter extends JModelAdmin
{
	protected $text_prefix = 'COM_PCENTER';
	public function getTable($type = 'Pcenter', $prefix = 'PcenterTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_pcenter.pcenter', 'pcenter',array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	//获取修改记录信息
	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_pcenter.edit.pcenter.data', array());
		if (empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}

	 public function save($data){
	 	//id=0 新建  id=$_GET['id'] 编辑.
	 	$id=isset($_GET['id'])?$_GET['id']:0;
	 	$data['type'] = trim($data['type']);
	 	$data['url'] = trim($data['url']);
		$data['name']= trim($data['name']);

		 if(strlen($data['url']) >5000)
		 {
			 $this->setError("内容不能超过5000字！");
			 return false;
		 }

		 if($data['type'] == 1 && empty($data['name']))
		 {
			 $this->setError("类别为帮助中心时标题必填！");
			 return false;
		 }

	 	$db = JFactory::getDBO();


	 	if(parent::save($data))
	 		return true;
	 	else
	 		return false;
	 }


}