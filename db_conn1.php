<?php 

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "my_ussd";

//create a connection to the db
$conn = mysqli_connect($servername, $username, $password, $dbname);

//check connection
if(!$conn){
    die("Connection failed". mysqli_connect_error());
}
?>