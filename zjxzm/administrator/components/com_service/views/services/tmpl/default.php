<?php
defined('_JEXEC') or die;

$user = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering')); 
$listDirn = $this->escape($this->state->get('list.direction'));

$canDo = ServiceHelper::getActions();

?>
<!--图片效果开始-->
<link href="../administrator/includes/css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../administrator/includes/js/jquery.js"></script>
<script type="text/javascript" src="../administrator/includes/js/style.js"></script>
<!--图片效果结束-->

<form action="<?php echo JRoute::_('index.php?option=com_service&view=services'); ?>" method="post" name="adminForm" id="adminForm">
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
           		<label for="filter_search" class="element-invisible"><?php echo JText::_('按客服人员搜索');?></label>
           		<input type="text" name="filter_search" id="filter_search" 
           			placeholder="<?php echo JText::_('按客服人员搜索'); ?>"
   					value="<?php echo $this->escape($this->state->get('filter.search'));?>" 
   					title="<?php echo JText::_('按客服人员搜索'); ?>" />
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
						<?php echo JHtml::_('grid.sort', '编号', 'a.id', $listDirn, $listOrder); ?>
					</th>

					<th width="1%" class="nowrap center" style="min-width:50px">
						<?php echo JHtml::_('grid.sort', '状态', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th width="20%" class="nowrap center hidden-phone">
            			<?php echo JHtml::_('grid.sort', '公司名称', 'a.bname', $listDirn, $listOrder); ?>
            		</th>

					<th width="20%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '客服人员','a.name', $listDirn, $listOrder); ?>
					</th>

					<th width="20%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '图像','a.picture', $listDirn, $listOrder); ?>
					</th>
					<th width="20%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '号码','a.tel', $listDirn, $listOrder); ?>
					</th>

					<th width="20%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '写入时间','a.addtime', $listDirn, $listOrder); ?>
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
				$canCheckin = $user->authorise('core.manage','com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
				$canChange = $user->authorise('core.edit.state','com_service') && $canCheckin;
				?>
				<tr class="row<?php echo $i % 2; ?>">

					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>


					<td class="center">
						<a href="<?php echo 'index.php?option=com_service&view=service&layout=edit&id='. $this->escape($item->id).'&info=1'?>">
							<?php echo $this->escape($item->id); ?></a>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published',$item->state,$i,'services.',$canChange,'cb',$item->publish_up,$item->publish_down); ?>
					</td>

					<td class="center">
						<?php echo  $this->escape($item->bname);?>
					</td>


					<td class="center">
						<?php echo  $this->escape($item->name);?>
					</td>

					<td class="center hidden-phone">
						<div class="piclist">
							<ul>
								<li class="pic">
									<div class="in">
										<div class="imgdiv">
											<img src="<?php echo "http://".$_SERVER['HTTP_HOST']."/".$item->picture;?>" />
										</div>
									</div>
								</li>
							</ul>
						</div>
					</td>

					<td class="center">
						<?php echo  $this->escape($item->tel);?>
					</td>

					<td class="center">
						<?php echo date('Y-m-d H:i:s',$this->escape($item->addtime)); ?>
					</td>
<!--					<td class="center">-->
<!--						--><?php //echo $this->escape($item->service_clicks); ?>
<!--					</td>-->
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


