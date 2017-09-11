<?php

defined('_JEXEC') or die;

class OrderViewOrder extends JViewLegacy{
	protected $item;
	protected $form;

	public function display($tpl = null){
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

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
		$canDo = OrderHelper::getActions();
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$id = $this->item->id;
		JToolbarHelper::title(JText::_('采购订单管理：采购订单'), 'list-2');

		if($canDo->get('core.edit')){
			JToolbarHelper::save('order.save');

		}

		if(empty($this->item->id)){
			JToolbarHelper::cancel('order.cancel');
		}else{
			JToolbarHelper::cancel('order.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
