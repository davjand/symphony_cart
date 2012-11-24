<?php

	if(!defined('__IN_SYMPHONY__')) die('<h2>Error</h2><p>You cannot directly access this file</p>');

	Class eventSymphony_Cart_Action extends Event{

		const ROOTELEMENT = 'symphony-cart-action';
		
		public $currentVersion = 0;
		public $eParamFILTERS = array();
		
		protected $driver;
		
		private $POST_KEY = 'symphony-cart-action';
		

		public static function about(){
			return array(
					 'name' => 'Symphony Cart Action',
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
			return '
				<h3>Allows items to be added to, edited and removed from the cart</h3>
				<p> Use the symphony-cart-action event on your page</p>
				 <pre class="XML"><code>
				&lt;form action=&quot;&quot; method=&quot;post&quot;&gt;
				
					&lt;!-- Quantity is optional, defaults to 1 --&gt;
					
					&lt;input type=&quot;text&quot; name=&quot;symphony-cart-action[quantity]&quot; value=&quot;1&quot;&gt;
					
					&lt;!-- YOU NEED TO SET THE PRODUCTS SECTION IN THE CONFIGURATION FIRST --&gt;
					
					&lt;input name=&quot;symphony-cart-action[product_id]&quot; type=&quot;hidden&quot; value=&quot;{YOUR PRODUCT ID}&quot;&gt;
					
					&lt;input name=&quot;symphony-cart-action[add | edit | remove]&quot; type=&quot;submit&quot; value=&quot;Add to Basket&quot;&gt;
					
				&lt;/form&gt;
				</code></pre>
				
				<h3>Success Response</h3>
				
				<pre class="XML"><code> 
				&lt;events&gt;
					&lt;symphony-cart-action&gt;
						&lt;success&gt;Message&lt;/success&gt;
					&lt;/symphony-cart-action&gt;
				&lt;/events&gt;
				</code></pre>
				
				<h3>Error Response>
				
				 <pre class="XML"><code>
				&lt;events&gt;
					&lt;symphony-cart-action&gt;
						&lt;error&gt;No Product ID Set&lt;/error&gt;
					&lt;/symphony-cart-action&gt;
				&lt;/events&gt;
				</code></pre>
				
				';
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
				$this->driver->addItemToBasket($product_id,$quantity);
				$result->appendChild(new XMLElement('success', "Product Added to Basket"));
				
			}
			//UPDATE
			elseif(isset($submitData['update'])){
				if(!isset($submitData['quantity'])){
					$result->appendChild(new XMLElement('error', "Cannot Update: No Quantity Set"));
					return $result;
				}
				else{
					$quantity = $submitData['quantity'];
					$this->driver->updateItemInBasket($product_id,$quantity);
					$result->appendChild(new XMLElement('success', "Product Quantity Updated"));
				}	
			}
			//REMOVE
			elseif(isset($submitData['remove'])){
				$this->driver->removeItemFromBasket($product_id);
				$result->appendChild(new XMLElement('success', "Product Removed"));
			}
			
			return $result;
		}
	}
