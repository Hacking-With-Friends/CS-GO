<?php
	header('Content-Type: application/json');
#	print('{"test" : "something"}');

	$data = file_get_contents('php://input');
	if($data) {
		$data = json_decode($data, true);

		print('{"banned" : "'.$data['ban'].'"}');
		#print_r($data);
	}
?>