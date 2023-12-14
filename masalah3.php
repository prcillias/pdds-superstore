<?php 
require_once 'autoload.php'; 
require 'connect.php';

$client = new MongoDB\Client();
$customers = $client->superstore->customers;

$regionList = $customers->distinct('Region');

$refundsJoin = "SELECT * FROM refunds";
$result2 = $conn->query($refundsJoin);
$customerIDsArray = [];
while ($row = $result2->fetch_assoc()) {
    $customerIDsArray[] = $row['CustomerID'];
}
$cursor = $customers->find(
    ['Customer ID' => ['$in' => $customerIDsArray]],
    ['projection' => ['_id' => 0, 'Customer ID' => 1, 'Region' => 1, 'State' => 1]]
);

foreach ($cursor as $document) {
    $customerID = $document['Customer ID'];
    $region = $document['Region'];
    $state = $document['State'];

    $updateQuery = "UPDATE refunds SET Region = '$region', State = '$state' WHERE CustomerID = '$customerID'";
    $conn->query($updateQuery);
}

$customerIds = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedRegion = isset($_POST['region']) ? $_POST['region'] : null;
    
    $sql = "SELECT State, COUNT(RefundID) as Total
            FROM refunds
            WHERE Region = '$selectedRegion'
            GROUP BY State
            ORDER BY Total DESC";
    $result = $conn->query($sql);
    $data = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($data);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kota dengan Jumlah Produk Refund Terbanyak dalam Suatu Negara Bagian</title>
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- AJAX -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <!-- SWEET ALERT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="filterWrap">
    <h2>Kota dengan Jumlah Produk Refund Terbanyak dalam Suatu Negara Bagian</h2>
    <div class="filterWrapMini">
        <div class="input-group mb-3">
            <select class="form-select" id="filterRegion" aria-label="Floating label select example">
                <option selected hidden>Region</option>
                <option>None</option>
                <?php foreach($regionList as $r): ?>
                <option ><?= $r ?></option>
                <?php endforeach; ?>
            </select> 
        <button class="btn btn-primary" type="submit" id="getData">Submit</button>
    </div>
    <div class="noData"></div>
</div>
<div class="table-wrap">
    <div class="table-wrap-mini">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Refund ID</th>
                    <th>Order ID</th>
                    <th>Customer ID</th>
                    <th>Refund Date</th>
                    <th>Product ID</th>
                    <th>Refund Amount</th>
                    <th>Refund Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result2 as $r): ?>
                    <tr>
                        <td><?= $r['RefundID'] ?? '' ?></td>
                        <td><?= $r['OrderID'] ?? '' ?></td>
                        <td><?= $r['CustomerID'] ?? '' ?></td>
                        <td><?= $r['RefundDate'] ?? '' ?></td>
                        <td><?= $r['ProductID'] ?? '' ?></td>
                        <td><?= $r['RefundAmount'] ?? '' ?></td>
                        <td><?= $r['RefundType'] ?? '' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
     $(document).ready(function(){
        $('#getData').on('click',function(){
            $inputRegion = $('#filterRegion').val()

            if($inputRegion == 'Region' || $inputRegion == 'None'){
                $inputRegion = ''
            }
            $.ajax({
                method: 'POST',
                data: {
                    region: $inputRegion
                },

                success: function (response) {
                    alert(response);
                    var filteredData = JSON.parse(response);

                    var thead = $('thead');
                    var tbody = $('tbody');
                    var noData = $('.noData');
                    
                    var noDataFlag = filteredData.length === 0;

                    tbody.empty();
                    thead.empty();
                    noData.empty();

                    if (noDataFlag) {
                        noData.append('<h3>No data found.</h3>');
                    } else {
                        var header = '<tr>';
                        header += '<th>State</th>';
                        header += '<th>Count</th>';
                        header += '</tr>';
                        thead.append(header);
                    }
                    
                    filteredData.forEach(function (item) {
                        var row = '<tr>';
                        row += '<td>' + (item["State"] || '') + '</td>';
                        row += '<td>' + (item["Total"] || '') + '</td>';
                        row += '</tr>';
                        tbody.append(row);
                    });
                },
                error: function(){
                    Swal.fire({
                        title: 'Sorry',
                        text: 'unknown error occurred',
                        icon: 'error',
                        confirmButtonText: 'Close',
                        timer: 3000,
                        timerProgressBar: true
                    })
                }
            });
        }) 
    });
</script>
</body>
</html>