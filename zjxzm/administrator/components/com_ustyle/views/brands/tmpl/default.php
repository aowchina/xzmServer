<?php
defined('_JEXEC') or die;

$user = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering')); 
$listDirn = $this->escape($this->state->get('list.direction'));

$canDo = UstyleHelper::getActions();

?>
<link href="../administrator/includes/css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../administrator/includes/js/jquery.js"></script>
<script type="text/javascript" src="../administrator/includes/js/style.js"></script>

<form action="<?php echo JRoute::_('index.php?option=com_ustyle&view=brands'); ?>" method="post" name="adminForm" id="adminForm">
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
           		<label for="filter_search" class="element-invisible"><?php echo JText::_('按品牌名称搜索');?></label>
           		<input type="text" name="filter_search" id="filter_search" 
           			placeholder="<?php echo JText::_('按品牌名称搜索'); ?>"
   					value="<?php echo $this->escape($this->state->get('filter.search'));?>" 
   					title="<?php echo JText::_('按品牌名称搜索'); ?>" />
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

					<th width="5%" class="nowrap center hidden-phone">
            			<?php echo JHtml::_('grid.sort', '编号', 'a.brandid', $listDirn, $listOrder); ?>
            		</th>

					<th width="20%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '品牌名称','a.bname', $listDirn, $listOrder); ?>
					</th>

					<th width="20%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '品牌首字母','a.fname', $listDirn, $listOrder); ?>
					</th>

					<th width="20%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '品牌图标','a.blogo', $listDirn, $listOrder); ?>
					</th>

					<th width="20%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '日期','a.addtime', $listDirn, $listOrder); ?>
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

			$canCheckin = $user->authorise('core.manage','com_checkin') || 
				$item->checked_out == $user->get('brandid') || $item->checked_out == 0;

        	$canChange  = $user->authorise('core.edit.state', 'com_ustyle') && $canCheckin;

			?>
				<tr class="row<?php echo $i % 2; ?>">

				    <td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->brandid); ?>
					</td> 


                    <td class="center hidden-phone"><?php echo $this->escape($item->brandid); ?></td>

					<td class="center">
						<?php echo $this->escape($item->bname); ?>
					</td>

					<td class="center">
						<?php echo $this->escape($item->fname); ?>
					</td>

					<td class="center hidden-phone">
						<div class="piclist">
							<ul>
								<li class="pic">
									<div class="in">
										<div class="imgdiv">
											<img src="<?php echo "http://".$_SERVER['HTTP_HOST']."/".$item->blogo;?>" />
										</div>
									</div>
								</li>
							</ul>
						</div>
					</td>

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