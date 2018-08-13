<?php
	/*
	Copyright: Deux Huit Huit 2015
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	require_once(TOOLKIT . '/class.jsonpage.php');

	class contentExtensionGrapejs_FieldSave extends JSONPage {
		
		/**
		 *
		 * Builds the content view
		 */
		public function view() {
			if ($_SERVER['REQUEST_METHOD'] != 'POST') {
				$this->_Result['status'] = Page::HTTP_STATUS_BAD_REQUEST;
				$this->_Result['error'] = 'This page accepts posts only';
				$this->setHttpStatus($this->_Result['status']);
				return;
			}

			$request_body = file_get_contents('php://input');
			// If you are passing json, then you can do:

			$data = json_decode($request_body);
			// var_dump($data);die;

			$id = MySQL::cleanValue($data->id);
			$css = MySQL::cleanValue($data->{'gjs-css'});
			$assets = MySQL::cleanValue($data->{'gjs-assets'});
			$styles = MySQL::cleanValue($data->{'gjs-styles'});
			$html = MySQL::cleanValue($data->{'gjs-html'});
			$json = MySQL::cleanValue($data->{'gjs-components'});

			// var_dump($id);die;

			try {

				$result = Symphony::Database()->insert(
					array (
						'entry_id' => $id ,
						'value_html' => $html ,
						'value_json' => $json ,
					), 
					'sym_entries_data_138', 
					true
				);
				
				$this->_Result['ok'] = $result;
				$this->_Result['status'] = Page::HTTP_STATUS_OK;
				$this->setHttpStatus($this->_Result['status']);
				return;

			} catch (Exception $ex) {
				$this->_Result['ok'] = false;
				$this->_Result['error'] = $ex->getMessage();
			}
		}
	}