<?php

defined('_JEXEC') or die;

class UstyleViewInbrand extends JViewLegacy{
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

		JToolbarHelper::title(JText::_('品牌管理：导入品牌数据'), 'list-2');

		if($canDo->get('core.edit')){
			JToolbarHelper::save('brands.save');
		}

		if(empty($this->item->brandid)){
			JToolbarHelper::cancel('brand.cancel');
		}else{
			JToolbarHelper::cancel('brand.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
