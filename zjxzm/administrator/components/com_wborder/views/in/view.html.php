<?php

defined('_JEXEC') or die;

class WborderViewIn extends JViewLegacy{
	protected $item;
	protected $form;

	public function display($tpl = null){
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
var_dump($this->item);exit;
		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		//获取指定时间的下一天
		$date = date('Y-m-d',$this->item->paytime);
		$nextDay = date("Y-m-d",strtotime('+1 days',strtotime($date)));
		
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = WborderHelper::getActions();
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('订单管理：导入订单数据'), 'list-2');

		if($canDo->get('core.edit')){
			JToolbarHelper::save('wborders.save');
		}

		if(empty($this->item->id)){
			JToolbarHelper::cancel('wborders.cancel');
		}else{
			JToolbarHelper::cancel('wborders.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
