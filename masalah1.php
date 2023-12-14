<?php
require 'connect.php';
require_once 'autoload.php';

$client = new MongoDB\Client();
$customers = $client->superstore->customers;
$products = $client->superstore->products;

// for customer segment filter
$cursor = $customers->distinct('Segment');

// for year filter
$sql = "SELECT DISTINCT YEAR(OrderDate) AS OrderYear FROM orders";
$stmt = $conn->query($sql)->fetchAll();

$customerIds = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['segment'])) {
        $segment = $_POST['segment'];
        $slicer = ($segment !== '') ? ['Segment' => $segment] : [];
        $cursor2 = $customers->find($slicer);
        foreach ($cursor2 as $cust) {
            $customerIds[] = "'" . $cust['Customer ID'] . "'";
        }
    }

    if (isset($_POST['year'])) {
        $year = $_POST['year'];
    }

    $sql = "SELECT ProductID, COUNT(ProductID) AS JumlahOrder
                FROM orders
                WHERE " . (empty($customerIds) ? '1' : "CustomerID IN (" . implode(",", $customerIds) . ")") . (empty($year) ? '' : " AND YEAR(OrderDate) IN (" . implode(",", $year) . ")") . "
                GROUP BY ProductID
                ORDER BY JumlahOrder DESC
                LIMIT 5";

    $stmt2 = $conn->query($sql)->fetchAll();

    foreach ($stmt2 as $row) {
        $productID = $row['ProductID'];
        $productIDs[] = $productID;
    }

    
    foreach ($productIDs as $productID) {
        $query = ['Product ID' => $productID];

        $cursor = $products->find($query);

        foreach ($cursor as $product) {
            $cursor3[] = $product['Product Name'];
        }
    }

    $response['stmt2'] = $stmt2;
    $response['cursor3'] = $cursor3;

    echo json_encode($response);
    exit;
}
?>








<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project PDDS</title>
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- AJAX -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <!-- SWEET ALERT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- JQUERY -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- CHART.JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <script src="https://cdn.canvasjs.com/ga/canvasjs.min.js"></script>
    
    <!-- FONT -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        
        .container {
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 50px;
        }

        h2 {
            font-size: 40px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .year-box {
            display: inline-block;
            margin: 5px;
            padding: 15px;
            cursor: pointer;
            border: 2px solid #000;
            /* border-radius: 10px; */
            text-align: center;
            font-size: 18px;
            line-height: 1.5;
        }

        .selected-year {
            background-color: gray;
            color: white;
        }

        .form-select {
            font-size: 20px; /* Increased font size */
            padding: 10px;
            text-align: center; 
        }

        #ordersTable th,
        #ordersTable td {
            font-size: 18px;
            padding: 15px;
        }
    </style>
</head>

<body>
    <div class="container p-3">
        <h2 class="text-center mb-4" id="title">Top 5 Product</h2>
        <div class="row mt-3">
            <!-- Filter Year -->
            <div class="col-md-6">
                <?php
                foreach ($stmt as $order) {
                    echo '<div class="year-box" data-status="off" data-year="' . $order['OrderYear'] . '">' . $order['OrderYear'] . '</div>';
                }
                ?>
            </div>

            <!-- Filter Customer Segment -->
            <div class="col-md-6">
                <select class="form-select" id="selectedSegment" aria-label="Default select example">
                    <option selected>All Segment</option>
                    <?php
                    foreach ($cursor as $segment) {
                        echo '<option value="' . $segment . '">' . $segment . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
            
        <div class="row mt-3">
            <div class="col-md-6">
                <table id="ordersTable" class="table table-striped table-bordered table-hover table-sm">
                    <thead class="thead">
                        <tr>
                            <th>Product Name</th>
                            <th>Total Ordered</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <canvas id="barChart" width="400" height="200"></canvas>
            </div>
        </div>

    </div>

        <script>
            $(document).ready(function() {
                selectedYears = [];
                loadData();

                function loadData(selectedSegment = null, selectedYear = []) {
                    $.ajax({
                        method: 'POST',
                        data: {
                            'segment': selectedSegment,
                            'year': selectedYear
                        },
                        success: function(e) {
                            var result = JSON.parse(e);
                            var jumlahOrders = result.stmt2;
                            var productsName = result.cursor3;
                            var tableBody = $('#ordersTable tbody');
                            tableBody.empty();
                            for (var i = 0; i < jumlahOrders.length; i++) {
                                var jumlahOrder = jumlahOrders[i].JumlahOrder;
                                var productName = productsName[i];
                                var newRow = "<tr>" +
                                    "<td>" + productName + "</td>" +
                                    "<td>" + jumlahOrder + "</td>" +
                                    "</tr>";
                                tableBody.append(newRow);
                            }

                            // Bar Chart
                            new Chart('barChart', {
                                type: "horizontalBar",
                                data: {
                                labels: productsName,
                                datasets: [{
                                    fill: false,
                                    lineTension: 0,
                                    backgroundColor: "rgba(75, 192, 192, 0.2)",
                                    borderColor: "rgba(75, 192, 192, 1)",
                                    data: jumlahOrders.map(order => order.JumlahOrder)
                                }]
                                },
                                options: {
                                legend: {
                                    display: false
                                },
                                scales: {
                                    yAxes: [{
                                    }],
                                    xAxes: [{
                                        ticks: {
                                            min: 0,
                                            max: Math.max(...jumlahOrders.map(order => order.JumlahOrder)) + 1
                                        }
                                    }],
                                },
                                title: {
                                    display: true,
                                    text: '',
                                    fontColor: 'grey',
                                    fontSize: 20
                                }
                                }
                            });

                        }
                    });
                }

                $('.year-box').on('click', function() {
                    var status = $(this).data('status');
                    selectedYear = $(this).data('year');
                    if (status == 'off') {
                        $(this).addClass('selected-year');
                        $(this).data('status', 'on');
                        selectedYears.push(selectedYear)
                    } else {
                        $(this).removeClass('selected-year');
                        $(this).data('status', 'off');
                        for (var i = 0; i < selectedYears.length; i++) {
                            if (selectedYears[i] == selectedYear) {
                                selectedYears.splice(i, 1);
                                break;
                            }
                        }
                    }

                    selectedSegment = $('#selectedSegment').val();
                    if (selectedSegment == 'All Segment') {
                        selectedSegment = null;
                    }

                    loadData(selectedSegment, selectedYears);
                });

                $('#selectedSegment').on('change', function() {
                    var selectedSegment = $('#selectedSegment').val();
                    var selectedYear = $('#selectedYear').val();
                    

                    if (selectedSegment == 'All Segment') {
                        selectedSegment = null;
                    }

                    loadData(selectedSegment, selectedYear);
                });

                

            })
        </script>

</body>

</html>