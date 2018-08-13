<?php

	Class extension_grapejs_field extends Extension {

			
		/**
		 * {@inheritDoc}
		 */
		public function uninstall() {
			Symphony::Database()->query("DROP TABLE `tbl_fields_grapejs`");
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function update($previousVersion = false) {
			$status = array();

			// Report status
			if(in_array(false, $status, true)) {
				return false;
			}
			else {
				return true;
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function install() {
			return Symphony::Database()->query("
				CREATE TABLE `tbl_fields_grapejs` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					PRIMARY KEY  (`id`),
					UNIQUE KEY `field_id` (`field_id`)
				) TYPE=MyISAM
			");
		}
			
	}
