<?php

defined('_JEXEC') or die;

 $script = "
 jQuery(document).ready(function ($){
 	var pid = document.getElementById('jform_pid').value;
 	getOp(pid, 1);

 	$('#jform_pid').change(function(){
 		var pid = $(this).val();
 		getOp(pid, 1);
 	});

 	function getOp(parentid, tag){
 		$.ajax({
 			url: 'index.php?option=com_appuser&task=appusers.getOp&pid=' + parentid,
 			dataType: 'json'
 		}).done(function(data) {
 			setOp(tag, data);
 		});
 	};

 	function setOp(tag, data){
 		if(tag == 1){
 			$('#jform_cid option').each(function() {
 				if ($(this).val() != '1') {
 					$(this).remove();
 				}
 			});

 			$.each(data, function (i, val) {
 				var option = $('<option>');
 				option.text(val.name).val(val.id);

 				if(".$this->item->cid." == val.id){
 					option.attr('selected',true);
 				}

 				$('#jform_cid').append(option);
 			});
 			$('#jform_cid').trigger('liszt:updated');

 			var cid = document.getElementById('jform_cid').value;
 			getOp(cid, 2);

 			$('#jform_cid').change(function(){
 				var cid = $(this).val();
 				getOp(cid, 2);
 			});
 		}
 		else{
 			$('#jform_qid option').each(function() {
 				if ($(this).val() != '1') {
 					$(this).remove();
 				}
 			});

 			$.each(data, function (i, val) {
 				var option = $('<option>');
 				option.text(val.name).val(val.id);

 				if(".$this->item->qid." == val.id){
 					option.attr('selected',true);
 				}

 				$('#jform_qid').append(option);
 			});
 			$('#jform_qid').trigger('liszt:updated');
 		}
 	}
 });
 ";

JFactory::getDocument()->addScriptDeclaration($script);

?>

<form action="<?php echo JRoute::_('index.php?option=com_appuser&layout=edit&appuserid='.(int)$this->item->appuserid); ?>" 
	method="post" name="adminForm" id="adminForm" class="form-validate">

	<div class="row-fluid">
		<div class="span10 form-horizontal">

			<fieldset>
				<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('基本信息', true)); ?>

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

