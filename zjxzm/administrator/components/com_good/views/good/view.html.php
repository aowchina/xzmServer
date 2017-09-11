<?php

defined('_JEXEC') or die;

class GoodViewGood extends JViewLegacy{
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

		$goodid = $this->item->goodid;

		if(empty($goodid)){
			JToolbarHelper::title(JText::_('商品信息: 添加'), 'book');
		}else{
			JToolbarHelper::title(JText::_('商品信息: 修改'), 'book');
		}

		JToolbarHelper::save('good.save');
		// JToolbarHelper::save2new('we.save2new');

		if(empty($this->item->goodid)){
			JToolbarHelper::cancel('good.cancel');
		}else{
			JToolbarHelper::cancel('good.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
