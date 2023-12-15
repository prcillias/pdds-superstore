<?php 
    $servername = "localhost"; // Replace with your database server name
    $username = "root"; // Replace with your database username
    $password = ""; // Replace with your database password
    $dbname = "superstore"; // Replace with your database name

    $conn2 = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn2->connect_error) {
        die("Connection failed: " . $conn2->connect_error);
    }
 ?>