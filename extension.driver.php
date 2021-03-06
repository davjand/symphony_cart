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
		private $STATE_BASKET = 'basket';
				
		private $cookie = NULL;
		private $session = NULL;
		
		
		/**
		 * Returns the current cookie
		*/
		public function getCookie(){
			return $this->cookie;
		}
		
		/**
		 * Get the database table
		 *
		*/
		public function getDatabaseTable(){
			return $this->TABLE;
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
			
			Symphony::Database()->query("DROP TABLE IF EXISTS $this->TABLE");
			
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
		 * Returns the ID of the currently active session
		 * Creates a new session / cookie if needed
		 *
		 * Returns the session
		*/
		public function initCartSession(){
			
			if(is_null($this->session)){
				
				//look for the cookie
				if(is_null($this->cookie)) {
					$this->cookie = new Cookie(
						$this->COOKIE_PREFIX, TWO_WEEKS, __SYM_COOKIE_PATH__, null, true
					);
				}
				
				//Get the existing session or initialize a new one
				if($this->cookie->get('session') == NULL){
					$this->session = uniqid(true);
					$this->cookie->set('session',$this->session);
				}
				else{
					$this->session = $this->cookie->get('session',$this->session);
				}
			}
			return $this->session;
		}
		
		
		/**
		 * Used to add an item record to the database
		 *
		 * @param $product_id
		 * @param $quantity
		*/
		public function addItemToBasket($product_id,$quantity){
			$session = $this->initCartSession();
			
			//see if in basket
			$existingProduct = $this->productInBasket($product_id);
			
			if($existingProduct != NULL){
				$this->updateItemInBasket($product_id,$quantity + $existingProduct['quantity']);	
			}
			else{
				//clean all the values
				$product_id = Symphony::Database()->cleanValue($product_id);
				$quantity = Symphony::Database()->cleanValue($quantity);
				$session = Symphony::Database()->cleanValue($session);
	
				$sql = "INSERT INTO $this->TABLE (`session_id`,`state`,`product_id`,`quantity`) VALUES(
					'$session','basket',$product_id,$quantity
				);";
				Symphony::Database()->query($sql);
				return true;	
			}
		}
		
		
		/**
		 * Updates the database with a new quantity for the given item
		 *
		 * @param $product_id
		 * @param $quantity
		 *
		*/
		public function updateItemInBasket($product_id,$quantity){
			$session = $this->initCartSession();
			
			if($this->productInBasket($product_id) != NULL){
			
				//clean all the values
				$product_id = Symphony::Database()->cleanValue($product_id);
				$quantity = Symphony::Database()->cleanValue($quantity);
				$session = Symphony::Database()->cleanValue($session);
				
				$sql = "UPDATE $this->TABLE SET `quantity`=$quantity WHERE `session_id`='$session' AND `product_id`=$product_id";
				
				Symphony::Database()->query($sql);
				return true;
			}
		}
		
		
		/**
		 * Removes an item from the basket
		 *
		 * @param product_id
		 *
		*/
		public function removeItemFromBasket($product_id){
			$session = $this->initCartSession();
			
			$product_id = Symphony::Database()->cleanValue($product_id);
			$session = Symphony::Database()->cleanValue($session);
			
			$sql = "DELETE FROM $this->TABLE WHERE `product_id`=$product_id AND `session_id`='$session' AND `state`='$this->STATE_BASKET'";
			Symphony::Database()->query($sql);
			return true;
		}
		

		/**
		 * Checks if the given product is in the basket
		 *
		 * @param product_id
		 *
		 * Returns the product array if the product is currently in the basket
		 * Returns NULL if not
		*/
		public function productInBasket($product_id){
			$session = $this->initCartSession();
			
			//clean all the values
			$product_id = Symphony::Database()->cleanValue($product_id);
			$session = Symphony::Database()->cleanValue($session);
			
			$sql = "SELECT * FROM $this->TABLE WHERE `product_id`=$product_id AND `session_id`='$session' AND `state`='$this->STATE_BASKET'";
			
			$result = Symphony::Database()->fetchRow(0,$sql);
			
			if(count($result)>0){
				return $result;
			}
			return NULL;
		}
		
		
	}

?>