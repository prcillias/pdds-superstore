<!-- cukup di run skali aja -->
<?php 
require 'connect.php';
$addRefunds = "ALTER TABLE refunds ADD uniqueRefunds VARCHAR(255)";

if ($conn->query($addRefunds) === TRUE) {
    echo "New column added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}

$updateSql = "UPDATE refunds SET uniqueRefunds = CONCAT(OrderID, '_', ProductID)";
$conn->query($updateSql);

$addOrders = "ALTER TABLE orders ADD uniqueOrders VARCHAR(255)";

if ($conn->query($addOrders) === TRUE) {
    echo "New column added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}

$updateSql = "UPDATE orders SET uniqueOrders = CONCAT(OrderID, '_', ProductID)";
$conn->query($updateSql);

$sql = "SELECT uniqueOrders FROM orders";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo $row["uniqueOrders"]."<br>";
    }
} else {
    echo "0 results";
}
?>