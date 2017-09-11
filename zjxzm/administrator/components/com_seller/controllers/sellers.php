<?php

defined('_JEXEC') or die;

//类别 组件名.Controller
class SellerControllerSellers extends JControllerAdmin{

	public function getModel($name = 'Seller', $prefix = 'SellerModel', $config = array('ignore_request'=>true)){
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	 public function getOp(){
	 	$pid = $_GET['pid'];
	 	$db = JFactory::getDbo();

	 	$sql = "select areaname as name, id from #__area where parentid = $pid";
	 	$db->setQuery($sql);

	 	$re = $db->loadObjectList();
	 	$json_re = json_encode($re);
	 	$new_re = json_decode($json_re, true);
	 	array_unshift($new_re, array('name'=>'无', 'id'=>0));

	 	echo json_encode($new_re);
	 	exit;
	 }


	//发布与取消发布is_rz代表app端的状态码，status代表joomla的状态码
	public function publish(){
		$ids = $this->input->get('cid', array(), 'array');
//		var_dump($ids);exit;
		$values = array('archive' => 2,'publish' => 1, 'unpublish' =>0 );//(审核中,已发布,未通过)
		$task   = $this->getTask();
		$value  = JArrayHelper::getValue($values, $task, 0, 'int');
		// var_dump($value);exit;
		$db = JFactory::getDbo();
		foreach($ids as $id){
			$sql = "update #__seller set state = $value where sellerid = $id";
			$db->setQuery($sql);
			$db->query();
			if($value == 1){
				$is_rz = 1;//已通过
				// $sql = "select picture,tel from #__seller where sellerid = $id";
				// $db->setQuery($sql);
				// $oldImg = $db->loadAssoc();

				// $sql = "select sname,picture,company from #__sellercert where sellerid = $id";
				// $db->setQuery($sql);
				// $re = $db->loadAssoc();

				// $sql = "update #__seller set name ='$re[sname]',picture ='$re[picture]' where sellerid = $id";
				// $db->setQuery($sql);
				// if($db->execute())
				// {
				// 	if(is_file(JPATH_ROOT.'/'.$oldImg['picture']))
				// 	{
				// 		unlink(JPATH_ROOT.'/'.$oldImg['picture']);
				// 	}
				// }

				//若认证成功,并且该用户在店铺表中没有记录，则插入店铺，否则，更新用户表中的店铺id
				$sql = "select count(*) from zj_shop where sellerid = $id";
				$db->setQuery($sql);
				$count = $db->loadResult();

				if($count == 0)
				{
					$time = time();
					$sql = "insert into zj_shop(tel,shopname,picture,sellerid,state,addtime,number,rate) values('$oldImg[tel]','$re[company]','$re[picture]',$id,1,$time,0,0)";
					$db->setQuery($sql);
					if($db->execute()){
						$sql = "select shopid from zj_shop where sellerid=".$id;
						$db->setQuery($sql);
						$shopid = $db->loadAssoc();
						if(isset($shopid['shopid'])){
							$sql = "update #__seller set shopid = $shopid[shopid] where sellerid = $id";
							$db->setQuery($sql);
							$db->execute();
						}
						
					}
				}else{
					$sql = "select shopid from zj_shop where sellerid=".$id;
					$db->setQuery($sql);
					$shopid = $db->loadAssoc();
					if(isset($shopid['shopid'])){
						$sql = "update #__seller set shopid = $shopid[shopid] where sellerid = $id";
						$db->setQuery($sql);
						$db->execute();
					}
				}

			} else if($value == 2){

				$is_rz = 0;//(审核中)

			}else{

				$is_rz=2;//未通过

			}
			$sql = "update #__seller set is_rz = $is_rz where sellerid = $id";
			$db->setQuery($sql);
			$db->execute();

		}

		if($value == 1){
			$ntext = 'COM_PRODUCT_N_ITEMS_PUBLISHED';
		}
		else{
			$ntext = 'COM_PRODUCT_N_ITEMS_UNPUBLISHED';
		}

		$this->setMessage(JText::plural($ntext, count($ids)));
		$this->setRedirect(JRoute::_('index.php?option=com_seller&view=sellers', false));
	}
}