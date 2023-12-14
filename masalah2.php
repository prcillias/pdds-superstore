<?php
require 'connect.php';
// require_once 'autoload.php';

// $client = new MongoDB\Client();
// $customers = $client->superstore->customers;
// $products = $client->superstore->products;
//

// $sql_ship = "SELECT EXTRACT(YEAR FROM o.orderdate) as year, o.rating as rating, DATEDIFF(s.shipdate,o.orderdate) as duration,
// CASE 
//     WHEN DATEDIFF(s.shipdate,o.orderdate) <2 THEN '<2 days'
//     WHEN DATEDIFF(s.shipdate,o.orderdate) >= 2 AND DATEDIFF(s.shipdate,o.orderdate) <=4 THEN '2-4 days'
//     WHEN DATEDIFF(s.shipdate,o.orderdate) >4 THEN '>4 days'
//     END AS group_duration
// FROM orders o join shipping s on o.orderid = s.orderid
// order BY year ";
// year filter
$sql = "SELECT DISTINCT YEAR(OrderDate) AS OrderYear FROM orders";
$stmt = $conn->query($sql)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

}
$sql_ship = "SELECT 
                group_duration,
                AVG(rating) AS avg_rating,
                round((AVG(rating) / 5) * 100,2) AS rating_percentage
            FROM (
                SELECT 
                    CASE 
                        WHEN DATEDIFF(s.shipdate, o.orderdate) < 2 THEN '<2 days'
                        WHEN DATEDIFF(s.shipdate, o.orderdate) >= 2 AND DATEDIFF(s.shipdate, o.orderdate) <= 4 THEN '2-4 days'
                        WHEN DATEDIFF(s.shipdate, o.orderdate) > 4 THEN '>4 days'
                    END AS group_duration,
                    o.rating AS rating
                FROM orders o 
                JOIN shipping s ON o.orderid = s.orderid
            ) AS subquery
            GROUP BY group_duration";



$stmt_ship = $conn->query($sql_ship)->fetchAll();
$ship_data_json = json_encode($stmt_ship);
// echo json_encode($stmt_ship);





// $sql_ship_dur = "SELECT shipdate, orderdate, shipdate-orderdate FROM orders o join shipping s on o.orderid = s.orderid";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project PDDS</title>
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- AJAX -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
    <!-- SWEET ALERT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- JQUERY -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- <link rel="stylesheet" href="https://code.jquery.com/mobile/1.5.0-alpha.1/jquery.mobile-1.5.0-alpha.1.min.css"> -->
    <!-- <script src="https://code.jquery.com/mobile/1.5.0-alpha.1/jquery.mobile-1.5.0-alpha.1.min.js"></script> -->
    <!-- CHART.JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <script src="https://cdn.canvasjs.com/ga/canvasjs.min.js"></script>
    <!-- FONT -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
</head>
<style>
     body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
    }

    .year-box {
        display: inline-block;
        margin: 5px;
        padding: 10px;
        cursor: pointer;
        border: 1px solid #000; /* Border color */
    }

    .selected-year {
        background-color: gray;
        color: white;
    }
</style>

<body>
    <div class="container">
        <div class="row">
            <!-- Filter Year -->
            <div class="col-md-6">
                <?php
                foreach ($stmt as $order) {
                    echo '<div class="year-box" data-status="off" data-year="' . $order['OrderYear'] . '">' . $order['OrderYear'] . '</div>';
                }
                ?>
            </div>
        </div>
        <div class="row">
            <h1>Processing Time</h1>
            <div class="col-md-12">
                <table id="shipTable" class="table table-striped table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Duration</th>
                            <th>Average Rating</th>
                            <th>Average Rating Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

            </div>
        </div>
        <div class="row">
            <!-- HTML Canvas element to render the line chart -->
            <canvas id="lineChart"></canvas>
        </div>

    </div>

    </div>

    <!-- <script>
        $(document).ready(function () {
            $('#shipTable').DataTable({
                "columnDefs": [{
                    "className": "dt-center",
                    "targets": "_all"
                }],
            });
        });
    </script>

    <script>

        // Assuming $stmt_ship holds the data obtained from the SQL query

        // Extracting data from PHP array to JavaScript variables
        const avgRatings = <?php echo json_encode(array_column($stmt_ship, 'avg_rating')); ?>;
        const ratingPercentages = <?php echo json_encode(array_column($stmt_ship, 'rating_percentage')); ?>;

        // X-axis labels in the desired order
        const labels = ['<2 days', '2-4 days', '>4 days'];

        // Calculate trendline data (simple linear regression)
        // (Assuming ratingPercentages array already corresponds to '<2 days', '2-4 days', and '>4 days' order)
        const trendline = [];
        const trendlineGradient = (ratingPercentages[ratingPercentages.length - 1] - ratingPercentages[0]) / (ratingPercentages.length - 1);
        for (let i = 0; i < ratingPercentages.length; i++) {
            const value = ratingPercentages[0] + trendlineGradient * i;
            trendline.push(value.toFixed(2));
        }

        // Creating a line chart using Chart.js
        const ctx = document.getElementById('lineChart').getContext('2d');
        const lineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels, // Set X-axis labels in the desired order
                datasets: [{
                    label: 'Average Rating',
                    data: avgRatings, // Y-axis data for average ratings
                    borderColor: 'blue', // Line color
                    fill: false // No fill beneath the line
                }, {
                    label: 'Rating Percentage',
                    data: ratingPercentages, // Y-axis data for rating percentages
                    borderColor: 'green', // Line color
                    fill: false // No fill beneath the line
                }, {
                    label: 'Trendline',
                    data: trendline, // Trendline data
                    borderColor: 'red', // Line color for trendline
                    fill: false, // No fill beneath the line
                    borderDash: [5, 5], // Dashed line for trendline
                }]
            },
            options: {
                // Chart options, customize as needed
                responsive: true,
                maintainAspectRatio: false,
                // ...other chart configurations
            }
        });
    </script> -->

    <script>
        $(document).ready(function() {
            // for filter year
            selectedYears = [];
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

                // updateData(selectedYear)

            });

            function updateData(selectedYear = []) {
                var tableBody = $('#shipTable tbody');
                tableBody.empty();

                $.ajax({
                    method: 'POST',
                    data: {
                        'year': selectedYear
                    },
                    success: function(e) {
                        var result = JSON.parse(e);
                        var jumlahOrders = result.stmt2;
                        var productsName = result.cursor3;
                        var tableBody = $('#ordersTable tbody');
                        tableBody.empty();
                        

                    }
                
                })

                data.forEach(function(row) {
                    var newRow = '<tr>' +
                        '<td>' + row.group_duration + '</td>' +
                        '<td>' + row.avg_rating + '</td>' +
                        '<td>' + row.rating_percentage + '%</td>' +
                        '</tr>';

                    tableBody.append(newRow);
                });
            }

            // function updateData(data, selectedYear = []) {
            //     var tableBody = $('#shipTable tbody');
            //     tableBody.empty();

            //     data.forEach(function(row) {
            //         var newRow = '<tr>' +
            //             '<td>' + row.group_duration + '</td>' +
            //             '<td>' + row.avg_rating + '</td>' +
            //             '<td>' + row.rating_percentage + '%</td>' +
            //             '</tr>';

            //         tableBody.append(newRow);
            //     });
            // }

            // var shipData = <?php echo $ship_data_json; ?>;
            // updateData(shipData)
        
        })
    </script>

</body>

</html>