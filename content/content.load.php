<?php
	/*
	Copyright: Deux Huit Huit 2015
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	require_once(TOOLKIT . '/class.jsonpage.php');

	class contentExtensionGrapejs_FieldLoad extends JSONPage {
		
		/**
		 *
		 * Builds the content view
		 */
		public function view() {

			$id = MySQL::cleanValue($_REQUEST['id']);

			if (!$id) {
				$this->_Result['status'] = Page::HTTP_STATUS_BAD_REQUEST;
				$this->_Result['error'] = 'This page requires an entry id';
				$this->setHttpStatus($this->_Result['status']);
				return;
			}

			try {

				$result = Symphony::Database()->fetchRow("0",
					"
					SELECT *
					FROM sym_entries_data_138
					WHERE entry_id = {$id}
					"
				);

				$json = stripslashes($result['value_json']);
				$html = stripslashes($result['value_html']);

				$this->_Result['gjs-components'] = stripslashes($json);//json_decode($json, true);
				// $this->_Result['gjs-components'] = json_decode($json, true);
				$this->_Result['gjs-html'] = $html;

        		$this->addHeaderToPage('Content-Type', 'application/json; charset=utf-8');
				//output the JSON
				$this->setHttpStatus(Page::HTTP_STATUS_OK);
				return;

			} catch (Exception $ex) {
				$this->_Result['ok'] = false;
				$this->_Result['error'] = $ex->getMessage();
			}
		}
	}