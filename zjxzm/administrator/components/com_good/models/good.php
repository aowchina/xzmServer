
<?php
defined('_JEXEC') or die;
class GoodModelGood extends JModelAdmin
{
	protected $text_prefix = 'COM_GOOD';
	public function getTable($type = 'Good', $prefix = 'GoodTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_good.good', 'good',array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	//获取修改记录信息
	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_good.edit.good.data', array());
		if (empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}
    
    
    public function save($data)
    {
//    	$syear = trim($data['syear']);
//		$eyear = trim($data['eyear']);
//
//		$data['syear'] = strtotime($data['syear']);
//		$data['eyear'] = strtotime($data['eyear']);
		
    	$goodid = $data['goodid'];
		$data['addtime']=time();

         // 实例化数据库是否成功
		if(JFactory::getDBO())
		{
			$db = JFactory::getDBO();
		}else
		{
			$this->setError("服务器繁忙，请重试！");
	 		return false;
	 	}
	 	
//       if($eyear < $syear){
//		   $this->setError("起止年,终止年不合理！");
//		   return false;
//	   }
//		var_dump($data);exit;
		//判断保存数据是否成功
	 	if(parent::save($data)){
			return true;
		}else{
			$this->setError("：保存数据失败，请重新提交！");
			return false;
		}
    }

	

}
