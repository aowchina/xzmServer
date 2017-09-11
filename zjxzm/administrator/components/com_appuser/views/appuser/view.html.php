<?php

defined('_JEXEC') or die;

class AppuserViewAppuser extends JViewLegacy{
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
		$canDo = AppuserHelper::getActions();
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$id = $this->item->appuserid;
		JToolbarHelper::title(JText::_('APP用户管理：app用户'), 'database');

		if($canDo->get('core.create') || $canDo->get('core.edit')){
			JToolbarHelper::save('appuser.save');
			JToolbarHelper::save2new('appuser.save2new');
		}

		if(empty($this->item->id)){
			JToolbarHelper::cancel('appuser.cancel');
		}else{
			JToolbarHelper::cancel('appuser.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
