<?php

defined('_JEXEC') or die;

class BillViewBill extends JViewLegacy{
	protected $item;
	protected $form;

	public function display($tpl = null){
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar(){
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$id = $this->item->id;
		if(empty($id)){
			JToolbarHelper::title(JText::_('账单管理: 添加'), 'book');
		}else{
			JToolbarHelper::title(JText::_('账单管理: 修改'), 'book');
		}

		JToolbarHelper::save('bill.save');
		
		if(empty($this->item->id)){
			JToolbarHelper::cancel('bill.cancel');
		}else{
			JToolbarHelper::cancel('bill.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
