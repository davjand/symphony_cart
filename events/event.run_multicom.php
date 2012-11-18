<?php

	Class eventrun_multicom extends Event{

		const ROOTELEMENT = 'symphony-cart';
		
		public $currentVersion = 0;
		public $eParamFILTERS = array();

		public static function about(){
			return array(
					 'name' => 'Run Multicom',
					 'author' => array(
							'name' => 'David Anderson',
							'website' => 'http://veodesign.co.uk',
							'email' => 'dave@veodesign.co.uk'),
					 'version' => '1.0',
					 'release-date' => '2012-11-18T23:37:24+00:00',
					 'trigger-condition' => 'action[symphony-cart]');
		}

		public static function getSource(){
			return false;
		}

		public static function allowEditorToParse(){
			return false;
		}

		public static function documentation(){
			return '';
		}

		public function load(){
			return;
		}
	}
