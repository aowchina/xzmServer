<?php
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_service&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<div class="row-fluid">
	<div class="span10 form-horizontal">
		<fieldset>
				<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('客服管理', true)); ?>

				<?php foreach ($this->form->getFieldset('my_fields') as $k=>$field) : ?>
	  				<div class="control-group">
		    			<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php
//								if($field->fieldname=='service_title')
//								{
//									//把下一个指针的内容复制给当前指针
//									if(isset($this->item->id)){
//										$field->value = unserialize(file_get_contents($this->form->getFieldset('my_fields')['jform_service_content']->value))['title'];
//									}
//								}
//								if($field->fieldname=='service_content')
//								{
//									if(isset($this->item->id))
//									{
//										$field->value = unserialize(file_get_contents($field->value))['content'];
//									}
//
//								}
//							//判断是详情还是编辑
//							if(isset($_GET['info']) && $_GET['info']==1){
//								$field->readonly='true';
//							}
							echo $field->input;
							?></div>
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
