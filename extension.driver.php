<?php

	Class Extension_Symphony_Cart extends Extension {
		
		
		/* ================================================ */
		/* ================================================ */
		
		// CONSTANTS / VARIABLES
		
		/* ================================================ */
		/* ================================================ */
		
		
		private $TABLE = 'tbl_symphony_cart';
		private $CONFIG = 'symphony-cart';
		private $COOKIE_PREFIX = 'cart-';
		
		
		
		/**
		 * Returns the cookie prefix
		*/
		public function getCookiePrefix(){
			return $this->COOKIE_PREFIX;
		}
		
		/* ================================================ */
		/* ================================================ */
		
		// INSTALL / UNINSTALL / SYMPHONY
		
		/* ================================================ */
		/* ================================================ */
		
		
		/**
		 * Returns the about information for symphony
		 *
		*/
		public function about() {
			return array(
				'name'			=> 'Symphony Cart',
				'version'		=> '1.0',
				'release-date'	=> '2012-11-18',
				'author'		=> array(
					array(
						'name' => 'David Anderson',
						'website' => 'http://veodesign.co.uk/',
						'email' => 'dave@veodesign.co.uk'
					)
				)
			);
		}
		
		/**
		 * Returns the symphony delegates
		 *
		 *
		*/
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
					'callback' => '__savePreferences'
				),
			);
		}
		
		
		/**
		 *
		 * Installs the extension
		 *
		*/
		public function install() {
		
			// install table
			
			Symphony::Database()->query("CREATE TABLE IF NOT EXISTS $this->TABLE (
					`id` INT(11) unsigned NOT NULL auto_increment,
					`session_id` VARCHAR(32) NULL,
					`state` VARCHAR(32)  NULL,
					`product_id` INT(11) unsigned NOT NULL,
					`quantity` INT(11) unsigned NOT NULL,
					`date_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					PRIMARY KEY  (`id`)
				)");		

			// config options
			Symphony::Configuration()->set('section_id', -1, $this->CONFIG);
			//Symphony::Configuration()->set('price_field_id', -1, $this->CONFIG);
			Administration::instance()->saveConfig();
			
			return true;
		}
		
		
		/**
		 * Uninstalls the extension
		 *
		*/
		public function uninstall() {
			
			try {
				Symphony::Database()->query("DROP TABLE IF EXISTS $this->TABLE");
				
			} catch(Exception $e) { print_r($e); return false; }
			
			Symphony::Configuration()->remove($this->CONFIG);	
		}

		/* ================================================ */
		/* ================================================ */
		
		// DELEGATE CALLBACKS
		
		/* ================================================ */
		/* ================================================ */
		
		
		/**
		 * Adds the preferences fields to the config
		 *
		*/
		public function __appendPreferences($context){
		
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings symphony-cart');
			$group->appendChild(new XMLElement('legend', __('Symphony Cart'))); 
	
			$span = new XMLElement('span', NULL, array('class' => 'frame'));
			
			$sections = SectionManager::fetch();
	
			$options = array();
			foreach ($sections as $s){
				$active = false;
				if($s->get('id') == $this->getConfig('section_id')){
					$active = true;
				}
				$options[] = array(
					$s->get('id'), $active, $s->get('name')
				);
			}
	
			$label = Widget::Label(__('Section'));
			$select = Widget::Select('symphony-cart[section_id]', $options);
			$label->appendChild($select);
			$group->appendChild($label);
		
			
			$context['wrapper']->appendChild($group);
		}
		
		
		/**
		 * Saves the preferences from the config
		 *
		*/
		public function __savePreferences($context){
			
			if(isset($_POST['symphony-cart'])){
				Symphony::Configuration()->set('section_id', $_POST['symphony-cart']['section_id'], $this->CONFIG);
				Symphony::Configuration()->write();	
			}
		}
		
		
		
		
		
		/* ================================================ */
		/* ================================================ */
		
		// PUBLIC FUNCTIONS
		
		/* ================================================ */
		/* ================================================ */
		
		
		/**
		 * Returns the config values
		 *
		 * @param $item - The item to retrieve
		*/
		public function getConfig($item){
			return Symphony::Configuration()->get($item, $this->CONFIG);
		}
		
		
		/**
		 * Used to add an item record to the database
		 *
		 * @param $data(product_id,
		*/
		public function addItemToBasket($session, $product_id,$quantity){
			$sql = "INSERT INTO $this->TABLE (`session_id`,`state`,`product_id`,`quantity`) VALUES(
				'$session','basket',$product_id,$quantity
			);";
			Symphony::Database()->query($sql);
			return true;
		}
		
		
	}

?>