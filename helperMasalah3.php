<!-- cukup di run skali aja -->
<?php 
require 'connect.php';
    $addRegion = "ALTER TABLE refunds ADD Region VARCHAR(255)";
    $conn->query($addRegion);
    // $addState = "ALTER TABLE refunds ADD State VARCHAR(255)";
    // $conn->query($addState);
    // $addCity = "ALTER TABLE refunds ADD City VARCHAR(255)";
    // $conn->query($addCity);
?>