<?php
defined('_JEXEC') or die;

$user = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering')); 
$listDirn = $this->escape($this->state->get('list.direction'));

?>

<form action="<?php echo JRoute::_('index.php?option=com_wallet&view=wallets'); ?>" method="post" name="adminForm" id="adminForm">
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
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_SERVICE_SEARCH_IN_TITLE');?></label>
				<input type="text" name="filter_search" id="filter_search"
					   placeholder="按用户姓名搜索"
					   value="<?php echo $this->escape($this->state->get('filter.search'));?>"
					   title="<?php echo JText::_('COM_SERVICE_SEARCH_IN_TITLE'); ?>" />
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
						<?php echo JHtml::_('grid.sort', '用户姓名','b.name', $listDirn, $listOrder); ?>
					</th>

					<th width="15%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '手机号','b.tel', $listDirn, $listOrder); ?>
					</th>

					<th width="15%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '用户类别','a.tid', $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '余额','a.money', $listDirn, $listOrder); ?>
					</th>

					<th width="15%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '日期','a.addtime', $listDirn, $listOrder); ?>
					</th>

					<th width="1%" class="nowrap center hidden-phone">
            			<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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

					<td class="center hidden-phone">

						<?php echo $item->aname ? $this->escape($item->aname) : $this->escape($item->sname); ?>
					</td>

					<td class="center hidden-phone">
						<?php echo $item->atel ? $this->escape($item->atel) : $this->escape($item->stel); ?>
					</td>

					<td class="center hidden-phone">
						<?php
						$type = $this->escape($item->tid);
						if($type==1){

							echo "买家";
						}else{

							echo "卖家";
						}
						 ?>
					</td>
					

					<td class="center hidden-phone">
						<?php echo $this->escape($item->money).'元'; ?>
					</td>

					<td class="center hidden-phone"><?php echo date("Y-m-d H:i:s", $this->escape($item->addtime)); ?></td>
					<td class="center hidden-phone"><?php echo $this->escape($item->id); ?></td>
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
