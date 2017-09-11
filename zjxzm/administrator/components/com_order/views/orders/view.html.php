<?php
defined('_JEXEC') or die;

class OrderViewOrders extends JViewLegacy{
	protected $items;
	protected $state;

	public function display($tpl = null){
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		OrderHelper::addSubmenu('orders');

		if(count($errors = $this->get('Errors'))){
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolbar(){
		$canDo = OrderHelper::getActions();
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('采购订单管理：采购订单列表'), 'list-2');

//		if($canDo->get('core.create')){
//			JToolbarHelper::addNew('order.add');
//		}
		
		if($canDo->get('core.edit','详情')){
			JToolbarHelper::editList('order.edit','详情');
			// JToolbarHelper::custom('orders.out', 'pin', 'pin', '导出订单', false);
			// JToolbarHelper::custom('orders.toin', 'contract-2', 'contract-2', '导入订单', false);
		}
		
		if($canDo->get('core.admin')){
			JToolbarHelper::preferences('com_order');
		}

		// JHtmlSidebar::setAction('index.php?option=com_order&view=orders');
		// $wl_option = array();
		// $db = JFactory::getDBO();
		// $sql = 'select id as value,name as text from #__wl';
		// $db->setQuery($sql);
		// $wl_option = $db->loadAssocList();



       	// JHtmlSidebar::addFilter(JText::_('配送方式'), 'filter_wl',
       	// 	JHtml::_('select.options', $wl_option, 'value', 'text', $this->state->get('filter.wl'), true));

       	JHtmlSidebar::setAction('index.php?option=com_order&view=orders');
		$status_option = array();

		$op1 = array('value'=>'0', 'text'=>'待支付');
		$op2 = array('value'=>'1', 'text'=>'待发货');
		$op3 = array('value'=>'2', 'text'=>'待收货');
		$op4 = array('value'=>'3', 'text'=>'已完成');
		$op5 = array('value'=>'4', 'text'=>'待评价');


		$status_option[] = $op1;
		$status_option[] = $op2;
		$status_option[] = $op3;
		$status_option[] = $op4;
		$status_option[] = $op5;

       	JHtmlSidebar::addFilter(JText::_('支付状态'), 'filter_status',
       		JHtml::_('select.options', $status_option, 'value', 'text', $this->state->get('filter.status'), true));

		// //按会员类型搜索
		// JHtmlSidebar::setAction('index.php?option=com_order&view=orders');
		// $group_option = [
		// 	['value'=>'0', 'text'=>'普通消费者'],
		// 	['value'=>'1', 'text'=>'VIP'],
		// 	['value'=>'2', 'text'=>'初级微商'],
		// 	['value'=>'3', 'text'=>'中级微商'],
		// 	['value'=>'4', 'text'=>'高级微商'],

		// ];
		// JHtmlSidebar::addFilter(JText::_('会员类型'), 'filter_group',
		// 	JHtml::_('select.options', $group_option, 'value', 'text', $this->state->get('filter.group'), true));
	}
}
