<html>
<head>
	<title>Tests</title>

	<?php
		// meta
		$elements = array(
			[ 'charset' => 'UTF-8' ],
			[ 'name' => 'description', 'content' => 'A PHP Nano Framework' ],
			[ 'name' => 'author', 'content' => 'Nicholas Kajoh' ]
		);
		$t->meta( $elements );
	?>

	<?php
		// favicon 
		$source = "ironman.jpg";
		$type = "image/jpg";
		$t->fav( $source, $type );
	?>

	<?php
		// css 
		$base = "";
		$sources = [ 'style', 'test' ];
		$t->css( $base, $sources ); 
	?>
</head>
<body>
	<h1>Tests</h1>

	<?php 

		// DATABASE QUERY METHODS
		$table = "myTable";

		// Insert into db
		$columns = array(
			'col2' => 'tessy',
			'col4' => mt_rand( 1, 100 )
		);
		$t->create( $table, $columns );

		// Select
		$columns = [ 'col1', 'col2' ];
		$clauses = "WHERE col1 > 0 LIMIT 20";
		$data = $t->read( $table, $columns, $clauses );

		echo "<pre>";
		var_dump( $data['data'] );
		echo "</pre>";
		echo "Rows: " . $data['count'];

		// Update
		$set = array(
			'col2' => 'beans',
			'col3' => 'garri'
		);
		$clauses = "WHERE col1 = 1";
		$t->edit( $table, $t->sanitize( $set ), $clauses );

		// Delete
		$clauses = "WHERE col1 > 5";
		$t->delete( $table, $clauses );

		// sanitization
		$dirty = "<pre>I'll do my best</pre><script>alert();</script>";
		$clean = $t->sanitize( $dirty );
		echo "<br>" . $clean;
	?>	


	<?php 
		// validation 
		$is_valid_username = $t->validate( '_000user', 'uname' );
		$is_valid_name = $t->validate( 'James Bond', 'name' );
		$is_valid_email = $t->validate( 'me.you@them.us', 'email' );
		$is_valid_number = $t->validate( 2016, 'number' );
		$is_valid_url = $t->validate( 'http://somesite.com/goodstuff', 'url' );
		if( $is_valid_username && $is_valid_number && $is_valid_name && $is_valid_email && $is_valid_url ) echo "<br> All valid!";
	?>

	<form method="post" enctype="multipart/form-data">
		<input type="file" name="myfile">
		<input type="submit" value="Upload" name="submit">
	</form>

	<?php  
		// file upload
		if( isset( $_POST['submit'] ) ) {
			$input_name = "myfile";
			$params = array(
				'name' => 'mynewfile',
				'allowed_formats' => [ 'jpg', 'png', 'gif', 'raw' ],
				'upload_dir' => ''
			);

			if( $t->upload( $input_name, $params ) ) echo "<br> File uploaded!";
			else var_dump( $t->uploadErrors );
		}
	?>

	<?php
		// javascript
		$base = "";
		$sources = [ 'test', 'jquery' ];
		$t->js( $base, $sources ); 
	?>

	<script type="text/javascript">
		$(function() {
			$.ajax({
				method: 'get',
				url: '<?php echo $t->root ?>myajax/Hello World',
				dataType: 'text',
				data: { msg: "Hello World" },
				success: function(res) {
					console.log( res );
				}
			});
		});
	</script>
</body>
</html>