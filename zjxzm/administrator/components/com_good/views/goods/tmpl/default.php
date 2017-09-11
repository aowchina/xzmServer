<?php
defined('_JEXEC') or die;

$user = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering')); 
$listDirn = $this->escape($this->state->get('list.direction'));

$canDo = GoodHelper::getActions();

?>

<form action="<?php echo JRoute::_('index.php?option=com_good&view=goods'); ?>" method="post" name="adminForm" id="adminForm">
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
           		<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_GOOD_SEARCH_IN_TITLE');?></label>
           		<input type="text" name="filter_search" id="filter_search" 
           			placeholder="<?php echo JText::_('COM_GOOD_SEARCH_IN_TITLE'); ?>"
   					value="<?php echo $this->escape($this->state->get('filter.search'));?>" 
   					title="<?php echo JText::_('COM_GOOD_SEARCH_IN_TITLE'); ?>" />
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

					<th width="1%" class="nowrap center" style="min-width:50px">
						<?php echo JHtml::_('grid.sort', '状态', 'a.state', $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
            			<?php echo JHtml::_('grid.sort', '编号', 'a.id', $listDirn, $listOrder); ?>
            		</th>

					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '商品名称','a.name', $listDirn, $listOrder); ?>
					</th>

					<th width="5%" class="nowrap center hidden-phone">
            			<?php echo JHtml::_('grid.sort', 'OEM号', "a.oem", $listDirn, $listOrder); ?>
            		</th>

            		<th width="10%" class="nowrap center hidden-phone">
            			<?php echo JHtml::_('grid.sort', '所属商品类别', "c.typeid", $listDirn, $listOrder); ?>
            		</th>

            		<th width="5%" class="nowrap center hidden-phone">
            			<?php echo JHtml::_('grid.sort', '销售价格', "a.price", $listDirn, $listOrder); ?>
            		</th>

<!--

<!--            		<th width="5%" class="nowrap center hidden-phone">-->
<!--            			--><?php //echo JHtml::_('grid.sort', '起止年', "a.syear", $listDirn, $listOrder); ?>
<!--            		</th>-->
<!---->
<!--            		<th width="5%" class="nowrap center hidden-phone">-->
<!--            			--><?php //echo JHtml::_('grid.sort', '终止年', "a.eyear", $listDirn, $listOrder); ?>
<!--            		</th>-->

            		<th width="20%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '日期','a.ustintime', $listDirn, $listOrder); ?>
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
			<?php foreach($this->items as $i => $item) :
				$canCheckin = $user->authorise('core.manage','com_checkin') || $item->checked_out == $user->get('goodid') || $item->checked_out == 0;
				$canChange = $user->authorise('core.edit.state','com_good') && $canCheckin;

				?>

				<tr class="row<?php echo $i % 2; ?>">

					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->goodid); ?>
					</td>

					<td class="center">
						<?php echo JHtml::_('jgrid.published',$item->state,$i,'goods.',$canChange,'cb',$item->publish_up,$item->publish_down); ?>
					</td>

					<td class="center">
							<?php echo $this->escape($item->goodid); ?>
						</a>
					</td>

					<td class="center">
						<a href="<?php echo JRoute::_('index.php?option=com_good&task=good.edit&goodid='.$item->goodid); ?>">
						<?php echo $this->escape($item->name); ?>
					</td>

					<td class="center">
						<?php echo $this->escape($item->oem); ?>
					</td>

					 <td class="center">
						<?php echo $this->escape($item->tname); ?>
					</td>

					<td class="center">
						<?php echo $this->escape($item->price); ?>
					</td>

<!--					<td class="center">-->
<!--						--><?php //echo $this->escape($item->num); ?>
<!--					</td>-->

<!--					<td class="center">-->
<!--						--><?php //echo $this->escape(date('Y-m-d',$item->syear)); ?>
<!--					</td>-->
<!---->
<!--					<td class="center">-->
<!--						--><?php //echo $this->escape(date('Y-m-d'),$item->eyear); ?>
<!--					</td>-->

					<td class="center">
						<?php 
                        	date_default_timezone_set('PRC');
		                	$time = $this->escape($item->addtime);
							echo date('Y-m-d H:i:s',$time); 						
						 ?>
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


