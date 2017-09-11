<?php
defined('_JEXEC') or die;

$user = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering')); 
$listDirn = $this->escape($this->state->get('list.direction'));

$canDo = EpcHelper::getActions();

?>

<form action="<?php echo JRoute::_('index.php?option=com_epc&view=oems'); ?>" method="post" name="adminForm" id="adminForm">
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
           		<label for="filter_search" class="element-invisible"><?php echo JText::_('按OEM号搜索');?></label>
           		<input type="text" name="filter_search" id="filter_search" 
           			placeholder="<?php echo JText::_('按OEM号搜索'); ?>"
   					value="<?php echo $this->escape($this->state->get('filter.search'));?>" 
   					title="<?php echo JText::_('按OEM号搜索'); ?>" />
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

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>

					<th width="20%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '所属EPC结构图','a.epcid', $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'OEM号','a.oem', $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '名称','a.name', $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '销售价格', "a.price", $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '销售限价', "a.hprice", $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '单位','a.danwei', $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '单量', "a.num", $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '位置','a.position', $listDirn, $listOrder); ?>
					</th>


					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '起止年', "a.syear", $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '终止年', "a.eyear", $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '选装', "a.xzhuang", $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'lou级替换关系', "a.loureplace", $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'lou级新件号', "a.lounewjian", $listDirn, $listOrder); ?>
					</th>

					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '备注', "a.note", $listDirn, $listOrder); ?>
					</th>

				</tr>
			</thead>

			<tfoot>
				<tr>
					<td colspan="5">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>

			<tbody>
			<?php foreach($this->items as $i => $item) : ?>
				<tr class="row<?php echo $i % 2; ?>">

					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>

					<td class="center hidden-phone"><?php echo $this->escape($item->id); ?></td>

					<td class="center hidden-phone" width="10%">
						<?php echo $this->escape($item->epcname); ?>
					</td>

					<td class="center hidden-phone" width="10%">
						<?php echo $this->escape($item->oem); ?>
					</td>

					<td class="center hidden-phone" width="10%">
							<?php echo $this->escape($item->name); ?>
					</td>

					<td class="center hidden-phone" width="10%">
						<?php echo $this->escape($item->price); ?>
					</td>

					<td class="center hidden-phone" width="10%">
						<?php echo $this->escape($item->hprice); ?>
					</td>

					<td class="center hidden-phone" width="10%">
						<?php echo $this->escape($item->danwei); ?>
					</td>

					<td class="center hidden-phone" width="10%">
						<?php echo $this->escape($item->num); ?>
					</td>

					<td class="center hidden-phone" width="10%">
						<?php echo $this->escape($item->position); ?>
					</td>
					<td class="center hidden-phone" width="10%">
						<?php echo $this->escape($item->syear); ?>
					</td>
					<td class="center hidden-phone" width="10%">
						<?php echo $this->escape($item->eyear); ?>
					</td>

					<td class="center hidden-phone" width="10%">
							<?php echo $this->escape($item->xzhuang); ?>
					</td>

					<td class="center hidden-phone" width="10%">
							<?php echo $this->escape($item->loureplace); ?>
					</td>

					<td class="center hidden-phone" width="10%">
						<?php echo $this->escape($item->lounewjian); ?>
					</td>

					<td class="center hidden-phone" width="10%">
						<?php echo $this->escape($item->note); ?>
					</td>

<!--					<td class="center hidden-phone">--><?php //echo $this->escape($item->id); ?><!--</td>-->
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
