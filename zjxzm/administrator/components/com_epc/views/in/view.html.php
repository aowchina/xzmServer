<?php

defined('_JEXEC') or die;

class EpcViewIn extends JViewLegacy{
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

		JToolbarHelper::title(JText::_('EPC结构图管理：导入epc数据'), 'list-2');

		if($canDo->get('core.edit')){
			JToolbarHelper::save('epcs.save');
		}

		if(empty($this->item->typeid)){
			JToolbarHelper::cancel('epc.cancel');
		}else{
			JToolbarHelper::cancel('epc.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
