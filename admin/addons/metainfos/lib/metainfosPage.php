<?php

class metainfosPage {
	
	static $types = ['text', 'textarea', 'select', 'radio', 'checkbox', 'DYN_LINK', 'DYN_LINK_LIST'];
	
	static public function addType($name) {
		
		self::$types[] = $name;	
		
	}
	
	static public function Backend($name, $pagename, $tablename, $action, $id) {
		
		if($action == 'delete' && dyn::get('user')->hasPerm('metainfos[delete]')) {
			self::delete($tablename, $id);
			echo message::success(lang::get('entry_deleted'));
			$action = '';
		}
		
		if(dyn::get('user')->hasPerm('metainfos[edit]')) {
			
			if(ajax::is()) {
				self::BackendAjax();	
			}	
			
			if($action == 'add' || $action == 'edit' || $action == 'delete') {
				self::BackendFormular($name, $tablename, $action, $id);
			}
		
		}
		
		if($action == '') {
			self::BackendShow($name, $pagename);	
		}
		
		
	}
	
	static protected function BackendAjax() {
		
		$sort = type::post('array', 'array');
	
		$sql = sql::factory();
		$sql->setTable('metainfos');
		foreach($sort as $s=>$id) {
			$sql->setWhere('id='.$id);
			$sql->addPost('sort', $s+1);
			$sql->update();	
		}
		
		ajax::addReturn(message::success(lang::get('save_sorting'), true));
		
	}
	
	static protected function BackendFormular($name, $tablename, $action, $id) {
		
		$prefix = substr($name, 0, 3).'_';
		
		$form = form::factory('metainfos', 'id='.$id, 'index.php');
		
		$field = $form->addRawField($prefix);
		$field->fieldName(lang::get('prefix'));
	
		$field = $form->addTextField('label', $form->get('label'));
		$field->fieldName(lang::get('description'));
		$field->autofocus();
		
		$field = $form->addTextField('name', $form->get('name'));
		$field->fieldName(lang::get('name'));
		
		$field = $form->addSelectField('formtype', $form->get('formtype'));
		$field->fieldName(lang::get('field_type'));
		$field->addAttribute('id', 'formtype');
		foreach(self::$types as $type) {
			$field->add($type, $type);	
		}
		
		$field = $form->addTextField('default', $form->get('default'));
		$field->fieldName(lang::get('default_value'));
		$field->setSuffix('<small>'.lang::get('meta_pre_selection').'</small>');
		
		$style = (in_array($form->get('formtype'), ['select', 'radio', 'checkbox'])) ? 'block' : 'none' ;
		
		$field = $form->addTextareaField('params', $form->get('params'));
		$field->fieldName(lang::get('meta_parameter'));
		$field->setPrefix('<div id="param_info" style="display:'.$style.'">');
		$field->setSuffix(lang::get('examples').':<br />a) all|user|admin<br />b) 1:all|2:user|3:admin</div>');
		
		$field = $form->addTextareaField('attributes', $form->get('attributes'));
		$field->fieldName('HTML-Attribute');
		$field->setSuffix('<small>'.lang::get('examples').':<br /> style=color:red<br />multiple=multiple<br />class=my_css_class</small>');
		
		$form->addHiddenField('type', $name);
		
		if($action == 'edit') {
			$form->addHiddenField('id', $id);
		}
		
		if($form->isSubmit()) {
			
			switch($form->get('formtype')) {
				case 'textarea':
					$type = 'text';
					break;
				default:
					$type = 'VARCHAR(255)';
					break;	
			}
			
			$colum = sql::showColums($tablename, $prefix.$form->get('name'), false);
			$colum->result();
			
			$isRight = function() use($action, $colum, $form, $prefix) {
				
				if($action == 'add' && $colum->num()) {
					return false;
				}				

				if($action == 'edit' && $form->sql->getValue('name') != $form->get('name')) {
					$sql = sql::factory();
					return (bool)!$sql->num('SELECT id FROM '.sql::table('metainfos').' WHERE `name` = "'.$form->get('name').'" AND `type` = "'.$form->get('type').'"');
				}
				
				return true;
			
			};
			
			if($isRight()) {
				
				$sql = sql::factory();
				if($action == 'add') {
					$sql->query('ALTER TABLE '.$tablename.' ADD `'.$prefix.$form->get('name').'` '.$type.' DEFAULT "'.$form->get('default').'" ');
				} else {
					$sql->query('ALTER TABLE '.$tablename.' CHANGE `'.$prefix.$form->sql->getValue('name').'` `'.$prefix.$form->get('name').'` '.$type.' DEFAULT "'.$form->get('default').'" ');
				}

			} else {
				
				$form->setErrorMessage(sprintf(lang::get('col_name_exists'), $prefix.$form->get('name')));
				$form->setSave(false);
				
			}
			
		}
		
		
?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><?php echo lang::get('metainfo_edit'); ?></h3>
			</div>
			<div class="panel-body">
				<?php echo $form->show(); ?>
			</div>
		</div>
	</div>
</div>
<?php
		
		self::jsSelect();
		
	}
	
	static protected function BackendShow($name, $pagename) {
	
		$table = table::factory(['class'=>['js-sort']]);
		$table->setSql('SELECT * FROM '.sql::table('metainfos').' WHERE `type` = "'.$name.'"');
		
		$table->addRow()->addCell()->addCell('Name')->addCell('Aktion');
		
		$table->addCollsLayout('25,*,110');
		
		$table->addSection('tbody');
		
		if($table->numSql()) {
		
			while($table->isNext()) {
				
				$edit = '';
				$delete = '';
				
				if(dyn::get('user')->hasPerm('metainfos[edit]')) {
					$edit = '<a href="'.url::backend('meta', ['subpage'=>$pagename, 'action'=>'edit', 'id'=>$table->get('id')]).'" class="btn btn-sm  btn-default fa fa-pencil-square-o"></a>';
				}
				
				if(dyn::get('user')->hasPerm('metainfos[delete]')) {
					$delete = '<a href="'.url::backend('meta', ['subpage'=>$pagename, 'action'=>'delete', 'id'=>$table->get('id')]).'" class="btn btn-sm btn-danger delete fa fa-trash-o"></a>';
				}
				
				$table->addRow(['data-id'=>$table->get('id')])
				->addCell('<i class="fa fa-sort"></i>')
				->addCell($table->get('name'))
				->addCell('<span class="btn-group">'.$edit.$delete.'</span>');
				
				$table->next();	
				
			}
		
		} else {
		
			$table->addRow()
			->addCell(lang::get('no_entries'), ['colspan'=>3]);
		
		}
		?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title pull-left"><?php echo backend::getSubpageName(); ?></h3>
                <?php
				if(dyn::get('user')->hasPerm('metainfos[edit]')) {
				?>
				<div class="btn-group pull-right">
					<a href="<?php echo url::backend('meta', ['subpage'=>$pagename, 'action'=>'add']); ?>" class="btn btn-sm btn-default"><?php echo lang::get('add'); ?></a>
				</div>
                <?php
				}
				?>
				<div class="clearfix"></div>
			</div>
			<?php echo $table->show(); ?>
		</div>
	</div>
</div>
		<?php
		
	}
	
	static protected function delete($tablename, $id) {
		
		$prefix = substr($tablename, 0, 3).'_';
		
		$sql = sql::factory();
		$sql->setTable('metainfos')->setWhere('`id`='.$id)->select('`name`')->result();
			
		$sql->query('ALTER TABLE '.$tablename.' DROP `'.$prefix.$sql->get('name').'`');
		
		$sql->delete();
		
	}
	
	static protected function jsSelect() {
		
		layout::addJSCode('$("#formtype").change(function() {
			var value = $(this).val()  
			if(value == "radio" || value == "checkbox" || value == "select") {
				$("#param_info").slideDown();
			} else {
				$("#param_info").slideUp();
			}
			
		});');
			
	}
	
}

?>