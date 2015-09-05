<?php

$dbHost = "us-cdbr-azure-central-a.cloudapp.net";
	$dbUser = "b125155e5e1df5";
	$dbPass = "bba28a8d";
	$dbName = "PACMySQLDatabase";
	$db = new mysqli( $dbHost, $dbUser, $dbPass, $dbName );


	if( $db->connect_errno )
	    die( "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error );

	if( !$db->set_charset( "utf8mb4" ) ) {
	    printf("Error loading character set utf8mb4: %s\n", $db->error);
	} 

	if( $db->connect_errno )
	    die( "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error );

	if( !$db->set_charset( "utf8mb4" ) ) {
	    printf("Error loading character set utf8mb4: %s\n", $db->error);
	} 
	$patid = $_SESSION['patid'];
	$datasetId = 1; //currently not functional

	// table names for queries
	
	$tablePrefix = "";
	$dataTable = $tablePrefix ."sensors";
	$userTable = $tablePrefix . "patientdata";
	$datasetTable = $tablePrefix . "dataset";
	$activityDatasetTable = $tablePrefix . "activityDataset";
	$activityTable = $tablePrefix . "activity";
	

	$query = "select * from {$activityDatasetTable} where datasetId={$datasetId}";
	$result = $db->query( $query );
	if( !$result ) {
	//an error occured
	die( "There was a problem executing the SQL query. MySQL error returned: {$db->error} (Error #{$db->errno})" );

	$query2 = "select * from {$sensors} where ID={$datasetId}";
	$result2 = $db->query( $query );
	if( !$result ) {
	//an error occured
	die( "There was a problem executing the SQL query. MySQL error returned: {$db->error} (Error #{$db->errno})" );

	// get existing labels (data_mat and labels; -1 for unlabeled data)
	$data_mat = array();
	$labels = array();
	
	foreach($result as $row){
		$arr = array($row['startTime'],$row['endTime']);
		$data_mat[] = json_encode($arr);
		if ($row['activity']===null){
			$labels[] = -1;	
		}
		else{
			$labels[]=$row['activity'];
		}
	}

	// get raw data
	$raw_data = array();
	foreach($result2 as $row2){
		$arr2[$row2['timestamp']] = array($row2['accelerometer_x_CAL'], $row2['accelerometer_y_CAL'], $row2['accelerometer_z_CAL']);
		$raw_data[] = json_encode($arr2);
	}

	$data_mat_j = json_encode($data_mat);
	$labels_j = json_encode($labels);
	$raw_data_j = json_encode($raw_data);

	// exec python file 
	$command = escapeshellcmd('predict.py "{$data_mat_j}" "{$labels_j}"  "{$raw_data_j}"');
	$output = shell_exec($command);

	alert("Prediction complete. Suggestions will appear in labeling box.");

	// for each result, display (NOTE: #output is a string)

	?>