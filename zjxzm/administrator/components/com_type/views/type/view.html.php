<?php

defined('_JEXEC') or die;

class TypeViewType extends JViewLegacy{
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

		$typeid = $this->item->typeid;
		if(empty($typeid)){
			JToolbarHelper::title(JText::_('商品类别: 添加'), 'book');
		}else{
			JToolbarHelper::title(JText::_('商品类别: 修改'), 'book');
		}

		JToolbarHelper::save('type.save');
		// JToolbarHelper::save2new('we.save2new');

		if(empty($this->item->uscid)){
			JToolbarHelper::cancel('type.cancel');
		}else{
			JToolbarHelper::cancel('type.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
