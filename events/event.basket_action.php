<?php

	if(!defined('__IN_SYMPHONY__')) die('<h2>Error</h2><p>You cannot directly access this file</p>');

	Class eventBasket_Action extends Event{

		const ROOTELEMENT = 'basket-actions';
		
		public $currentVersion = 0;
		public $eParamFILTERS = array();
		
		protected $driver;
		protected $cookie;
		protected $session;
		
		private $POST_KEY = 'symphony-cart-action';
		

		public static function about(){
			return array(
					 'name' => 'Basket Action',
					 'author' => array(
							'name' => 'David Anderson',
							'website' => 'http://veodesign.co.uk',
							'email' => 'dave@veodesign.co.uk'),
					 'version' => '1.0',
					 'release-date' => '2012-11-18T23:37:24+00:00',
					 'trigger-condition' => 'symphony-cart-action[add|edit|remove]');
		}

		
		/* ================================== */
		/* Symphony functions */
			
		public static function getSource(){
			return false;
		}

		public static function allowEditorToParse(){
			return false;
		}

		public static function documentation(){
			return '';
		}
		
		
		/**
		 * Called by the page on page load
		 *
		*/
		public function load(){
			
			if(isset($_POST[$this->POST_KEY])){
				return $this->__trigger();
			}
			return;
		}
		
		/** Runs the Event
		 *
		 * 1) Checks for an active session
		 * 2) Works out what action is required
		 * 3) Performs the necessary db update
		 *
		*/
		protected function __trigger(){
			$result = new XMLElement(self::ROOTELEMENT);
			
			//initialise the driver
			if (empty($this->driver)){
				$this->driver = Symphony::ExtensionManager()->create('symphony_cart');
			}
			
			//look for the cookie
			if(is_null($this->cookie)) {
				$this->cookie = new Cookie(
					$this->driver->getCookiePrefix(), TWO_WEEKS, __SYM_COOKIE_PATH__, null, true
				);
			}
			
			//Get the existing session or initialize a new one
			if($this->cookie->get('session',$this->session) == NULL){
				$this->session = uniqid(true);
				$this->cookie->set('session',$this->session);
			}
			else{
				$this->session = $this->cookie->get('session',$this->session);
			}
			
			
			// workout the action
			$submitData = $_POST[$this->POST_KEY];
			
			//check that we have a product id
			if(!isset($submitData['product_id'])){
				$result->appendChild(new XMLElement('error', "No Product ID Set"));
				return $result;
			}
			$product_id = $submitData['product_id'];
			
			//ADD
			if(isset($submitData['add'])){
				//get the quantity
				$quantity = isset($submitData['quantity']) ? $submitData['quantity'] : 1;	
				$this->driver->addItemToBasket($this->session,$product_id,$quantity);
				$result->appendChild(new XMLElement('success', "Product Added to Basket"));
				
			}
			//UPDATE
			elseif(isset($submitData['update'])){
				
			}
			//REMOVE
			elseif(isset($submitData['remove'])){
				
			}
			
			

			
			
			
			return $result;
		}
	}
