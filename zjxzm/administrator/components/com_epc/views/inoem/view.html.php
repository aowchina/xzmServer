<?php

defined('_JEXEC') or die;

class EpcViewInoem extends JViewLegacy{
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
		$canDo = EpcHelper::getActions();
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('配件管理：导入配件数据'), 'list-2');

		if($canDo->get('core.edit')){
			JToolbarHelper::save('oems.save');
		}

		if(empty($this->item->id)){
			JToolbarHelper::cancel('oems.cancel');
		}else{
			JToolbarHelper::cancel('oems.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
