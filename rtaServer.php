<?php
$servername = "***********";
$username = "***********";
$password = "*********";
$dbname = "RTA";

$conn = new mysqli($servername, $username, $password, $dbname);
$response = json_encode(['status'=>'FAIL', 'msg'=>'Couldnt connect to server']);

if($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
	echo($response);
} else {

	if($_SERVER['REQUEST_METHOD'] == "GET") {
		if(isset($_GET['stop']) && isset($_GET['route']) && isset($_GET['nextEntries']) && $_GET['stop'] != "" && $_GET['route'] != "" && $_GET['nextEntries'] != "") {
			// if route and stop are set, then get next 4 stop times past the entries index
			$rte = htmlspecialchars($_GET['route']);
			$stp = htmlspecialchars($_GET['stop']);
			$indx = htmlspecialchars($_GET['nextEntries']);
			
			$secsInDay = 86400;
			// convert current time to float
			$time = date('H:m:s');
			$time = explode(':', $time);
			$time = $time[2] + $time[1]*60 + $time[0]*60*60;
			$time = $time / $secsInDay;
			
			$result = mysqli_query($conn, "SELECT time FROM rta WHERE time >= $time AND route LIKE '$rte' AND stop LIKE '$stp' ORDER BY time ASC LIMIT $indx");

			$arr = array();
			while($res = mysqli_fetch_assoc($result)) {
				$arr[] = $res["time"];
			}
			
			// Tried to implement SQL Injection prevention, didn't work due to the incorrect driver being installed

			/*
			$stmt = $conn->prepare("SELECT time FROM rta WHERE time >= ? AND route LIKE ? AND stop LIKE ? ORDER BY time ASC LIMIT ?");
			$stmt->bind_param("sssd", $time, $rte, $stp, $indx);

			$stmt->execute();

			$res = $stmt->get_result();
			$arr = array();
			while($row = $result->fetch_assoc()) {
				$arr[] = $row["time"];
			}
			*/

			$response = json_encode(['satus'=>'OK', 'msg'=>'got times', 'tms'=>$arr]);

			echo($response);


		} elseif(isset($_GET['route']) && !isset($_GET['stop']) && !isset($_GET['nextEntries'])&& $_GET['route'] != "") {
			// if only route is set, then get the stops for that route
			$rte = htmlspecialchars($_GET['route']);			
			
			$result = mysqli_query($conn, "SELECT DISTINCT stop FROM rta WHERE route LIKE '".$rte."'");
			
			$arr = array();
			while($res = mysqli_fetch_assoc($result)) {
				$arr[] = $res["stop"];
			}
			
			//$response = json_encode(['status'=>'FAIL', 'msg'=>'Couldnt query db for route']);
			$response = json_encode(['status'=>'OK', 'msg'=>'obtained stops', 'stps'=>$arr]);
	
			echo($response);

		} else {
			$result = mysqli_query($conn, "SELECT DISTINCT route FROM rta");
			//$response = json_encode(['status'=>'FAIL', 'msg'=>'couldnt retreive routes']);

		
			$arr = array();
			while($res = mysqli_fetch_assoc($result)) {
				$arr[] = $res["route"];
			}

			$response = json_encode(['status'=>'OK', 'msg'=>'retreived routes', 'rts'=>$arr]);
			

			echo($response);
		}
	} else {
		$response = json_encode(['stauts'=>'FAIL', 'msg'=>'Connected, but only accepts GET requests']);
		echo($response);
	}
}
?>
