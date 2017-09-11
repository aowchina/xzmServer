<?php

defined('_JEXEC') or die;

class SellerViewSeller extends JViewLegacy{
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
		$canDo = SellerHelper::getActions();
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$id = $this->item->sellerid;
		JToolbarHelper::title(JText::_('配件商管理：配件商'), 'database');

		if($canDo->get('core.create') || $canDo->get('core.edit')){
			JToolbarHelper::save('seller.save');
			JToolbarHelper::save2new('seller.save2new');
		}

		if(empty($this->item->id)){
			JToolbarHelper::cancel('seller.cancel');
		}else{
			JToolbarHelper::cancel('seller.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
