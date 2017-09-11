<?php

defined('_JEXEC') or die;

//类别 组件名.Controller
class WalletControllerWallets extends JControllerAdmin{

	public function getModel($name = 'Wallet', $prefix = 'WalletModel', $config = array('ignore_request'=>true)){
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}