<?php
defined('_JEXEC') or die;
$script = "
jQuery(document).ready(function ($){
		var tid = document.getElementById('jform_parentid').value;
		getType(tid);
		$('#jform_parentid').change(function(){

		var tid = $(this).val();

		getType(tid);
		});

	function getType(tid){
		$.ajax({
			url: 'index.php?option=com_pt&task=pts.getTp&tid=' + tid,
			dataType: 'json'
		}).done(function(data) {
		$('#jform_tpid option').each(function() {
					$(this).remove();
			});
			$.each(data, function (i, val) {

				var option = $('<option>');
				option.text(val.name).val(val.id);
				if(".$this->item->id." == val.id){
					option.attr('selected',true);
				}
				$('#jform_tpid').append(option);
			});
	$('#jform_tpid').trigger('liszt:updated');
		});
	};

});
";

JFactory::getDocument()->addScriptDeclaration($script);
?>
<form action="<?php echo JRoute::_('index.php?option=com_pt&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<div class="row-fluid">
	<div class="span10 form-horizontal">
		<fieldset>
				<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('配件分类', true)); ?>

				<?php foreach ($this->form->getFieldset('my_fields') as $k=>$field) : ?>
	  				<div class="control-group">
		    			<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input;?></div>
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
