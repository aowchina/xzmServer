<?php

defined('_JEXEC') or die;

class WborderViewWborder extends JViewLegacy{
	protected $item;
	protected $form;

	public function display($tpl = null){
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		//获取指定时间的下一天
		$date = date('Y-m-d',$this->item->paytime);
		$nextDay = date("Y-m-d",strtotime('+1 days',strtotime($date)));

		$this->item->paytime = strtotime($nextDay);

		if(empty($this->item->cid)){
			$this->item->cid = 0;
		}

		if(empty($this->item->qid)){
			$this->item->qid = 0;
		}

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = WborderHelper::getActions();
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$id = $this->item->id;
		JToolbarHelper::title(JText::_('求购订单管理：求购订单'), 'list-2');

		if($canDo->get('core.edit')){
			JToolbarHelper::save('wborder.save');

		}

		if(empty($this->item->id)){
			JToolbarHelper::cancel('wborder.cancel');
		}else{
			JToolbarHelper::cancel('wborder.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
