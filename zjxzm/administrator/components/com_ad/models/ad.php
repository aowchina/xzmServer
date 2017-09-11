<?php
defined('_JEXEC') or die;
class AdModelAd extends JModelAdmin
{
	protected $text_prefix = 'COM_AD';
	public function getTable($type = 'Ad', $prefix = 'AdTable', $config = array()){
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true){
		$app = JFactory::getApplication();
		$form = $this->loadForm('com_ad.ad', 'ad',array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	//获取修改记录信息
	protected function loadFormData(){
		$data = JFactory::getApplication()->getUserState('com_ad.edit.ad.data', array());
		if (empty($data)){
			$data = $this->getItem();
		}
		return $data;
	}

	// public function save($data){
	// 	//id=0 新建  id=$_GET['id'] 编辑.
	// 	$id=isset($_GET['id'])?$_GET['id']:0;
	// 	$data['ad_title'] = trim($data['ad_title']);
	// 	$data['ad_content'] = trim($data['ad_content']);
	// 	if(empty($data['ad_title']) || empty($data['ad_content']))
	// 	{
	// 		$this->setError("所填内容不能为空！");
	// 		return false;
	// 	}
	// 	$db = JFactory::getDBO();
	// 	$sql = 'select ad_content from `#__ad` where id!='.$id;
	// 	$db->setQuery($sql);
	// 	$contentUrl = $db->loadColumn();
	// 	foreach($contentUrl as $k=>$v)
	// 	{
	// 		$content = unserialize(file_get_contents($v));
	// 		if($data['ad_title'] == $content['title'])
	// 		{
	// 			$this->setError("标题已经存在！");
	// 			return false;
	// 		}
	// 	}
	// 	//新建时生成子目录,并保存文件路径.
	// 	if($id==0)
	// 	{
	// 		$data['ad_create_time'] = time();
	// 		$data['state'] = 2;
	// 		$data['ad_content'] = parent::createDir('vr_ad','ad',['title'=>$data['ad_title'],'content'=>$data['ad_content']],$db);
	// 	}
	// 	else
	// 	{
	// 		$db->setQuery('select ad_content from `#__ad` where id='.$id);
	// 		$result = $db->loadResult();
	// 		file_put_contents($result,serialize(['title'=>$data['ad_title'],'content'=>$data['ad_content']]));
	// 		$data['ad_content'] = $result;
	// 	}

	// 	if(parent::save($data))
	// 		return true;
	// 	else
	// 		return false;
	// }
	
	public function delete(&$data){
		if(JFactory::getDBO())
		{
			$db = JFactory::getDBO();
		}else
		{
			$this->setError("服务器繁忙，请重试！");
			return false;
		}
		//删除文件
		$content_name=implode(',',$data);
		$sql='select img from `#__ad` where id in ('.$content_name.')';
		$db->setQuery($sql);
		$result = $db->loadColumn();
		foreach($result as $k=>$v){
			if(file_exists($v))
				unlink(JPATH_ROOT.'/'.$v);
		}
		if(parent::delete($data))
			return true;
		else
			return false;
	}
}