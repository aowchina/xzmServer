<?php

defined('_JEXEC') or die;

class EpcViewEpc extends JViewLegacy{
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

		$epcid = $this->item->epcid;
		if(empty($epcid)){
			JToolbarHelper::title(JText::_('epc类别: 添加'), 'book');
		}else{
			JToolbarHelper::title(JText::_('epc类别: 修改'), 'book');
		}

		JToolbarHelper::save('epc.save');
		// JToolbarHelper::save2new('we.save2new');

		if(empty($this->item->epcid)){
			JToolbarHelper::cancel('epc.cancel');
		}else{
			JToolbarHelper::cancel('epc.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
