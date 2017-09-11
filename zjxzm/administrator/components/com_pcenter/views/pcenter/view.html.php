<?php

defined('_JEXEC') or die;

class PcenterViewPcenter extends JViewLegacy{
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
			JToolbarHelper::title(JText::_('文本管理: 详情'), 'book');
		}else{
			JToolbarHelper::title(JText::_('文本管理: 修改'), 'book');
		}

		JToolbarHelper::save('pcenter.save');


		// JToolbarHelper::save2new('we.save2new');

		if(empty($this->item->id)){
			JToolbarHelper::cancel('pcenter.cancel');
		}else{
			JToolbarHelper::cancel('pcenter.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
