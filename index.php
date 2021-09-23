<?php
/*
   cd path/to/your/app
   Localhost - php -S localhost:8000

   ngrok - ./ngrok http 8000
*/
   //Read the variables sent via POST from the API africa's talking api
   $sessionId   = $_POST['sessionId'];
   $serviceCode = $_POST['serviceCode'];
   $phonenumber = $_POST['phoneNumber'];
   $text        = $_POST['text'];

   //Print the response as plain text so that the ussd gateway can understand
   header('Content-type: plain/text');

   //create an array from the user selection from one level to another 
   //store the user selection in the array $data_input
   //use explode to split the text to an array
   //the array will be separated by a * for every menu level / input
   //we will create an associative array an array with key value pairs key and value
   $data_input = explode("*", $text);

   //set the level to be 0 zero 
   $level = count($data_input);

   //bring in the db connection file
   require('db_conn.php');

   //the main menu of the ussd is level zero
   //this is what will be displayed when the user dials the ussd
   function mainMenu(){
      //define our variables and make them global
      global $text;
      global $level;
      
      $response = "CON Welcome to The Course\n";
      $response .= "1. Courses\n";
      $response .= "2. Register\n";
      $response .= "3. Profile\n";
      $response .= "4. About\n________\n";
      $response .= "0. Exit\n" ;
      
      echo $response ;
  }

  //when the user dials in 1 and chooses courses this function will called  
  function courses(){
     global $level;
     global $conn;

     //create an sql statement to select all courses 
     $sql = "SELECT * FROM `course`";

     //execute the sql query and the connection
     $results = mysqli_query($conn,$sql);

     //we count db response 
     //num-rows is used to check the number of rows in the dataset 
     //it is used to check the if data is present in the database or not 
      $db_res = mysqli_num_rows($results);
       
      //
     if ($level == 1) {
      $response = "CON Courses $level\n";

      //if the response from db is greater than 0 diplay 
      if ($db_res > 0) {
         //we are creating an association array out of $results 
         while($row = mysqli_fetch_assoc($results)){
            // $course_id = $row['course_id'];
            $course_name = $row['course_name'];
            $course_fee  = $row['course_fee'];
            $duration    = $row['duration'];
            //show the user the details
            $response .= "- $course_name, $course_fee, $duration\n"; 
         }
      }else{
            $response .= "No courses Found\n"; 
      }
             $response .= "\n____________\n0. Mainmenu\n";        
     }elseif($level== 2){
		unset($sorted_data);
		$level=0;
      $response =  mainMenu();
	}
      
    echo $response;

    //show the courses offered
   //  $response = "CON Courses $level\n";
   //  $response .= "1. Django\n";
   //  $response .= "2. laravel\n";
   //  $response .= "3. Flask\n";
   //  $response .= "4. MongoDB\n_____\n";
   //  $response .= "0. Mainmenu\n";
   }

    function register(){
       //make the variables global 
      global $level;
      //remember that $data_input   
      global $data_input;
      global $conn;
      global $phonenumber;
      
        //enable the user to enter their names and choose a course
        //this is level 1 = First Name
        if ($level == 1) {
           $response = "CON Welcome $level \n Please enter your First Name?\n";
         //this is level 2 = Last Name 
        }elseif ($level == 2) {
            if ($data_input[1] != "") {
               $response = "CON ".$data_input[1]." $level \n please enter your Last Name?\n";
            }
         // this is level 3 = DOB
        }elseif ($level == 3) {
           $name = $data_input[1]." ".$data_input[2];
            if ($data_input[2] !== "") {
               $response = "CON ".$name ." $level \n please enter the year your born?";
            }
         //this is level 4 = Course
        }elseif ($level == 4) {
           if($data_input[3] != ""){
              $response = "CON Please choose a course $level\n";

              //create an sql statement
              $sql = "SELECT * FROM `course`";
   
              //execute the sql and 'connection
              $results = mysqli_query($conn, $sql);

              if ($results) {
                 $count = 1;
                 while ($row = mysqli_fetch_assoc($results)) {
                    $course_id   = $row['course_id'];
                    $course_name = $row['course_name'];
                    $course_fee  = $row['course_fee'];
                    $duration    = $row['duration'];

                    $response .= "$course_id: $course_name, $course_fee $duration\n";
                 }
              }
                    $response .= "\n__________\n 0: Main menu";
           }
           //this is level 5 == Confirm or cancel registration
        }elseif ($level == 5) {
           //get the user input / user selection we store it in $selected
           $selected = $data_input[4];
           //we get the name of the course selected through the number choosen from the function
           $courseSelected = getCourseNameSelected($conn, $selected);
           
           //show the user a response
           $response = "CON You have selected\n $courseSelected \n 1. Confirm \n 2. Cancel";     
        }elseif ($level == 6) {
           //the user inputs are stored in the $data_input array so
           //       level = 0                       level = 1 
           //data_input[0] = 2 for register data_input[1] = firstname of the user

           if($data_input[5] == 1){
            //get the details that the user has inputted 
            $firstname = $data_input[1];
            $lastname  = $data_input[2];
            $dob       = $data_input[3];
            //the current year - year the user entered = age
            $age       = date("Y")-$data_input[3];
            $courseSelected = getCourseNameSelected($conn, $selected);
            $selected = $data_input[4];
            $phonenumber;
            
 
            //insert the details in the db
            $sql =  "INSERT INTO `registration`(`course_id`, `first_name`, `last_name`, `dob`, `age`, `phone_number`) VALUES ('$selected','$firstname','$lastname','$dob','$age','$phonenumber')";
            
            $results = mysqli_query($conn, $sql);
            if ($results) {
               $response = "END You have successfully registered for $courseSelected";
            }else {
               $response = "END Registration has failed kindly try again";
            }
         }
          
        }else {
           invalid();
        }
        
      echo $response;
    }
   //---- QUE
   //when you choose 1 you enter level one (new order) even when you choose 2 you are still in level 1
   
   function profile(){
     global $level;
     global $data_input;
     global $conn;
     global $phonenumber;
     //$course_name = getCourseNameSelected($conn);
     //create an sql statement
   //   $sql = "SELECT * FROM `registration`";
     $sql = "SELECT r.`registration_id`,r.`first_name`,r.`last_name`,r.`age`,r.`date_time`,c.`course_name` FROM registration r 
     JOIN course c ON c.`course_id` = r.`course_id` WHERE phone_number=".$phonenumber;
     //execute the sql query and the connection
     $results = mysqli_query($conn,$sql);

     //we count db response   
      // $db_res = mysqli_num_rows($results);

     if ($level == 1) {
      $response = "CON Your Profile $level\n";

      if ($results) {
         //we are creating an association array out of $results 
         while($row = mysqli_fetch_assoc($results)){
            $registration_id   = $row['registration_id'];
            $course_name = $row['course_name'];
            $first_name  = $row['first_name'];
            $last_name   = $row['last_name'];
            $age         = $row['age'];
            $datetime    = date('M j, Y', strtotime($row['date_time']));           //show the user the details
            $response   .= "- $registration_id: $course_name, $first_name $last_name $age,\n $datetime\n"; 
         }
      }
             $response .= "\n____________\n0. Mainmenu\n";        
     }elseif($level== 2){
		unset($sorted_data);
		$level=0;
      $response =  mainMenu();
	}
   echo $response;
}

   function about(){
    //make level global
    global $level;
    global $data_input;

    //show the about details 
    $response = "CON About $level\n";
    $response .= "Register for any course using our ussd. Thank you\n____________\n";
    $response .= "0. Mainmenu\n";

    if ($level == 2) {
        if ($data_input[1]==0) {
         unset($sorted_data);
         $level=0;      
         $response = mainMenu();
        }

    }
      
    echo $response;
   }

   function getCourseNameSelected($conn, $course_id){
        //create an sql statement
        $sql = "SELECT * FROM `course` WHERE course_id=".$course_id;

        //execute the sql statement and the connection
        $results = mysqli_query($conn,$sql);

        //get an association array from results
        $course = mysqli_fetch_assoc($results);

        //return the results 
        return $course['course_name'];
   }
  
   function endSession(){
      $response = "END Thank you for using our service";

      echo $response;
   } 

   function invalid(){
      $response = "END Invalid choice or entry";

      echo $response;
   }

   if($text != ""){
      if($level>0){
          switch($data_input[0]){
              case 1:
                 courses();
              break;
              case 2:
                 register();
              break;
              case 3:
                 profile();
             break; 
              case 4:
                 about();
              break;
              case 0:
                endSession();
              break;
              default:
                 invalid();
              break;
         }
      }
   }else{
  mainMenu();
 }
    
?>