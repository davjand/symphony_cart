<?php

	Class extension_multicom_plugin extends Extension {

		public function install() {
		
			// install table
			/*try {
				Symphony::Database()->query('CREATE TABLE IF NOT EXISTS `tbl_symphony_cart` (
						`id` int(11) unsigned NOT NULL auto_increment,
						`field_id` int(11) unsigned NOT NULL,
						PRIMARY KEY  (`id`),
						KEY `field_id` (`field_id`)
				);');
				
			} catch(Exception $e) { print_r($e); return false; }*/			

			// config options
			Symphony::Configuration()->set('mode', 'test', 'symphony-cart');

			
			Administration::instance()->saveConfig();
			return true;
		}

		public function uninstall() {
			
			try {
				Symphony::Database()->query('DROP TABLE IF EXISTS `tbl_symphony_cart`');
				
			} catch(Exception $e) { print_r($e); return false; }
			
			Symphony::Configuration()->remove('symphony-cart');	
		}

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => '__appendPreferences'
				),
				array(
					'page' => '/system/preferences/',
					'delegate' => 'Save',
					'callback' => '__SavePreferences'
				),
			);
		}
		
		//Callbacks
		
		public function __SavePreferences(){
			
		}
		
		public function __appendPreferences(){
			
		}

	}
