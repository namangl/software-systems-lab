<?php
    require_once('connect.php');

    $user_condition = "";
    if (isset($_POST["usertype_id"]) && trim($_POST["usertype_id"]) != ""){
        $use = $_POST["usertype_id"];
        $user_condition = " AND (usertype_id= '$use' OR usertype_id='0')";
    }
    else{
        $user_condition = " AND usertype_id='0'";
    }

    $category_condition ="";
    if (isset($_POST["category_id"]) && trim($_POST["category_id"]) != ""){
        $cat = $_POST["category_id"];
        if ($cat != 0){
            $category_condition = " AND category_id='$cat'";
        }       
    }

    $limit = 0;
    if (isset($_POST["limit"])){
        $limit = $_POST["limit"];
    }
    $statement = "SELECT * FROM events WHERE `time`>= NOW()" . $user_condition . $category_condition . " ORDER BY time DESC LIMIT 10 OFFSET " . 10*$limit;
    $res = mysqli_query($connect,$statement);
    $result = array();
    if (mysqli_num_rows($res) > 0) {
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

        while ($row = mysqli_fetch_array($res)) {
            $user_id = $row['user_id'];
            $user_statement = "SELECT * FROM user WHERE user_id = '$user_id';";
            $user_res = mysqli_query($connect,$user_statement);
            if ($user_row = mysqli_fetch_array($user_res)) {

                array_push($result,
                array('event_id'=>$row['event_id'],
                'name'=>$row['name'],
                'time'=>$row['time'],
                'venue'=>$row['venue'],
                'details'=>$row['details'],
                'usertype_id'=>$row['usertype_id'],
                'usertype'=>$usertype_list[$row['usertype_id']],
                'creator_id'=>$row['user_id'],
                'creator'=>$user_row['name'],
                'category_id'=>$row['category_id'],
                'category'=>$category_list[$row['category_id']]
                ));
            }
        }
        echo json_encode(array("events"=>$result, "success"=>true));
    }
    else{
        echo json_encode(array("success"=>false));
    }

    
    mysqli_close($connect);
?>