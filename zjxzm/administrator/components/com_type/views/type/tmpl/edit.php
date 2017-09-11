<?php
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_type&layout=edit&typeid='.(int) $this->item->typeid); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<div class="row-fluid">
	<div class="span10 form-horizontal">
		<fieldset>
				<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('商品类别', true)); ?>

				<?php foreach ($this->form->getFieldset('my_fields') as $field) : ?>
	  				<div class="control-group">
		    			<div class="control-label"><?php echo $field->label; ?></div>
		    			<div class="controls"><?php echo $field->input; ?></div>
	             	</div>
           		<?php endforeach; ?>

           		<?php echo JHtml::_('bootstrap.endTab'); ?>
           		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</div>
</div>
</form>
