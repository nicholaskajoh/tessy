<?php
	require_once "tessy.php";

	// create a Tessy object
	$t = new Tessy();

	// open a db connection
	$config = array(
		"host" => "localhost",
		"dbname" => "tessy",
		"username" => "root",
		"password" => ""
	);
	$t->odb( $config );

	// APPS ROUTES
	$t->add_route('/', function() use ($t) { include 'welcome.php'; });
	$t->add_route('/blog-app', function() use ($t) { include 'tessy-blog/app.php'; });
	$t->add_route('/pathetic-app', function() use ($t) { include 'pathetic/app.php'; });
	$t->add_route('/guns-bay-app', function() use ($t) { include 'guns-bay/app.php'; });
	$t->add_route('/landmark-app', function() use ($t) { include 'landmark/app.php'; });
	$t->add_route('/vid-hub-app', function() use ($t) { include 'vid-hub/app.php'; });

	// PATHETIC APP AJAX CALLBACKS
	// send message
	$t->add_route('/send-pathetic-msg', function() use ($t) { 
		$table = "messages";
		$data = [
			'sender' => $_POST['sender'],
			'receiver' => $_POST['receiver'],
			'message' => $_POST['message']
		];
		$t->create($table, $data);
	});
	// refresh chat
	$t->add_route('/refresh-pathetic-chat', function() use ($t) { 
		$table = "messages";
		$clauses = "WHERE (sender = '{$_POST['sender']}' AND receiver = '{$_POST['receiver']}') OR (sender = '{$_POST['receiver']}' AND receiver = '{$_POST['sender']}') ORDER BY date";
		$data = $t->read($table, [], $clauses);
		$html = "";
		foreach ($data['data'] as $d) {
			if($d['sender'] == $_POST['sender']) {
				$html .= "<div class=\"sender-chat-bubble\"><div class=\"user\">{$d['sender']}, <span>{$d['date']}</span></div>{$d['message']}</div>";
			} else {
				$html .= "<div class=\"receiver-chat-bubble\"><div class=\"user\">{$d['receiver']}, <span>{$d['date']}</span></div>{$d['message']}</div>";

			}
		}
		echo $html;
	});

	// initalize routing
	$t->route();