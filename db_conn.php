<?php 

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "the_course";

//create a connection to the db
$conn = mysqli_connect($servername, $username, $password, $dbname);

//check connection
if(!$conn){
    die("Connection failed". mysqli_connect_error());
}

/*
     ------------ Database ---------
     name =  the_course

     course = cid cname cfee duration
     registration = rid cid Fname Lname DOB Age phone_number date_time
 
*/
?>