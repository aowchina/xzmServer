<?php
defined('_JEXEC') or die;

//JFactory::getDocument()->addScriptDeclaration($script);

$db = JFactory::getDbo();
$id = $this->item->goodid;

$group = $db->loadAssoc();
$sql = "select goodid, name,img from #__good where goodid='$id'";

$db->setQuery($sql);
$goods_list = $db->loadAssocList();
//var_dump($goods_list);
$imgs = $goods_list[0]['img'];
$imgs=explode(',',$imgs);

foreach($imgs as $k=> &$v){

	$v = trim($v,'"[ ]"');
}

//var_dump($imgs);
?>
<link href="../administrator/includes/css/style.css" rel="stylesheet" type="text/css" />
<!-- <script type="text/javascript" src="../administrator/includes/js/jquery.js"></script> -->
<script type="text/javascript" src="../administrator/includes/js/style.js"></script>

<form action="<?php echo JRoute::_('index.php?option=com_good&layout=edit&goodid='.$this->item->goodid); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<div class="row-fluid">
	<div class="span10 form-horizontal">
		<fieldset>
				<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('商品信息', true)); ?>

				<?php foreach ($this->form->getFieldset('my_fields') as $field) : ?>
	  				<div class="control-group">
		    			<div class="control-label"><?php echo $field->label; ?> </div>

		    			<div class="controls"><?php echo $field->input; ?> </div>
	             	</div>
           		<?php endforeach; ?>
           		<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'more', JText::_('商品图片详情', true)); ?>
			<table class="table table-striped" id="eventList">
				<thead>
				<tr>
					<th width="10%" class="nowrap center hidden-phone">
						<?php echo JText::_('商品标识'); ?>
					</th>

					<th width="20%" class="nowrap center hidden-phone">
						<?php echo JText::_('商品名称'); ?>
					</th>

					<th width="50%" class="nowrap center hidden-phone">
						<?php echo JText::_('商品图片'); ?>
					</th>

				</tr>
				</thead>

<!--				--><?php //foreach ($this->form->getFieldset('my_list') as $field) : ?>
<!--					<div class="control-group">-->
<!--						<div class="control-label">--><?php //echo $field->label; ?><!-- </div>-->
<!---->
<!--						<div class="controls">--><?php //echo $field->input; ?><!-- </div>-->
<!--					</div>-->
<!--				--><?php //endforeach; ?>

				<tbody>
				<?php
				foreach($goods_list as $i => $item) :

					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center hidden-phone">
							<?php echo $this->escape($item['goodid']); ?>
						</td>

						<td class="center hidden-phone">
							<?php echo $this->escape($item['name']); ?>
						</td>



						<td class="center hidden-phone">
							<div class="piclist">
								<ul>
									<li class="pic">
										<div class="in">
											<div class="imgdiv">
							<?php
							$imgs=$this->escape($item['img']);
							$imgs = $goods_list[0]['img'];
							$imgs=explode(',',$imgs);

							foreach($imgs as $k=> &$v){

								$v = trim($v,'"[ ]"');
								echo '<img src="http://'.$_SERVER['HTTP_HOST'].'/'.$v.'" car="width:30px;display:inline"/>';
							}
							?>
											</div>
										</div>
									</li>
								</ul>
							</div>
						</td>

					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>



           		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>
		</fieldset>




	</div>
</div>
</form>


