<?php

defined('_JEXEC') or die;

class UstyleViewInserial extends JViewLegacy{
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
		$canDo = UstyleHelper::getActions();
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('车系管理：导入车系数据'), 'list-2');

		if($canDo->get('core.edit')){
			JToolbarHelper::save('ustypes.save');
		}

		if(empty($this->item->serialid)){
			JToolbarHelper::cancel('ustype.cancel');
		}else{
			JToolbarHelper::cancel('ustype.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
