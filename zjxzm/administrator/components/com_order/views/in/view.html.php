<?php

defined('_JEXEC') or die;

class OrderViewIn extends JViewLegacy{
	protected $item;
	protected $form;

	public function display($tpl = null){
		//$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = OrderHelper::getActions();
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('订单管理：导入订单数据'), 'list-2');

		if($canDo->get('core.edit')){
			JToolbarHelper::save('orders.save');
		}

		if(empty($this->item->id)){
			JToolbarHelper::cancel('orders.cancel');
		}else{
			JToolbarHelper::cancel('orders.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
