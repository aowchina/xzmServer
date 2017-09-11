<?php
defined('_JEXEC') or die;

class WborderViewWborders extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		WborderHelper::addSubmenu('wborders');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = WborderHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('求购订单管理：求购订单列表'), 'list-2');
		
		if($canDo->get('core.edit','详情')){
			JToolbarHelper::editList('wborder.edit','详情');
		}

		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_wborder');
		}

       	JHtmlSidebar::setAction('index.php?option=com_wborder&view=wborders');
		$status_option = array();

		$op1 = array('value'=>'0', 'text'=>'待支付');
		$op2 = array('value'=>'1', 'text'=>'待发货');
		$op3 = array('value'=>'2', 'text'=>'待收货');
		$op4 = array('value'=>'3', 'text'=>'待评价');
		$op5 = array('value'=>'4', 'text'=>'已完成');


		$status_option[] = $op1;
		$status_option[] = $op2;
		$status_option[] = $op3;
		$status_option[] = $op4;
		$status_option[] = $op5;

       	JHtmlSidebar::addFilter(JText::_('支付状态'), 'filter_status',
       		JHtml::_('select.options', $status_option, 'value', 'text', $this->state->get('filter.status'), true));

	}
}
