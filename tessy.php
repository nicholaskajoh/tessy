<?php

/**
 * Tessy PHP Nano Framework
 *
 * Tessy helps you demonstrate, experiment and test code with ease.
 *
 * MIT License
 * 
 * Copyright (c) 2016 Nicholas Terna Kajoh
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * 
 */

class Tessy {

	/**
	 * VARIABLES
	 */

	public $root; // root of project e.g http://example.com/

	public static $c; // db connection (object)

	protected $routes = [];

	public $routeParams = [];

	public $routeElse = "Page Not Found!"; // if specified route does not exist


	/*
	 * METHODS
	 */

	function __construct( $args = [] ) {

		$default = array(
			'root' => '',
			'auth_users' => false,
			'libs_path' => false,
			'libs' => null
		);

		$args = array_merge( $default, $args );

		// project root
		$this->root = $args['root'] . "/";

		// if auth_users is true, start a session
		if( $args['auth_users'] ) session_start();

		//include any libraries
		if( $args['libs'] != null ) {
			foreach( $args['libs'] as $lib ) {
				if( $args['libs_path'] ) $lib = $args['libs_path'] . "/" . $lib;
				require_once $lib . ".php";
			}
		}

	}

	/*
	 * DB connection
	 */

	// open db connection
	public static function odb( $data ) {

		$default = array(
			"host" => "localhost",
			"dbname" => "test",
			"username" => "root",
			"password" => ""
		);

		$data = array_merge( $default, $data );

		try {

			self::$c = new PDO( "mysql:host=" . $data["host"] .";dbname=". $data["dbname"], $data["username"], $data["password"] );
			self::$c->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

		} catch ( PDOException $e ) {

		    print "Error: " . $e->getMessage() . "<br/>";
		    die();

		}
	}

	// close db connection
	public static function cdb() {

		$this->c = null;

	}

	/*
	 * DB query
	 */

	public function create( $table, $data ) {

		foreach ($data as $key => $value)
			$data[$key] = "'$value'";

		// construct query
		$q = "INSERT INTO " . $table . " ( ";
		$q .= implode( ", ", array_keys( $data ) ) . " ) VALUES ( ";
		$q .= implode( ", ", array_values( $data ) ) . " )";

		try {

			$_ = self::$c->prepare( $q );
			$_->execute();

		} catch ( PDOException $e ) {

			echo $q . "<br>" . $e->getMessage();		
		}

	}

	public function read( $table, $columns = false, $other = false ) {

		// construct query
		$q = "SELECT ";
		if( $columns ) $q .= implode( ", ", $columns ) . " "; else $q .= "* ";
		$q .= "FROM " . $table . " ";
		if( $other ) $q .= $other;

		try {

			$_ = self::$c->prepare( $q );
			$_->execute();
			return array( 'data' => $_->fetchAll(), 'count' => $_->rowCount() );

		} catch ( PDOException $e ) {

			echo $q . "<br>" . $e->getMessage();
			
		}

	}

	public function edit( $table, $data, $other ) {

		// construct query
		$q = "UPDATE " . $table . " SET ";
		$temp = [];
		foreach( $data as $key => $value )
			$temp[] = $key . " = " . $value;
		$q .= implode( ", ", $temp );
		$q .= $other;

		try {

			$_ = self::$c->prepare( $q );
			$_->execute();
			return true;

		} catch ( PDOException $e ) {

			echo $q . "<br>" . $e->getMessage();
			return false;
			
		}

	}

	public function delete( $table, $other ) {

		// construct query
		$q = "DELETE FROM " . $table . " ";
		$q .= $other;

		try {

			$_ = self::$c->prepare( $q );
			$_->execute();
			return true;

		} catch ( PDOException $e ) {

			echo $q . "<br>" . $e->getMessage();
			return false;
			
		}

	}

	/*
	 * Data validation and sanitization
	 */

	// validate data using regular expressions or PHP's filter_var()
	public function validate( $data, $type ) {

		/* 
		 * DATA TYPES
		 * username - starts with _ or a-z, alphanumeric chars only, case insensitive, no spaces
		 * name - alphabetic chars, hypens, apostropes only, spaces, case insensitive
		 * email
		 * number - int or float
		 * url
		 */

		switch( $type ) {
			case 'uname':
				return preg_match( '~^[^0-9]\w*$~', trim( $data ) );
			break;

			case 'name':
				// remove any extra white space
				$data = preg_replace( '~\s+~', ' ', $data );

				// make each name any array element
				$data = explode( " ", $data );

				foreach ($data as $name)
					if( !preg_match( '~^[^0-9][-a-zA-Z0-9\'\.]*$~', $name ) ) return FALSE; 
				return TRUE;
			break;

			case 'email':
				return filter_var( $data, FILTER_VALIDATE_EMAIL ) == TRUE;
			break;

			case 'number':
				return filter_var( $data, FILTER_VALIDATE_FLOAT ) == TRUE;
			break;

			case 'url':
				return filter_var( $data, FILTER_VALIDATE_URL ) == TRUE;
			break;
			
			default:
				return FALSE;
			break;
		}

	}

	// sanitize data
	public function sanitize( $data ) {

		/*
		 * 1. Trim
		 * 2. Add slashes
		 * 3. Remove JavaScript tags
		 * 4. Remove CSS tags
		 * 5. Remove HTML tags
		 * 6. Remove HTML comments
		 */

		if( !is_array( $data ) ) $data = [ $data ]; // if $data is a string, put it in an array

		$regexes = array(
			'~<script[^>]*?>.*?</script>~si', // Remove JavaScript tags
			'~<style[^>]*?>.*?</style>~siU', // Remove CSS tags
		    '~<[\/\!]*?[^<>]*?>~si', // Remove HTML tags  
		    '~<![\s\S]*?--[ \t\n\r]*>~' // Remove HTML comments
		);

		$replace_with = "";

		// perform sanitization
		foreach ($data as $key => $value)
			$data[$key] = preg_replace( $regexes, $replace_with, addslashes( trim( $value ) ) );
		
		// if $data has only one element, return as string
		if( count( $data ) == 1 ) return $data[0]; else return $data;
	}

	/*
	 * HTML helpers
	 */

	public function meta( $elements ) {

		foreach ($elements as $meta) {
			echo "<meta ";
			foreach ($meta as $attr => $value)
				echo $attr.'="'.$value.'" ';
			echo ">";
		}

	}

	public function fav( $src = "favicon.ico", $type = "image/x-icon" ) {

		echo '<link rel="icon" type="' . $type . '" href="' . $src . '">';

	}

	public function css( $base, $srcs ) {

		foreach( $srcs as $src )
			echo '<link rel="stylesheet" href="'.$this->root.$base.$src.'.css" > ';

	}

	public function js( $base, $srcs ) {

		foreach( $srcs as $src )
			echo '<script src="'.$this->root.$base.$src.'.js" ></script>';

	}


	/*
	 * Routing
	 */

	public function addRoute( $route, $call_back ) {

		$__ = explode( "/", $route );
		$this->routes[ $__[0] ] = $call_back;

	}

	private function currentRoute() {

		return explode( "/", $_SERVER['QUERY_STRING'] ); // from the url

	}

	public function route() {

		// find matching route and return call back or 404 if there's no match
		if( array_key_exists( $this->currentRoute()[0], $this->routes ) ) {

			$this->routeParams = $this->currentRoute();
			return $this->routes[ $this->currentRoute()[0] ]( $this );

		} else {

			if( is_callable( $this->routeElse ) ) ( $this->routeElse )();
			else echo $this->routeElse;

		}

	}


	/*
	 * Authentication
	 */

	public function auth( $sessions, $redirect_url ) {

		$s = [];
		foreach ($sessions as $key => $value) {
			
			if( $_SESSION[ $key ] == $value ) $s[] = true;
			else $s[] = false;

		}

		// all sessions' data must be correct for a user to be allowed access
		if( in_array( false, $s) ) { 

			$this->redirect_to( $redirect_url );
			return true;

		} else {

			return false;
			
		}

	}

	public function create_session( $id, $data ) {

		$_SESSION[ $id ] = $data;
		return $_SESSION[ $id ];

	}

	/*
	 * AJAX
	 */

	public function ajax( $data, $call_back ) {

		$data_count = [];
		foreach ($data as $var_name) {
			
			if( isset( $_REQUEST[$var_name] ) )
				$$var_name = $_REQUEST[$var_name];

		}

		if( count( $data ) == count( $data_count ) ) {

			$call_back();
			exit;

		}
	}

	/*
	 * File upload
	 */

	public function upload( $input, $file_data ) {

		$default = array(
			'name' => $_FILES[$input]['name'],
			'allowed_formats' => [],
			'size_range' => [ 'min' => false, 'max' => false ],
			'upload_dir' => ''
		);

		$file_data = array_merge( $default, $file_data );

		$upload_errors = [];

		$uf = true; // can file be uploaded?

		// target file
		$target = $upload_dir . $file_data['name'];

		// check if file with same name exists
		if( file_exists( $target ) ) {

			$upload_errors[] = "File already exists!";
			$uf = false;

		}

		// check that file size is within specified range, if range is set
		if( $file_data['size_range']['min'] ) {
			if( $_FILES[$input]['size'] < $file_data['size_range']['min'] ) {
				$upload_errors[] = "File size too small";
				$uf = false;
			}
		}

		if( $file_data['size_range']['max'] ) {
			if( $_FILES[$input]['size'] > $file_data['size_range']['max'] ) {
				$upload_errors[] = "File size too large";
				$uf = false;
			}
		}

		// allowed file formats
		$format = pathinfo( $target, PATHINFO_EXTENSION );

		if( !in_array( $format, $file_data['allowed_formats']) ) {
			$upload_errors[] = "File format not supported";
			$uf = false;
		}


		// upload file
		if( $uf ) {
			if ( move_uploaded_file( $_FILES["file"]["tmp_name"], $target ) ) {
		        return true;
		    } else {
		        return false;
		    }
		}

	}


	/*
	 * Utility
	 */

	// redirect page
	public function redirect_to( $url ) {

		header( "Location: " . $url );
		exit;

	}

}