<?php
   //Read the variables sent via POST from the API
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

   require('db_conn.php');

   //the main menu of the ussd it is level zero
   function mainMenu(){
      //define our variables and make them global
      global $text;
      global $level;
      
      //show the menu when the user dials the ussd
      $response = "CON Welcome to Merchants $level\n";
      $response .= "1. New Order\n";
      $response .= "2. My Orders\n";
      $response .= "3. About\n";
      $response .= "0. Exit\n";

      echo $response;
   }

   //---- QUE
   //when you choose 1 you enter level one (new order) even when you choose 2 you are still in level 1

   function Order(){
      //here we have the levels after each menu after counting the data_input array 
      //the level is the same as the last input entered by the user  
     global $level;
     //here we have the text inform of an array as data_input
     global $data_input;
     //here we create a variable of chosen category type
     $category="";
     //the connection to the db
     global $conn;
     //we will need the phone number of the customer
     global $phonenumber;
     
     //select all the categories to be displayed from the db
     $sql = "SELECT * FROM `Categories`";

     //execute the sql statement together with the connection
     $results = mysqli_query($conn, $sql);

     if($level==1){
        $response = "CON Choose a category $level\n";

        if($results){
           $count = 1;
           //get the values gotten from the db and loop thru them since 
           //the values come as an array
           while($row = mysqli_fetch_assoc($results)){
              $category_id = $row['category_id'];
              $category_name  = $row['category_name'];
              $category_price  = $row['price'];
                 //display to the user the looped info
              $response .= "$category_id . $category_name. $category_price\n";

              $count++;
           } 
        }
        $response .="00. Exit\n";
      }else if($level == 2){
        $selected = $data_input[1];

        $categorySelected = getSelectedCategory($conn, $selected);
        $response = "CON Which kind of $categorySelected?";
        if($selected==00){
           $response = "END Thank you";
        }else if($level==3){
        $selected=$data_input[1];

        $categorySelected = getSelectedCategory($conn, $selected);
        $quantity = $data_input[2];
        $response = "CON You have ordered $quantity - $categorySelected.\n 1. Confirmed \n 2. Cancel";
        }elseif($level == 4) { 
        if($data_input[3]==1){
         $response = "CON Where do you want to be delivered?";
        }else if($data_input[3]==1){
         $response = "END Thank you for using our service";
        }else {
         invalid();
       }
      }else if($level==5){
         $selected=$data_input[1];
         $categorySelected = getSelectedCategory($conn,$selected);
         $cost = getCostOfSelectedCategory($conn,$selected);
         $deliveryLocation = $data_input[4];
         $qty = $data_input[2];
         $food = $data_input[1];

         //insert into the orders table.

         $sql  = "INSERT INTO `orders`(`category_id`, `delivery_location`, `customer_phone`, `quantity`) VALUES ('$selected','$deliveryLocation',' $phoneNumber','$qty')";

         $results = mysqli_query($conn,$sql);
         if($results){
          $response = "END You have successfully ordered:\n $quantity - ".$categorySelected." to be delivered to\n$deliveryLocation\nTotal  = KSH.".($quantity*$cost);

         }else{
          $response = "END Your order failed.Kindly try again";
         }
     }
     }

     echo $response;
   }

   function getCostOfSelectedCategory($conn, $category_id){
      $sql = "SELECT * FROM `categories` WHERE category_id=".$category_id;
      //get executing
      $results = mysqli_query($conn,$sql);
      $category = mysqli_fetch_assoc($results);
  
      return $category['price'];
   }

   function getSelectedCategory($conn, $category_id){

      global $category_id;

      $sql = "SELECT * FROM `categories` WHERE category_id=".$cagetory_id;

      //execute the sql statement and the connection to db
      //mysqli_query is used to execute mysql queries
      $results = mysqli_query($conn, $sql);
      //return the results as an associative array from the results   
      $category = mysqli_fetch_assoc($results);

      return $category['category_name'];

   }

   function MyOrders($conn){
      global $level;
      //the connection to the db
      global $conn;
      //we will need the phone number of the customer
      global $phonenumber;

      $sql = "SELECT * FROM `orders` WHERE customer_phone=".$phonenumber;

      //execute the sql statement together with the connection
     $results = mysqli_query($conn, $sql);

        $response = "CON All your orders $level\n";

        if($results){
           $count = 1;
           //get the values gotten from the db and loop thru them since 
           //the values come as an array
           while($row = mysqli_fetch_assoc($results)){
              $order_id = $row['order_id'];
              
                 //display to the user the looped info
              $response .= "$order_id\n";

              $count++;
           } 
        }

        echo $response;
     }
   function about(){
      global $text;
      global $level;

         $response = "CON About\n____\n";
         $response .= "Lorem ipsum dolor sit amet\n. Nulla est purus, ultrices in porttitor\n";

         $selected = $data_input[1];
         if($selected)

      echo $response;
   }
   function endSession(){
      $response = "END Thank you for using our service";

      echo $response;
   } 

   function invalid(){
      $response = "END Invalid choice";

      echo $response;
   }

   if($text != ""){
      if($level>0){
          switch($data_input[0]){
              case 1:
                 Order();
              break;
              case 2:
                 MyOrders($conn);
              break; 
              case 3:
                 about();
              break;
              case 00:
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