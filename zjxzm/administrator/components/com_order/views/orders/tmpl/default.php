<?php
defined('_JEXEC') or die;

$user = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering')); 
$listDirn = $this->escape($this->state->get('list.direction'));

?>

<form action="<?php echo JRoute::_('index.php?option=com_order&view=orders'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<div id="filter-bar" class="btn-toolbar">

			<div class="filter-search btn-group pull-left">
           		<label for="filter_search" class="element-invisible"><?php echo JText::_('按订单号、收货人（姓名/手机号）搜索');?></label>
           		<input type="text" name="filter_search" id="filter_search" 
           			placeholder="<?php echo JText::_('按订单号、收货人（姓名/手机号）、快递单号搜索'); ?>"
   					value="<?php echo $this->escape($this->state->get('filter.search'));?>" 
   					title="<?php echo JText::_('按订单号、收货人（姓名/手机号）搜索'); ?>" style="width:300px"/>
         	</div>

         	<div class="filter-search btn-group pull-left">
   				<label for="filter_search2" class="element-invisible"><?php echo JText::_('按下单者姓名或手机号搜索');?></label>
   				<input type="text" name="filter_search2" id="filter_search2" 
           			placeholder="<?php echo JText::_('按下单者姓名或手机号搜索'); ?>"
   					value="<?php echo $this->escape($this->state->get('filter.search2'));?>" 
   					title="<?php echo JText::_('按下单者姓名或手机号搜索'); ?>" style="width:160px"/>
         	</div>


        	<div class="btn-group pull-left">
         		<button class="btn hasTooltip" type="submit" 
         			title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>

           		<button class="btn hasTooltip" type="button" 
           			title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" 
           			onclick="document.getElementById('filter_search').value='';document.getElementById('filter_search2').value='';document.getElementById('filter_search3').value='';this.form.submit();"><i class="icon-remove"></i></button>
         	</div>

         	<div class="btn-group pull-right hidden-phone">
           		<label for="limit" class="element-invisible">
           			<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?>
           		</label>
           		<?php echo $this->pagination->getLimitBox(); ?>
         	</div>
		</div>

		<div class="clearfix"> </div>

	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>

		<table class="table table-striped" id="eventList">
			<thead>
				<tr>
					<th width="1%" class="center hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>

					<th width="1%" class="nowrap center hidden-phone">
            			<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
            		</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '订单编号','a.orderid', $listDirn, $listOrder); ?>
					</th>

            			<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '下单者姓名','a.name', $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '下单者手机号','b.tel', $listDirn, $listOrder); ?>
					</th>


					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '订单金额','a.money', $listDirn, $listOrder); ?>
					</th>


					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '下单日期','a.addtime', $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '支付方式','a.paytype', $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '支付日期','a.paytime', $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '状态','a.status', $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '快递单号','a.kuaidih', $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '物流名称','a.wlname', $listDirn, $listOrder); ?>
					</th>

					

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '收货时间','a.retime', $listDirn, $listOrder); ?>
					</th>


					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '收货人姓名','a.sname', $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '收货人手机号','a.stel', $listDirn, $listOrder); ?>
					</th>

<!--					<th width="5%" class="nowrap center hidden-phone">-->
<!--						--><?php //echo JHtml::_('grid.sort', '省','a.pid', $listDirn, $listOrder); ?>
<!--					</th>-->
<!---->
<!--					<th width="5%" class="nowrap center hidden-phone">-->
<!--						--><?php //echo JHtml::_('grid.sort', '市','a.cid', $listDirn, $listOrder); ?>
<!--					</th>-->
<!---->
<!--					<th width="5%" class="nowrap center hidden-phone">-->
<!--						--><?php //echo JHtml::_('grid.sort', '区','a.qid', $listDirn, $listOrder); ?>
<!--					</th>-->
<!---->
<!--					<th width="5%" class="nowrap center hidden-phone">-->
<!--						--><?php //echo JHtml::_('grid.sort', '详细地址','a.address', $listDirn, $listOrder); ?>
<!--					</th>-->

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '备注','a.info', $listDirn, $listOrder); ?>
					</th>					

					

				</tr>
			</thead>

			<tfoot>
				<tr>
					<td colspan="20">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>

			<tbody>
			<?php
			foreach($this->items as $i => $item) : 
			?>
				<tr class="row<?php echo $i % 2; ?>">

					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>

					
					<td class="center hidden-phone"><?php echo $this->escape($item->id); ?></td>

					<td class="center hidden-phone">
						<a href="<?php echo JRoute::_('index.php?option=com_order&task=order.edit&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->orderid); ?>
						</a>
					</td>

					<td class="center hidden-phone">
						<?php echo $this->escape($item->name); ?>
					</td>

					<td class="center hidden-phone">
						<?php echo $this->escape($item->tel); ?>
					</td>


					<td class="center hidden-phone">
						<?php echo $this->escape($item->money); ?>
					</td>


					<td class="center hidden-phone"><?php echo date("Y-m-d H:i:s", $this->escape($item->addtime)); ?></td>


					<td class="center hidden-phone">
						<?php
						$paytype = $this->escape($item->paytype);
						if(empty($paytype)){
							echo '暂未支付';
						}
						elseif($paytype == 1){
							echo '支付宝';
						}elseif($paytype == 2){
							echo '微信';
						}elseif($paytype == 3){
							echo '余额支付';
						}else{
							echo $paytype;
						}
						?>
					</td>

					<td class="center hidden-phone">
						<?php
						$paytime = $this->escape($item->paytime);
						if(empty($paytime)){
							echo '暂未支付';
						}
						else{
							echo date("Y-m-d H:i:s", $this->escape($item->addtime));
						}
						?>
					</td>

					<td class="center hidden-phone">
						<?php
						$status = $this->escape($item->status);
						switch($status){
							case 1:
								echo '待发货';
								break;
							case 2:
								echo '已发货';
								break;
							case 3:
								echo '已完成';
								break;
							case 4:
								echo '已取消';
								break;
							default:
								echo '待支付';
								break;
						}
						?>
					</td>

					<td class="center hidden-phone">
						<?php
						$kuaidih = $this->escape($item->kuaidih);
						if(empty($kuaidih)){
							echo '待发货';
						}
						else{
							echo $kuaidih;
						}
						?>
					</td>

					<td class="center hidden-phone">
						<?php
						$wlname = $this->escape($item->wlname);
						if(empty($wlname)){
							echo '待发货';
						}
						else{
							echo $wlname;
						}
						?>
					</td>


			

					<td class="center hidden-phone">
						<?php
						$retime= $this->escape($item->retime);

						if(empty($retime)){
							echo '暂未收货';
						}
						else{
							echo $retime;
						}
						?>
					</td>

					<td class="center hidden-phone">
						<?php echo $this->escape($item->sname); ?>
					</td>

					<td class="center hidden-phone">
						<?php echo $this->escape($item->stel); ?>
					</td>


					<td class="center hidden-phone">
						<?php echo $this->escape($item->info); ?>
					</td>





				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif;?>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
       	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
