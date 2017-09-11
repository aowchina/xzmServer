<?php

defined('_JEXEC') or die;

class UstyleViewCimg extends JViewLegacy{
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

		$canDo = UstyleHelper::getActions();
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$imgid = $this->item->imgid;
		if(empty($imgid)){
			JToolbarHelper::title(JText::_('车款图片: 添加'), 'book');
		}else{
			JToolbarHelper::title(JText::_('车款图片: 修改'), 'book');
		}

//		JToolbarHelper::save('cimg.save');
		// JToolbarHelper::save2new('we.save2new');

		if($canDo->get('core.create') || $canDo->get('core.edit')){
			JToolbarHelper::save('cimg.save');
			JToolbarHelper::save2new('cimg.save2new');
		}

		if(empty($this->item->imgid)){
			JToolbarHelper::cancel('cimg.cancel');
		}else{
			JToolbarHelper::cancel('cimg.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
