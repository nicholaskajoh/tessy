<?php
	require_once "tessy.php";

	// instantiate Tessy
	$t = new Tessy();

	// open a db connection
	$config = array(
		"host" => "localhost",
		"dbname" => "tessy",
		"username" => "root",
		"password" => ""
	);

	$t->odb( $config );

	// routing
	$t->addRoute( '/', 'mytests' );
	$t->addRoute( '/home', function() use( $t ) { echo $t->root; echo " Welcome Home!"; } );
	$t->addRoute( '/myajax', 'handleAjax' );
	$t->routeElse = function() { echo "404: Page not Found!"; };
	$t->route();

	function mytests() {
		global $t;
		require_once "tests.php";
	}

	function handleAjax() {
		global $t;
		echo "Received!";
	}

	// close db connection
	$t->cdb();
