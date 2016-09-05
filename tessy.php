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

	# VARIABLES
	public static $c; // db connection (object)
	protected $routes = []; // app routes (e.g of route in example.com/blog/1/hello == blog)
	public $route_params = []; // route parameters (e.g of route parameters in example.com/blog/1/hello == 1, hello)
	public $route_else = "<h2 style=\"text-align: center; font-family: Arial; padding-top: 50px;\">Error (404): Page Not Found!</h2"; // if specified route does not exist, return 404
	public $upload_errors = []; // file upload errors e.g wrong fie format, too small/large file size etc

	# METHODS
	/**
	 * DB connection
	 */

	// open db connection
	public static function odb( $data ) {
		$default = array(
			"host" => "localhost",
			"dbname" => "",
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
		self::$c = NULL;
	}

	/**
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

	public function read( $table, $columns = NULL, $clauses = NULL ) {
		// construct query
		$q = "SELECT ";
		if( $columns != NULL ) $q .= implode( ", ", $columns ) . " "; else $q .= "* ";
		$q .= "FROM " . $table . " ";
		if( $clauses != NULL ) $q .= $clauses;

		try {
			$_ = self::$c->prepare( $q );
			$_->execute();
			// strip slashes
			$results = $_->fetchAll();
			foreach( $results as $index => $result ) {
				foreach( $result as $column => $field )
					$result[$column] = stripslashes( $field );
				$results[$index] = $result;
			}
			// return result
			return array( 'data' => $results, 'count' => $_->rowCount() );
		} catch ( PDOException $e ) {
			echo $q . "<br>" . $e->getMessage();	
		}
	}

	public function edit( $table, $data, $clauses ) {
		// construct query
		$q = "UPDATE " . $table . " SET ";
		$temp = [];
		foreach( $data as $key => $value )
			$temp[] = "$key = '$value'";
		$q .= implode( ", ", $temp );
		$q .= " " . $clauses;

		try {
			$_ = self::$c->prepare( $q );
			$_->execute();
			return TRUE;
		} catch ( PDOException $e ) {
			echo $q . "<br>" . $e->getMessage();
			return FALSE;
		}
	}

	public function delete( $table, $clauses ) {
		// construct query
		$q = "DELETE FROM " . $table . " ";
		$q .= $clauses;
		try {
			$_ = self::$c->prepare( $q );
			$_->execute();
			return TRUE;
		} catch ( PDOException $e ) {
			echo $q . "<br>" . $e->getMessage();
			return FALSE;			
		}
	}

	/**
	 * Data validation and sanitization
	 */

	// validate data using regular expressions or PHP's filter_var()
	public function validate( $data, $type ) {
		/** 
		 * DATA TYPES
		 * username - starts with _ or a-z, alphanumeric chars only, case insensitive, no spaces
		 * name - alphabetic chars, hyphens, apostrophes only, spaces, case insensitive
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
					if( !preg_match( '~^[^0-9][-a-zA-Z\'\.]*$~', $name ) ) return FALSE; 
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
		/**
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
		
		// if $data has only one element, return as string, else return array
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
		echo "<link rel=\"icon\" type=\"$type\" href=\"/$src\" >";
	}

	public function css( $base, $srcs ) {
		foreach( $srcs as $src )
			echo "<link rel=\"stylesheet\" href=\"/$base$src.css\" >";
	}

	public function js( $base, $srcs ) {
		foreach( $srcs as $src )
			echo "<script src=\"/$base$src.js\" ></script>";
	}


	/**
	 * Routing
	 */

	public function add_route( $route, $call_back ) {
		$__ = explode( "/", $route );
		$this->routes[ $__[1] ] = $call_back;
	}

	private function current_route() {
		return explode( "/", $_SERVER['QUERY_STRING'] ); // from the url
	}

	public function route() {
		// find matching route and return call back or 404 if there's no match
		if( array_key_exists( $this->current_route()[0], $this->routes ) ) {
			$this->route_params = $this->current_route();
			if( is_callable( $this->routes[$this->current_route()[0]] ) ) $this->routes[$this->current_route()[0]]();
			else call_user_func_array( $this->routes[$this->current_route()[0]], [ $this ] );
		} else {
			if( is_callable( $this->route_else ) ) ($this->route_else)();
			else echo $this->route_else;
		}
	}

	/**
	 * File upload
	 */

	public function upload( $input, $params ) {
		$default = array(
			'name' => $_FILES[$input]['name'],
			'allowed_formats' => [],
			'size_range' => [ 'min' => NULL, 'max' => NULL ],
			'upload_dir' => ''
		);

		$params = array_merge( $default, $params );
		$uf = TRUE; // can file be uploaded?
		$format = pathinfo( $_FILES[$input]['name'], PATHINFO_EXTENSION ); // file format

		// target file
		if( $params['name'] == $_FILES[$input]['name'] ) $target = $params['upload_dir'] . $params['name'];
		else $target = $params['upload_dir'] . $params['name'] . "." . $format;

		// check if file with same name exists
		if( file_exists( $target ) ) {
			$this->upload_errors[] = "File already exists!";
			$uf = FALSE;
		}

		// check that file size is within specified range, if range is set
		// MIN
		if( isset( $params['size_range']['min'] ) ) {
			if( $_FILES[$input]['size'] < $params['size_range']['min'] ) {
				$this->upload_errors[] = "File size too small";
				$uf = FALSE;
			}
		}
		// MAX
		if( isset( $params['size_range']['max'] ) ) {
			if( $_FILES[$input]['size'] > $params['size_range']['max'] ) {
				$this->upload_errors[] = "File size too large";
				$uf = FALSE;
			}
		}

		// allowed file formats
		if( !in_array( $format, $params['allowed_formats']) ) {
			$this->upload_errors[] = "File format not supported";
			$uf = FALSE;
		}

		// upload file
		if( $uf ) {
			if ( move_uploaded_file( $_FILES[$input]["tmp_name"], $target ) ) return TRUE;
		    return FALSE;
		}
	}

	/**
	 * Utility
	 */

	// redirect page
	public function redirect_to( $url ) {
		header( "Location: " . $url );
		exit;
	}
}