<?php 
require_once 'autoload.php'; 
require 'connect.php';

$client = new MongoDB\Client();
$customers = $client->superstore->customers;

$state = $customers->distinct('State');

$refunds = "SELECT * FROM refunds";
$result = $conn->query($refunds);

$customerIds = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedState = isset($_POST['state']) ? $_POST['state'] : null;
    
    // Cari cust ID di tabel customer yang sesuai selected state
    if ($state !== '') {
        $cursor2 = $customers->find(['State' => $selectedState]);
        $customerIds .= '(';
        foreach ($cursor2 as $customer) {
          $customerIds .= "'" . $customer['Customer ID'] . "', ";
        }
        $customerIds = rtrim($customerIds, ', ');
        $customerIds .= ')';
    }
    
    // Cari customer ID di tabel Refund sesuai cust ID yg selected state (atas)
    $sql = "SELECT CustomerID, RefundID
            FROM refunds
            WHERE " . (empty($customerIds) ? '1' : "CustomerID IN $customerIds");
    $result2 = $conn->query($sql);
    $custIDs = [];
    $count = 0;
    foreach ($result2 as $row) {
        $custID = $row['CustomerID'];
        $count = $count + 1;
        $custIDs[] = $custID;
    }
    

    // Ini uda bener munculnya Arizona smua
    // $custSelected = $customers->find(['Customer ID' => ['$in' => $custIDs]]);

    $aggregationPipeline = [
        ['$match' => ['Customer ID' => ['$in' => $custIDs]]],
        ['$group' => ['_id' => '$City', 'count' => ['$sum' => 1]]],
    ];

    $cityCounts = $customers->aggregate($aggregationPipeline);

    // Output the results
    // foreach ($cityCounts as $result) {
    //     echo "City: " . $result['_id'] . ", Count: " . $result['count'] . "\n";
    // }

    echo json_encode(['totalState' => $count, 'totalCity' => iterator_to_array($cityCounts)]);
    exit;
}
$conn->close();
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
            <select class="form-select" id="filterState" aria-label="Floating label select example">
                <option selected hidden>State</option>
                <option>None</option>
                <?php foreach($state as $s): ?>
                <option ><?= $s ?></option>
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
                <?php foreach ($result as $r): ?>
                    <tr>
                        <td><?= $r['RefundID'] ?? '' ?></td>
                        <td><?= $r['OrderID'] ?? '' ?></td>
                        <td><?= $r['CustomerID'] ?? '' ?></td>
                        <td><?= $r['RefundDate'] ?? '' ?></td>
                        <td><?= $r['PrductID'] ?? '' ?></td>
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
            $inputState = $('#filterState').val()
            // alert($inputState)

            if($inputState == 'State' || $inputState == 'None'){
                $inputState = ''
            }
            $.ajax({
                method: 'POST',
                data: {
                    state: $inputState
                },

                success: function (response) {
                    var responseData = JSON.parse(response);
                    var totalState = responseData.totalState;
                    var filteredData = responseData.totalCity;
                    var thead = $('thead');
                    var tbody = $('tbody');
                    var noData = $('.noData');
                    
                    // Set a flag to indicate whether there is no data
                    var noDataFlag = filteredData.length === 0;

                    tbody.empty();
                    thead.empty();
                    noData.empty();

                    if (noDataFlag) {
                        noData.append('<h3>No data found.</h3>');
                    } else {
                        var header = '<tr>';
                        header += '<th>City</th>';
                        header += '<th>Count</th>';
                        header += '</tr>';
                        thead.append(header);
                    }

                    alert(totalState);
                    filteredData.forEach(function (item) {
                        var row = '<tr>';
                        row += '<td>' + (item._id || '') + '</td>';  // Use item._id for City
                        row += '<td>' + (item.count || '') + '</td>';
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