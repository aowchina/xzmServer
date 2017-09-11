<?php

defined('_JEXEC') or die;

class PtViewIn extends JViewLegacy{
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
		$canDo = PtHelper::getActions();
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('配件分类管理：导入配件分类数据'), 'list-2');

		if($canDo->get('core.edit')){
			JToolbarHelper::save('pts.save');
		}

		if(empty($this->item->id)){
			JToolbarHelper::cancel('pt.cancel');
		}else{
			JToolbarHelper::cancel('pt.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
