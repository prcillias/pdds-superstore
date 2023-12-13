<?php
require 'connect.php';
require_once 'autoload.php';

$client = new MongoDB\Client();
$customers = $client->superstore->customers;
$products = $client->superstore->products;
//

// $sql_ship = "SELECT EXTRACT(YEAR FROM o.orderdate) as year, o.rating as rating, DATEDIFF(s.shipdate,o.orderdate) as duration,
// CASE 
//     WHEN DATEDIFF(s.shipdate,o.orderdate) <2 THEN '<2 days'
//     WHEN DATEDIFF(s.shipdate,o.orderdate) >= 2 AND DATEDIFF(s.shipdate,o.orderdate) <=4 THEN '2-4 days'
//     WHEN DATEDIFF(s.shipdate,o.orderdate) >4 THEN '>4 days'
//     END AS group_duration
// FROM orders o join shipping s on o.orderid = s.orderid
// order BY year ";

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
// echo json_encode($stmt_ship);





// $sql_ship_dur = "SELECT shipdate, orderdate, shipdate-orderdate FROM orders o join shipping s on o.orderid = s.orderid";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"
        integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <title>Document</title>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <?php
                foreach ($stmt_ship as $order) {
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
                            <th>Group Duration</th>
                            <th>Average Rating</th>
                            <th>Rating Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stmt_ship as $row): ?>
                            <tr>

                                <td>
                                    <?php echo $row['group_duration']; ?>
                                </td>
                                <td>
                                    <?php echo $row['avg_rating']; ?>
                                </td>
                                <td>
                                    <?php echo $row['rating_percentage'] . "%"; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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

    <script>
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
    </script>


</body>

</html>