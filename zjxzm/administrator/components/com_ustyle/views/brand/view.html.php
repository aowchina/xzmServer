<?php

defined('_JEXEC') or die;

class UstyleViewBrand extends JViewLegacy{
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

		$brandid = $this->item->brandid;
		if(empty($brandid)){
			JToolbarHelper::title(JText::_('品牌管理: 添加'), 'book');
		}else{
			JToolbarHelper::title(JText::_('品牌管理: 修改'), 'book');
		}

		JToolbarHelper::save('brand.save');
		
		if(empty($this->item->brandid)){
			JToolbarHelper::cancel('brand.cancel');
		}else{
			JToolbarHelper::cancel('brand.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
