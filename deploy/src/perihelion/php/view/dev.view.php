<?php

final class CodeGeneratorView {

	private $loc;
	private $input;
	private $errors;
	private $messages;

	public function __construct($loc = array(), $input = array(), $errors = array(), $messages = array()) {

		$this->loc = $loc;
		$this->input = $input;
		$this->errors = $errors;
		$this->messages = $messages;

		$role = Auth::getUserRole();
		if ($role != 'siteAdmin') { die("You do not have permissions sufficient to view the dev module."); }

	}

	public function codeGeneratorResults($arg) {

		$codeGenerator = new CodeGenerator($arg);

		$body = '<button type="button" class="btn btn-outline-secondary btn-sm clippy float-right" data-clippable-id="model_code"><span class="far fa-copy"></span></button>';
		$body .= '<pre id="model_code" class="clippable">' . $codeGenerator->compileModelFile() . '</pre>';
		$modelFileCard = new CardView('compile_model_file', array('container-fluid','mb-3'), '', array('col-12'), 'COMPILE MODEL FILE', $body, true, false);

		$body = '<button type="button" class="btn btn-outline-secondary btn-sm clippy float-right" data-clippable-id="view_code"><span class="far fa-copy"></span></button>';
		$body .= '<pre id="view_code" class="clippable">' . $codeGenerator->compileViewFile() . '</pre>';
		$viewFileCard = new CardView('compile_view_file', array('container-fluid','mb-3'), '', array('col-12'), 'COMPILE VIEW FILE', $body, true, true);

		$body = '<button type="button" class="btn btn-outline-secondary btn-sm clippy float-right" data-clippable-id="controller_code"><span class="far fa-copy"></span></button>';
		$body .= '<pre id="controller_code" class="clippable">' . $codeGenerator->compileViewFile() . '</pre>';
		$controllerFileCard = new CardView('compile_controller_file', array('container-fluid','mb-3'), '', array('col-12'), 'COMPILE CONTROLLER FILE', $body, true, true);

		return $modelFileCard->card(); /* . $viewFileCard->card() . $controllerFileCard->card() */

	}

	public function codeGeneratorForm(CodeGeneratorArguments $arg) {

		$keyRows = '';
		foreach ($arg->fieldArray['keys'] AS $keyName => $key) {
			$keyRows .= $this->codeGeneratorFormKeyRow(
				$keyName,
				$key['type'],
				$key['default'],
				$key['default-value'],
				$key['nullable'],
				$key['primary'],
				$key['auto-increment']
			);
		}

		$fieldRows = '';
		foreach ($arg->fieldArray['fields'] AS $fieldName => $field) {
			$fieldRows .= $this->codeGeneratorFormFieldRow(
				$fieldName,
				$field['type'],
				$field['parameter'],
				$field['default'],
				$field['default-value'],
				$field['nullable']
			);
		}

		$form = '
		
			<form method="post">
				<div class="form-row">
					<div class="form-group col-12 col-md-4">
						<label for="moduleName" class="col-form-label">' . Lang::getLang('codeGeneratorModuleName') . '</label>
						<input type="text" class="form-control" name="moduleName" value="' . $arg->moduleName . '">
					</div>
					<div class="form-group col-12 col-md-4">
						<label for="className" class="col-form-label">' . Lang::getLang('codeGeneratorClassName') . '</label>
						<input type="text" class="form-control" name="className" value="' . $arg->className . '">
					</div>
					<div class="form-group col-12 col-md-4">
						<label for="scope" class="col-form-label">' . Lang::getLang('codeGeneratorScope') . '</label>
						<select class="form-control" name="scope">
							<option value="global"' . ($arg->scope=='global'?' selected':'') . '>' . Lang::getLang('codeGeneratorScopeGlobal') . '</option>
							<option value="site"' . ($arg->scope=='site'?' selected':'') . '>' . Lang::getLang('codeGeneratorScopeSite') . '</option>
						</select>
					</div>
				</div>
				<hr />
				KEYS
				<div id="code_generator_form_key_rows" class="table-responsive">
					<table class="table table-sm table-striped table-bordered">
						<thead class="thead-light">
							<th class="text-center">key name</th>
							<th class="text-center">type</th>
							<th class="text-center">default</th>
							<th class="text-center">default value</th>
							<th class="text-center">nullable</th>
							<th class="text-center">primary</th>
							<th class="text-center">auto increment</th>
							<th class="text-center">action</th>
						</thead>
						<tbody>' . $keyRows . '</tbody>
					</table>
				</div>
				<button type="button" id="btn_code_generator_add_key_row" class="btn btn-sm btn-outline-success">ADD KEY</button>
				<hr />
				FIELDS
				<div id="code_generator_form_field_rows" class="table-responsive">
					<table id="code_generator_form_field_rows" class="table table-sm table-striped table-bordered">
						<thead class="thead-light">
							<th>field name</th>
							<th>type</th>
							<th>parameter</th>
							<th>default</th>
							<th>default-value</th>
							<th>nullable</th>
							<th>action</th>
						</thead>
						<tbody>' . $fieldRows . '</tbody>
					</table>
				</div>
				<button type="button" id="btn_code_generator_add_field_row" class="btn btn-sm btn-outline-success">ADD FIELD</button>
				<hr />
				<div class="form-group form-check">
					<input type="checkbox" class="form-check-input" id="extends_orm" name="extendsORM" value="1" ' . ($arg->extendsORM?' checked':'')  . '>
					<label class="form-check-label" for="extends_orm">' . Lang::getLang('codeGeneratorExtendsORM') . '</label>
				</div>
				<div class="form-row">
					<div class="form-group col-12 col-sm-6 offset-sm-6 col-md-4 offset-md-8 col-lg-3 offset-lg-9 col-xl-2 offset-xl-10">
						<button type="submit" name="code-generator-submit" class="btn btn-outline-primary btn-block">' . Lang::getLang('codeGeneratorCompile') . '</button>
					</div>
				</div>
			</form>
			
		';

		$card = new CardView('code_generator_form', array('container-fluid','mb-3'), '', array('col-12'), 'PARAMETERS', $form, false);

		return $card->card();

	}

	public function codeGeneratorFormKeyRow(
		$keyName = null,
		$type = 'int',
		$default = 'NOT NULL',
		$defaultValue = 'zero',
		$nullable = false,
		$primary = true,
		$autoIncrement = true
	) {

		if (is_null($keyName)) { $keyName = 'temp_key_name_' . Utilities::generateUniqueKey(); }

		return '
			<tr>
				<td class="text-center">
					<input type="text" class="form-control form-control-sm" name="keys[' . $keyName . '][keyName]" value="' . $keyName . '">
				</td>
				<td class="text-center">
					' . $this->typeDropdown('keys[' . $keyName . '][type]', $type) . '
				</td>
				<td class="text-center">
					<select name="keys[' . $keyName . '][default]" class="form-control form-control-sm">
						<option value="NOT NULL"' . ($default=='NOT NULL'?' selected':'') . '>NOT NULL</option>
						<option value="NULL"' . ($default=='NULL'?' selected':'') . '>NULL</option>
					</select>
				</td>
				<td class="text-center">
					' . $this->defaultValueDropdown('keys[' . $keyName . '][default-value]', $defaultValue) . '
				</td>
				<td class="text-center">
					<input type="checkbox" name="keys[' . $keyName . '][nullable]" value="true"' . ($nullable?' checked':'') . '>
				</td>
				<td class="text-center">
					<input type="checkbox" name="keys[' . $keyName . '][primary]" value="true"' . ($primary?' checked':'') . '>
				</td>
				<td class="text-center">
					<input type="checkbox" name="keys[' . $keyName . '][auto-increment]" value="true"' . ($autoIncrement?' checked':'') . '>
				</td>
				<td class="text-center">
					<button type="button" class="btn btn-sm btn-outline-danger btn-block delete-row-button"><span class="far fa-trash-alt"></span></button>
				</td>
			</tr>
		';

	}

	public function codeGeneratorFormFieldRow(
		$fieldName = null,
		$type = 'int',
		$parameter = null,
		$default = 'NOT NULL',
		$defaultValue = 'zero',
		$nullable = false
	) {

		if (is_null($fieldName)) { $fieldName = 'temp_field_name_' . Utilities::generateUniqueKey(); }

		return '
			<tr>
				<td class="text-center">
					<input type="text" class="form-control form-control-sm" name="fields[' . $fieldName . '][fieldName]" value="' . $fieldName . '">
				</td>
				<td class="text-center">
					' . $this->typeDropdown('fields[' . $fieldName . '][type]', $type) . '
				</td>
				<td class="text-center">
					<input type="text" class="form-control form-control-sm" name="fields[' . $fieldName . '][parameter]" value="' . $parameter . '">
				</td>
				<td class="text-center">
					<select name="fields[' . $fieldName . '][default]" class="form-control form-control-sm">
						<option value="NOT NULL"' . ($default=='NOT NULL'?' selected':'') . '>NOT NULL</option>
						<option value="NULL"' . ($default=='NULL'?' selected':'') . '>NULL</option>
					</select>
				</td>
				<td class="text-center">
					' . $this->defaultValueDropdown('fields[' . $fieldName . '][default-value]', $defaultValue) . '
				</td>
				<td class="text-center">
					<input type="checkbox" name="fields[' . $fieldName . '][nullable]" value="true"' . ($nullable?' checked':'') . '>
				</td>
				<td class="text-center">
					<button type="button" class="btn btn-sm btn-outline-danger btn-block delete-row-button"><span class="far fa-trash-alt"></span></button>
				</td>
			</tr>
		';

	}

	private function typeDropdown($fieldName, $type) {

		return '
		
			<select name="' . $fieldName . '" class="form-control form-control-sm">
				<option value="int"' . ($type=='int'?' selected':'') . '>int</option>
				<option value="decimal"' . ($type=='decimal'?' selected':'') . '>decimal</option>
				<option value="varchar"' . ($type=='varchar'?' selected':'') . '>varchar</option>
				<option value="datetime"' . ($type=='datetime'?' selected':'') . '>datetime</option>
				<option value="text"' . ($type=='text'?' selected':'') . '>text</option>
			</select>
			
		';

	}

	private function defaultValueDropdown($fieldName, $defaultValue) {

		return '
		
			<select name="' . $fieldName . '" class="form-control form-control-sm">
				<option value="zero"' . ($defaultValue=='zero'?' selected':'') . '>0</option>
				<option value="null"' . ($defaultValue=='null'?' selected':'') . '>null</option>
				<option value="empty-string"' . ($defaultValue=='empty-string'?' selected':'') . '>\'\'</option>
				<option value="current-timestamp"' . ($defaultValue=='current-timestamp'?' selected':'') . '>CURRENT_TIMESTAMP</option>
			</select>
		
		';

	}

}

?>