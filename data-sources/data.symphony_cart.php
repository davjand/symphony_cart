<?php
	
	require_once(TOOLKIT . '/class.datasource.php');

	Class datasourcesymphony_cart extends Datasource{

		public $dsParamROOTELEMENT = 'symphony-cart';
		
		protected $driver;
		
		private $session;
		
		
		/**
		 * Constructor
		*/
		public function __construct(array $env = null, $process_params=true){
			parent::__construct($env, $process_params);
		}

		/**
		 * Symphony About Param
		 *
		*/
		public function about(){
			return array(
				'name' => 'Symphony Cart: Cart',
				'version' => '1.0',
				'release-date' => '2012-11-12',
				'author' => array(
					'name' => 'David Anderson',
					'website' => 'http://veodesign.co.uk/',
					'email' => 'dave@veodesign.co.uk'
				)
			);
		}
		
		
		/**
		 * Retrieve the XML and Pool Parameters
		 *
		*/
		public function grab(array &$param_pool=NULL){
		
			$xmlResult = new XMLElement($this->dsParamROOTELEMENT);
			
			//initialise the driver
			if (empty($this->driver)){
				$this->driver = Symphony::ExtensionManager()->create('symphony_cart');
			}
			$this->session = $this->driver->initCartSession();
			
			//Build & Executre the SQL
			$table = $this->driver->getDatabaseTable();
			$session = $this->session;
			$sql = "SELECT * FROM $table WHERE `session_id`='$session' AND `state`='basket'";
			
			$results = Symphony::Database()->fetch($sql);
			
			
			
			//Parse the results into XML / Param Pool
			if(is_array($results) && !empty($results)){
				$params = array();
				
				foreach($results as $result){
				
					$params[] = $result['product_id'];
					$xml = new XMLElement('item', '', array(
						'product'=> $result['product_id'],
						'quantity'=>$result['quantity'],
					));
					$xmlResult->appendChild($xml);
					
				}
				$param_pool['symphony-cart']=$params;	
			}
			
			return $xmlResult;
		}

	}
