<?php
    require_once('connect.php');

    $response = array();
    $response["success"] = false;

    if (isset($_POST["name"]) && trim($_POST["name"]) != "" && isset($_POST["user_id"]) && trim($_POST["user_id"]) != ""  && isset($_POST["category_id"]) && trim($_POST["category_id"]) != "" && isset($_POST["usertype_id"]) && trim($_POST["usertype_id"]) != "" && isset($_POST["venue"]) && trim($_POST["venue"]) != "" && isset($_POST["time"]) && trim($_POST["time"]) != "" && isset($_POST["details"]) && trim($_POST["details"]) != "" ){

	    $name = trim($_POST["name"]);
	    $user_id = trim($_POST["user_id"]);    
	    $category_id = trim($_POST["category_id"]);
	    $usertype_id = trim($_POST["usertype_id"]);
	    $venue = trim($_POST["venue"]);
	    $eventtime = trim($_POST["time"]);
	    $details = trim($_POST["details"]);
		$statement = mysqli_prepare($connect, "INSERT INTO events (name, user_id, category_id, usertype_id, venue, time, details) VALUES (?, ?, ?, ?, ?, ?, ?)");
		mysqli_stmt_bind_param($statement, "siiisss", $name, $user_id, $category_id, $usertype_id, $venue ,$eventtime, $details);
		if (mysqli_stmt_execute($statement)){
		    mysqli_stmt_close($statement);
		    $event_id =  mysqli_insert_id($connect);
		    $event_statement = "SELECT * FROM events WHERE event_id = '$event_id';";
	        $event_res = mysqli_query($connect,$event_statement);
	        if (mysqli_num_rows($event_res) ==1) {
	        	$category_statement = "SELECT * FROM category;";
		        $category_res = mysqli_query($connect,$category_statement);
		        $category_list = array();
		        while ($category_row = mysqli_fetch_array($category_res)) {
		            $category_list[$category_row['category_id']] = $category_row['name'];
		        }

		        $usertype_statement = "SELECT * FROM usertype;";
		        $usertype_res = mysqli_query($connect,$usertype_statement);
		        $usertype_list = array();
		        while ($usertype_row = mysqli_fetch_array($usertype_res)) {
		            $usertype_list[$usertype_row['usertype_id']] = $usertype_row['name'];
		        }

		        while ($row = mysqli_fetch_array($event_res)) {
		            $user_id = $row['user_id'];
		            $user_statement = "SELECT * FROM user WHERE user_id = '$user_id';";
		            $user_res = mysqli_query($connect,$user_statement);
		            if ($user_row = mysqli_fetch_array($user_res)) {

	        			$response["success"] = true;
	        			$dt = new DateTime($row['time']);
			            $newdate = $dt->format('d M h:i A');
		                
		                $msg = array
							(
								'body' 	=> $row['venue'] . ' - ' . $newdate,
								'title'		=> "New Event: " . $row['name'],
								'event_id'=>$row['event_id'],
								'name'=>$row['name'],
				                'time'=>$row['time'],
				                'venue'=>$row['venue'],
				                'details'=>$row['details'],
				                'usertype_id'=>$row['usertype_id'],
				                'usertype'=>$usertype_list[$row['usertype_id']],
				                'creator_id'=>$row['user_id'],
				                'creator'=>$user_row['name'],
				                'category_id'=>$row['category_id'],
				                'category'=>$category_list[$row['category_id']],
								'vibrate'	=> 1,
								'sound'		=> 1
							);
					    exec('php notifyevent.php '. escapeshellarg(serialize($msg)) .' > /dev/null 2>/dev/null &');
		            }
		        }
	        }
		    
		    

		    
		}
		else {
			mysqli_stmt_close($statement);
		}
	    
    }
    echo json_encode($response);
    mysqli_close($connect);
    $_POST = array();
?>
