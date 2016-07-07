<?php

	class Example {
		
		function __construct() {
			echo 'I am an Example library!';
		}

		public function doSomething() {
			echo mt_rand( 1, 10 );
		}
	}