<?php

defined('_JEXEC') or die;

class ShopViewShop extends JViewLegacy{
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

		$id = $this->item->shopid;
		if(empty($id)){
			JToolbarHelper::title(JText::_('店铺管理: 添加'), 'book');
		}else{
			JToolbarHelper::title(JText::_('店铺管理: 修改'), 'book');
		}

		JToolbarHelper::save('shop.save');
		// JToolbarHelper::save2new('we.save2new');

		if(empty($this->item->uslid)){
			JToolbarHelper::cancel('shop.cancel');
		}else{
			JToolbarHelper::cancel('shop.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
