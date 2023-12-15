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
            $category[] = $product['Category'];
            $subcategory[] = $product['Subcategory'];
        }
    }

    $sql2 = "SELECT COUNT(DISTINCT CustomerID) AS totalCustomer, COUNT(DISTINCT ProductID) AS totalProduct, COUNT(DISTINCT OrderID) AS totalOrders, ROUND(AVG(Sales), 1) AS avgSales
                FROM orders
                WHERE " . (empty($customerIds) ? '1' : "CustomerID IN (" . implode(",", $customerIds) . ")") . (empty($year) ? '' : " AND YEAR(OrderDate) IN (" . implode(",", $year) . ")") . "";

    $stmt3 = $conn->query($sql2)->fetchAll();

    foreach ($stmt3 as $row) {
        $totalCustomer = $row['totalCustomer'];
        $totalProduct = $row['totalProduct'];
        $totalOrders = $row['totalOrders'];
        $avgSales = $row['avgSales'];
    }

    $response['stmt2'] = $stmt2;
    $response['cursor3'] = $cursor3;
    $response['totalCustomer'] = $totalCustomer;
    $response['totalProduct'] = $totalProduct;
    $response['totalOrders'] = $totalOrders;
    $response['avgSales'] = $avgSales;
    $response['category'] = $category;
    $response['subcategory'] = $subcategory;

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
        body{
        width: 90%;
        display: flex;
        }
        .combined-container {
            margin-top: 20px;
            padding: 15px;
        }

        .table-container,
        .chart-container {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .chart-container canvas {
            max-width: 100%;
        }

        h2 {
            font-size: 50px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .year-box {
            display: inline-block;
            margin: 5px;
            padding: 15px;
            cursor: pointer;
            border: 2px solid #725C3F;
            text-align: center;
            font-size: 20px;
            color: #725C3F;
            line-height: 1.5;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        [class^="kpi"] {
            width: 250px;
            height: 105px;
            border: 1px solid #725C3F;
            border-radius: 10px;
            margin: 10px;
            padding: 10px;
            box-sizing: border-box;
            display: inline-block;
            text-align: center;
            font-size: 45px;
            color: #000;
            line-height: 50px;
            background-color: #E5ADA8;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        [class^="kpi"] p {
            font-size: 18px;
            margin: 0;
        }

        .selected-year {
            background-color: #725C3F;
            color: #E5e0d8;
        }

        .form-select {
            font-size: 25px;
            color: #725C3F;
            padding: 10px;
            text-align: center;
            border: 2px solid #725C3F;
            background-color: transparent;
            appearance: none;
            -webkit-appearance: none;
        }

        #ordersTable th,
        #ordersTable td {
            font-size: 18px;
            padding: 15px;
        }

        .table thead th {
            background-color: #725C3F;
            color: #EFE8D8;
        }

        .table tbody tr:hover {
            background-color: #D0A778;
            color: #EFE8D8;
        }

        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            z-index: 1000;
            background-color: #725C3F;
            color: #EFE8D8;
            padding-top: 20px;
        }

        #sidebar ul.nav flex-column {
            padding-left: 0;
            list-style: none;
        }

        #sidebar .nav-link {
            color: #ffffff;
        }

        #sidebar .nav-link:hover {
            color: #EFE8D8;
            background-color: #D0A778;
        }

        #sidebar .active {
            color: #725C3F;
            background-color: #EFE8D8;
        }

        main {
            margin-left: 80px;
            padding: 20px;
        }

        body {
            background-color: #E5e0d8;
        }

        h2,
        h3 {
            color: #725C3F;
        }

        .barChart {
            font-family: Poppins;
        }
    </style>
</head>

<body>
    <?php include "navbar.php" ?>
    <div class="container-fluid flex-column" style="flex-grow: 1;">
        <div class="row">
            <!-- <nav id="sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="masalah1.php">
                                <i class="bi bi-house-door"></i> Order
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="masalah2.php">
                                <i class="bi bi-person"></i> Shipping
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="masalah3.php">
                                <i class="bi bi-box"></i> Refund
                            </a>
                        </li>
                    </ul>
                </div>
            </nav> -->

            <main>
                <h2 class="text-center mb-4" id="title">Superstore Order Report</h2>
                <div class="container container-filter">
                    <div class="row">
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
                </div>

                <div class="container mt-3 combined-container">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="kpiCustomer-box">

                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="kpiProduct-box">

                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="kpiOrders-box">

                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="kpiSales-box">

                            </div>
                        </div>
                    </div>

                    <div class="row mt-3 text-center">
                        <h3 class="mx-auto" id="subtitle"></h3>
                    </div>


                    <!-- Table Container -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="table-container">
                                <table id="ordersTable" class="table table-bordered table-hover table-sm">
                                    <thead class="thead">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Category</th>
                                            <th>Sub-Category</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Table body content will be dynamically populated -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Chart Container -->
                        <div class="col-md-6">
                            <div class="chart-container">
                                <canvas id="barChart" width="400" height="280"></canvas>
                            </div>

                        </div>
                    </div>

                </div>
            </main>

        </div>
    </div>

    <script>
        $(document).ready(function() {
            selectedYears = [];
            loadData();
            updateSubtitle();

            function loadData(selectedSegment = null, selectedYear = []) {
                $.ajax({
                    method: 'POST',
                    data: {
                        'segment': selectedSegment,
                        'year': selectedYear
                    },
                    success: function(e) {
                        var result = JSON.parse(e);
                        var totalCustomer = result.totalCustomer;
                        var totalProduct = result.totalProduct;
                        var totalOrders = result.totalOrders;
                        var avgSales = result.avgSales;
                        var jumlahOrders = result.stmt2;
                        var productsName = result.cursor3;
                        var categoryProduct = result.category;
                        var subcategoryProduct = result.subcategory;
                        var tableBody = $('#ordersTable tbody');
                        tableBody.empty();
                        for (var i = 0; i < jumlahOrders.length; i++) {
                            var jumlahOrder = jumlahOrders[i].JumlahOrder;
                            var productName = productsName[i];
                            var category = categoryProduct[i];
                            var subcategory = subcategoryProduct[i];
                            var newRow = "<tr>" +
                                "<td>" + productName + "</td>" +
                                "<td>" + category + "</td>" +
                                "<td>" + subcategory + "</td>" +
                                "</tr>";
                            tableBody.append(newRow);
                        }

                        // KPI
                        $('.kpiCustomer-box').text(totalCustomer).append('<p>Total Customers</p>');
                        $('.kpiProduct-box').text(totalProduct).append('<p>Products Ordered</p>');
                        $('.kpiOrders-box').text(totalOrders).append('<p>Total Order</p>');
                        $('.kpiSales-box').text(avgSales).append('<p>Average Sales</p>');

                        // Bar Chart
                        new Chart('barChart', {
                            type: "horizontalBar",
                            data: {
                                labels: productsName,
                                datasets: [{
                                    fill: false,
                                    lineTension: 0,
                                    backgroundColor: "#e5ada8",
                                    borderColor: "#e5ada8",
                                    data: jumlahOrders.map(order => order.JumlahOrder)
                                }]
                            },
                            options: {
                                legend: {
                                    display: false
                                },
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            fontColor: '#725C3F',
                                            fontFamily: 'Poppins, sans-serif'
                                        }
                                    }],
                                    xAxes: [{
                                        ticks: {
                                            min: 0,
                                            max: Math.max(...jumlahOrders.map(order => order.JumlahOrder)) + 1,
                                            fontColor: '#725C3F',
                                            fontFamily: 'Poppins, sans-serif'
                                        }
                                    }],
                                },
                                title: {
                                    display: true,
                                    text: '',
                                    fontColor: '#725c3f',
                                    fontSize: 20,
                                    fontFamily: 'Poppins, sans-serif' 
                                }
                            }
                        });

                    }
                });
            }

            // Filter Year
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

                updateSubtitle(selectedSegment, selectedYears);
                loadData(selectedSegment, selectedYears);
            });

            // Filter Customer Segment
            $('#selectedSegment').on('change', function() {
                var selectedSegment = $('#selectedSegment').val();
                var selectedYear = $('#selectedYear').val();

                if (selectedSegment == 'All Segment') {
                    selectedSegment = null;
                }

                updateSubtitle(selectedSegment, selectedYears);
                loadData(selectedSegment, selectedYear);
            });

            // Update Subtitle
            function updateSubtitle(segment = null, years = []) {
                var subtitle = 'Top 5 Products By ';

                if (segment) {
                    subtitle += segment + ' Segment';
                } else {
                    subtitle += 'All Segment';
                }

                if (years.length > 0) {
                    subtitle += ' In ' + years.join(', ');
                } else {
                    subtitle += ' In All Years';
                }
                $('#subtitle').text(subtitle);
            }



        })
    </script>

</body>

</html>