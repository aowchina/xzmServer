<?php
defined('_JEXEC') or die;

$user = JFactory::getUser();
$listWborder = $this->escape($this->state->get('list.wbordering')); 
$listDirn = $this->escape($this->state->get('list.direction'));

?>

<form action="<?php echo JRoute::_('index.php?option=com_wborder&view=wborders'); ?>" method="post" name="adminForm" id="adminForm">
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
            			<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listWborder); ?>
            		</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '订单编号','a.qgorderid', $listDirn, $listWborder); ?>
					</th>

            			<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '下单者','a.name', $listDirn, $listWborder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '下单者手机号','b.tel', $listDirn, $listWborder); ?>
					</th>

                    <th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '类型','a.type', $listDirn, $listWborder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '单价金额','a.price', $listDirn, $listWborder); ?>
					</th>


					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '下单日期','a.addtime', $listDirn, $listWborder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '支付方式','a.paytype', $listDirn, $listWborder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '支付日期','a.paytime', $listDirn, $listWborder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '状态','a.status', $listDirn, $listWborder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '快递单号','a.kuaidih', $listDirn, $listWborder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '物流名称','a.wlname', $listDirn, $listWborder); ?>
					</th>

				<!-- 	<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '是否确认收货','a.ifreceive', $listDirn, $listWborder); ?>
					</th> -->

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '收货时间','a.retime', $listDirn, $listWborder); ?>
					</th>


					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '收货人姓名','a.sname', $listDirn, $listWborder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '收货人手机号','a.stel', $listDirn, $listWborder); ?>
					</th>

<!--					<th width="5%" class="nowrap center hidden-phone">-->
<!--						--><?php //echo JHtml::_('grid.sort', '省','a.pid', $listDirn, $listWborder); ?>
<!--					</th>-->
<!---->
<!--					<th width="5%" class="nowrap center hidden-phone">-->
<!--						--><?php //echo JHtml::_('grid.sort', '市','a.cid', $listDirn, $listWborder); ?>
<!--					</th>-->
<!---->
<!--					<th width="5%" class="nowrap center hidden-phone">-->
<!--						--><?php //echo JHtml::_('grid.sort', '区','a.qid', $listDirn, $listWborder); ?>
<!--					</th>-->
<!---->
<!--					<th width="5%" class="nowrap center hidden-phone">-->
<!--						--><?php //echo JHtml::_('grid.sort', '详细地址','a.address', $listDirn, $listWborder); ?>
<!--					</th>-->

					<!-- <th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '备注','a.info', $listDirn, $listWborder); ?>
					</th>	 -->				

					

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
						<a href="<?php echo JRoute::_('index.php?option=com_wborder&task=wborder.edit&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->qgorderid); ?>
						</a>
					</td>

					<td class="center hidden-phone">
						<?php echo $this->escape($item->name); ?>
					</td>

					<td class="center hidden-phone">
						<?php echo $this->escape($item->tel); ?>
					</td>

					<td class="center hidden-phone">
						<?php
						$arr=[0,1,2,3];
						$data=[
							"原厂 " => 0,
						    "拆车 " => 1,
							"品牌 " => 2,
						    "其他 " => 3,
						];
						$types = $this->escape($item->type);
						$type = explode(',',$types);

						foreach($type as $v){
                            if(in_array($v,$arr)){
								$v_type= array_search($v,$data);
								echo $v_type;
							}
						}
						
                           ?>
					</td>


					<td class="center hidden-phone">
						<?php echo $this->escape($item->price); ?>
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
							echo date('Y-m-d',$paytime);
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
							// case 3:
							// 	echo '待评价';
							// 	break;
							case 4:
								echo '已完成';
								break;
							// case 5:
							// 	echo '已取消';
							// 	break;
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


					<!-- <td class="center hidden-phone">
						<?php
						$ifreceive = $this->escape($item->ifreceive);
						if(empty($ifreceive)){
							echo '暂未收货';
						}
						else{
							echo $ifreceive;
						}
						?>
					</td> -->

					<td class="center hidden-phone">
						<?php
						$retime = $this->escape($item->retime);

						if($retime){
							echo date('Y-m-d',$retime);
						}
						else{
							echo '暂未收货';
						}
						?>
					</td>

					<td class="center hidden-phone">
						<?php echo $this->escape($item->sname); ?>
					</td>

					<td class="center hidden-phone">
						<?php echo $this->escape($item->stel); ?>
					</td>


					<!-- <td class="center hidden-phone">
						<?php echo $this->escape($item->info); ?>
					</td> -->





				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif;?>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_wborder" value="<?php echo $listWborder; ?>" />
       	<input type="hidden" name="filter_wborder_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
