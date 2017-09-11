<?php
defined('_JEXEC') or die;

class BillViewBills extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');
		BillHelper::addSubmenu('bills');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		//如果求购订单存在的，将求购订单的信息赋到账单信息中
		 foreach ($this->items as $key => $value) {
		 	if($value->qgorderid){
		 		$this->items[$key]->money = $value->qgmoney;
		 		$this->items[$key]->retime = $value->qgretime;
		 		$this->items[$key]->tel= $value->qgtel;
		 		$this->items[$key]->name = $value->qgname;
		 	}
		 }
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = BillHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('账单'), 'pie');
//
//			if($canDo->get('core.create')){
//				JToolbarHelper::addNew('bill.add');
//			}
//
//		if($canDo->get('core.edit')){
//			JToolbarHelper::editList('bill.edit');
//		}
//
//		if ($canDo->get('core.edit.state'))
//		{
//			if ($this->state->get('filter.published') != 2)
//			{
//				JToolbarHelper::publish('bills.publish', 'JTOOLBAR_PUBLISH', true);
//				JToolbarHelper::unpublish('bills.unpublish', 'JTOOLBAR_UNPUBLISH', true);
//			}
//		}
//		if($canDo->get('core.delete')){
//
//		JToolbarHelper::deleteList('', 'bills.delete', 'JTOOLBAR_DELETE');
//
//		}
		if($canDo->get('core.edit')){

			JToolbarHelper::custom('bills.pay', 'save', 'save', '转至配件商钱包', true);
		}

//		if($canDo->get('core.admin')){
//			JToolbarHelper::preferences('com_newinform');
//		}
	}
}
