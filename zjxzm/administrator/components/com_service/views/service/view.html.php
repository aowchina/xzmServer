<?php

defined('_JEXEC') or die;

class ServiceViewService extends JViewLegacy{
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
			JToolbarHelper::title(JText::_('客服信息: 添加'), 'book');
		}else{
			JToolbarHelper::title(JText::_('客服信息‘: 修改'), 'book');
		}
		if(isset($_GET['info']) && $_GET['info']==1)
		{
			JToolbarHelper::title(JText::_('客服信息: 详情'), 'book');
		}
		else{
			JToolbarHelper::save('service.save');
		}

		// JToolbarHelper::save2new('we.save2new');

		if(empty($this->item->id)){
			JToolbarHelper::cancel('service.cancel');
		}else{
			JToolbarHelper::cancel('service.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
