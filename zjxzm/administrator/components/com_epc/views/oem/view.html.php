<?php

defined('_JEXEC') or die;

class EpcViewOem extends JViewLegacy{
	protected $item;
	protected $form;

	public function display($tpl = null){
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$id = $this->item->id;
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
		JToolbarHelper::title(JText::_('配件'), 'cart');

		JToolbarHelper::save('oem.save');
		JToolbarHelper::save2new('oem.save2new');

		if(empty($this->item->id)){
			JToolbarHelper::cancel('oem.cancel');
		}else{
			JToolbarHelper::cancel('oem.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
