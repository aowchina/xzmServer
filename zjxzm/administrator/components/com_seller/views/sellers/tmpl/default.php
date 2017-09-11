<?php
defined('_JEXEC') or die;

$user = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering')); 
$listDirn = $this->escape($this->state->get('list.direction'));

?>

<form action="<?php echo JRoute::_('index.php?option=com_seller&view=sellers'); ?>" method="post" name="adminForm" id="adminForm">
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
           		<label for="filter_search" class="element-invisible"><?php echo JText::_('按名称或公司名搜索');?></label>
           		<input type="text" name="filter_search" id="filter_search"
           			placeholder="<?php echo JText::_('按名称或公司名搜索'); ?>"
   					value="<?php echo $this->escape($this->state->get('filter.search'));?>"
   					title="<?php echo JText::_('按名称或公司名搜索'); ?>" />
         	</div>
        	<div class="btn-group pull-left">
         		<button class="btn hasTooltip" type="submit"
         			title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>

           		<button class="btn hasTooltip" type="button"
           			title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
           			onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
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

					<th width="15%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '发布状态','a.state', $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '名称','a.name', $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '电话','a.tel', $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '认证名称','a.sname', $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '是否认证','a.is_rz', $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '公司名','a.shopid', $listDirn, $listOrder); ?>
					</th>

					<!--<th width="10%" class="nowrap center hidden-phone">
						<?php /*echo JHtml::_('grid.sort', '范围','a.major', $listDirn, $listOrder); */?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php /*echo JHtml::_('grid.sort', '专长','a.skill', $listDirn, $listOrder); */?>
					</th>-->


<!-- 					<th width="10%" class="nowrap center hidden-phone">
						<?php //echo JHtml::_('grid.sort', '用户类别','a.major', $listDirn, $listOrder); ?>
					</th> -->

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '证件号','a.major', $listDirn, $listOrder); ?>
					</th>

					<!--<th width="10%" class="nowrap center hidden-phone">
						<?php /*echo JHtml::_('grid.sort', '所在省','a.pname', $listDirn, $listOrder); */?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php /*echo JHtml::_('grid.sort', '所在市','a.cname', $listDirn, $listOrder); */?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php /*echo JHtml::_('grid.sort', '所在区/县','a.qname', $listDirn, $listOrder); */?>
					</th>-->

					<th width="25%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '详细地址','a.address', $listDirn, $listOrder); ?>
					</th>

					<!--<th width="10%" class="nowrap center hidden-phone">
						<?php /*echo JHtml::_('grid.sort', '个人图像','a.picture', $listDirn, $listOrder); */?>
					</th>


					<th width="15%" class="nowrap center hidden-phone">
						<?php /*echo JHtml::_('grid.sort', '身份证正面照','a.cardfront', $listDirn, $listOrder); */?>
					</th>

					<th width="15%" class="nowrap center hidden-phone">
						<?php /*echo JHtml::_('grid.sort', '身份证背面照','a.carback', $listDirn, $listOrder); */?>
					</th>

					<th width="15%" class="nowrap center hidden-phone">
						<?php /*echo JHtml::_('grid.sort', '手持身份证照','a.cardhand', $listDirn, $listOrder); */?>
					</th>

					<th width="15%" class="nowrap center hidden-phone">
						<?php /*echo JHtml::_('grid.sort', '营业执照','a.license', $listDirn, $listOrder); */?>
					</th>-->

					<th width="15%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '登录时间','a.addtime', $listDirn, $listOrder); ?>
					</th>


					<th width="1%" class="nowrap center hidden-phone">
            			<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.sellerid', $listDirn, $listOrder); ?>
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
				$canCheckin = $user->authorise('core.manage','com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
				$canChange = $user->authorise('core.edit.state','com_seller') && $canCheckin;
			?>
				<tr class="row<?php echo $i % 2; ?>">

					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->sellerid); ?>
					</td>

					<td class="center">
						<?php echo JHtml::_('jgrid.published',$item->state,$i,'sellers.',$canChange,'cb',$item->publish_up,$item->publish_down); ?>
					</td>

					<td class="center hidden-phone">
						<a href="<?php echo JRoute::_('index.php?option=com_seller&task=seller.edit&sellerid='.(int) $item->sellerid); ?>">
							<?php echo $this->escape($item->name); ?>
						</a>
					</td>

					<td class="center hidden-phone">

							<?php echo $this->escape($item->tel); ?>
					</td>

					<td class="center hidden-phone">

							<?php echo $this->escape($item->sname); ?>
					</td>

					<td class="center hidden-phone">
						<?php
						$rz = $this->escape($item->is_rz);
						switch($rz){
							case 0:
								echo "审核中";
								break;
							case 3:
								echo "未认证";
								break;
							case 1:
								echo "已通过";
								break;
							case 2:
								echo "未通过";
								break;
						}
						?>
					</td>

					<td class="center hidden-phone">
						<?php echo $this->escape($item->company); ?>
					</td>

					<!--<td class="center hidden-phone">
						<?php /*echo $this->escape($item->major); */?>
					</td>

					<td class="center hidden-phone">
						--><?php /*echo $this->escape($item->skill); */?>
					</td>

<!-- 					<td class="center hidden-phone">
						<?php //echo $this->escape($item->type); ?>
					</td> -->

					<td class="center hidden-phone">
						<?php echo $this->escape($item->number); ?>
					</td>

			 		<!--<td class="center hidden-phone">
						<?php /*echo $this->escape($item->pname); */?>
					</td>

					<td class="center hidden-phone">
						<?php /*echo $this->escape($item->cname); */?>
					</td>

					<td class="center hidden-phone">
						--><?php /*echo $this->escape($item->qname); */?>
					</td>

					<td class="center hidden-phone">
						<?php echo $this->escape($item->address); ?>
					</td>

					<!--<td class="center hidden-phone">
						<?php
/*						echo '<img src="http://'.$_SERVER['HTTP_HOST'].'/zjxzm/'.$item->picture.'" car="width:70px;"/>';
						*/?>
					</td>

					<td class="center hidden-phone">
						<?php
/*						echo '<img src="http://'.$_SERVER['HTTP_HOST'].'/zjxzm/'.$item->cardfront.'" car="width:70px;"/>';
						*/?>
					</td>

					<td class="center hidden-phone">
						<?php
/*						echo '<img src="http://'.$_SERVER['HTTP_HOST'].'/zjxzm/'.$item->cardback.'" car="width:70px;"/>';
						*/?>
					</td>

					<td class="center hidden-phone">
						<?php
/*						echo '<img src="http://'.$_SERVER['HTTP_HOST'].'/zjxzm/'.$item->cardhand.'" car="width:70px;"/>';
						*/?>
					</td>
					<td class="center hidden-phone">
						<?php
/*						echo '<img src="http://'.$_SERVER['HTTP_HOST'].'/zjxzm/'.$item->license.'" car="width:70px;"/>';
						*/?>
					</td>-->

					<td class="center">
						<?php

						$time = $this->escape($item->lastvisitDate);
						echo date('Y-m-d H:i:s',$time);
						?>
					</td>

					<td class="center hidden-phone"><?php echo $this->escape($item->sellerid); ?></td>
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
