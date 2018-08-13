<?php

	/**
	 * @package textboxfield
	 */

	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');

	require_once TOOLKIT . '/class.xsltprocess.php';
	require_once EXTENSIONS . '/textboxfield/extension.driver.php';

	/**
	 * An enhanced text input field.
	 */
	class FieldGrapejs extends Field {
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/

		public function __construct() {
			parent::__construct();

			$this->_name = 'Grape JS';
			$this->_required = false;

			// Set defaults:
			$this->set('show_column', 'no');
			$this->set('required', 'no');
		}

		public function createTable() {
			$field_id = $this->get('id');

			return Symphony::Database()->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`entry_id` INT(11) UNSIGNED NOT NULL,
					`value_html` TEXT DEFAULT NULL,
					`value_json` TEXT DEFAULT NULL,
					PRIMARY KEY (`id`),
					KEY `entry_id` (`entry_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			");
		}

		public function allowDatasourceOutputGrouping() {
			return false;
		}

		public function allowDatasourceParamOutput() {
			return false;
		}

		public function canFilter() {
			return false;
		}

		public function canPrePopulate() {
			return false;
		}

		public function isSortable() {
			return false;
		}

	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/


		protected function repairEntities($value) {
			return preg_replace('/&(?!(#[0-9]+|#x[0-9a-f]+|amp|lt|gt);)/i', '&amp;', trim($value));
		}

	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/

		public function displaySettingsPanel(XMLElement &$wrapper, $errors = null) {
			parent::displaySettingsPanel($wrapper, $errors);

			$order = $this->get('sortorder');


			/*---------------------------------------------------------------------
				Core options
			---------------------------------------------------------------------*/

			$columns = new XMLElement('div');
			$columns->setAttribute('class','two columns');
			$this->appendRequiredCheckbox($columns);
			$this->appendShowColumnCheckbox($columns);
			$wrapper->appendChild($columns);
		}

		public function commit() {
			if (!parent::commit()) return false;

			$id = $this->get('id');

			if ($id === false) return false;

			$fields = array(
				'field_id'			=> $id
			);

			return FieldManager::saveSettings($id, $fields);
		}

	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/

		public function displayPublishPanel(XMLElement &$wrapper, $data = null, $flagWithError = null, $fieldnamePrefix = null, $fieldnamePostfix = null, $entry_id = null) {


			$page = Administration::instance()->Page;

			// grape JS files
			// $page->addStylesheetToHead('https://unpkg.com/grapesjs/dist/css/grapes.min.css', 'screen');
			// $page->addScriptToHead('https://unpkg.com/grapesjs');

			$blocks = file_get_contents(WORKSPACE . '/grapejs/blocks.json');
			$canvas = file_get_contents(WORKSPACE . '/grapejs/canvas.json');

			$jsCode = "Symphony.grapejs = {'blocks':{$blocks},'canvas':{$canvas}};";

			// var_dump($jsCode);die;

			$page->addElementToHead(
				new XMLElement(
					'script', 
					$jsCode, 
					array(
						'type' => 'text/javascript'
					)
				)
			);


			// our custom css/js
			$page->addStylesheetToHead(URL . '/extensions/grapejs_field/assets/grapes.min.css', 'screen');
			$page->addStylesheetToHead(URL . '/extensions/grapejs_field/assets/grapejs_field.publish.css', 'screen');
			$page->addScriptToHead(URL . '/extensions/grapejs_field/assets/grapes.js');
			$page->addScriptToHead(URL . '/extensions/grapejs_field/assets/grapejs_field.publish.js');


			$label = Widget::Label($this->get('label'));
				
			$input = '
				<div class="panel__top">
				   	<div class="panel__basic-actions"></div>
				   	<div class="panel__switcher"></div>
				</div>
				<div class="editor-row">
				  	<div class="editor-canvas">
						<div class="panel__right">
						    <div class="layers-container"></div>
						    <div class="styles-container"></div>
						    <div class="traits-container"></div>
							<div id="blocks"></div>
						</div>
						<div id="gjs">
							<h1> Hello World Component!</h1> 
						</div>
					</div>
				</div>
				<input id="grapejs-html" type="hidden" name="html"/>
				<input id="grapejs-json" type="hidden" name="json"/>';

			if (is_null($label)) return;

			$label->appendChild($input);

			if ($flagWithError != null) {
				$label = Widget::Error($label, $flagWithError);
			}

			$wrapper->appendChild($label);
		}

	/*-------------------------------------------------------------------------
		Input:
	-------------------------------------------------------------------------*/


		public function checkPostFieldData($data, &$message, $entry_id = null) {
			$length = (integer)$this->get('text_length');
			$message = null;

			if ($this->get('required') == 'yes' and strlen(trim($data)) == 0) {
				$message = __(
					"'%s' is a required field.", array(
						$this->get('label')
					)
				);

				return self::__MISSING_FIELDS__;
			}

			if (empty($data)) self::__OK__;


			return self::__OK__;
		}

		public function processRawFieldData($data, &$status, &$message = null, $simulate = false, $entry_id = null) {
			$status = self::__OK__;

			return $data;
		}

	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/

		public function fetchIncludableElements() {
			return array(
				$this->get('element_name') . ': json',
				$this->get('element_name') . ': html'
			);
		}

		public function appendFormattedElement(XMLElement &$wrapper, $data, $encode = false, $mode = null, $entry_id = null) {
			if(is_null($data['value_html'])) return;

			if ($mode == 'json') {
				$value = '<![CDATA['  .trim($data['value_json']). ']]>';
			}

			else {
				$mode = 'html';

				$value = $this->repairEntities(trim(stripslashes($data['value_html'])));
			}




			$attributes = array();

			$wrapper->appendChild(new XMLElement(
				$this->get('element_name'), $value, $attributes
			));
		}
	}
