<?php

defined('_JEXEC') or die;

class TypeViewIn extends JViewLegacy{
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
		$canDo = TypeHelper::getActions();
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('类别管理：导入类别数据'), 'list-2');

		if($canDo->get('core.edit')){
			JToolbarHelper::save('types.save');
		}

		if(empty($this->item->typeid)){
			JToolbarHelper::cancel('type.cancel');
		}else{
			JToolbarHelper::cancel('type.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
