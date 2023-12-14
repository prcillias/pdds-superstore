<?php
require 'connect.php';

// year filter
$sql = "SELECT DISTINCT YEAR(OrderDate) AS OrderYear FROM orders";
$stmt = $conn->query($sql)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['year'])) {
        $year = $_POST['year'];
        
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
                    WHERE " . (empty($year) ? '1' : "YEAR(o.orderdate) IN (" . $year . ")") . "
                ) AS subquery
                GROUP BY group_duration";



    $stmt_ship = $conn->query($sql_ship)->fetchAll();
    // $ship_data_json = json_encode($stmt_ship);
    echo json_encode($stmt_ship);
    exit;





    // $sql_ship_dur = "SELECT shipdate, orderdate, shipdate-orderdate FROM orders o join shipping s on o.orderid = s.orderid";
}

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
            <canvas id="lineChart"></canvas>
        </div>

    </div>

    </div>

    <script>
        $(document).ready(function() {
            updateData();
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

                updateData(selectedYear)

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
                        result.forEach(function(row) {
                            var newRow = '<tr>' +
                                '<td>' + row.group_duration + '</td>' +
                                '<td>' + row.avg_rating + '</td>' +
                                '<td>' + row.rating_percentage + '%</td>' +
                                '</tr>';
                            tableBody.append(newRow);
                        });
                    }
                
                })

                
            }
            
        
        })
    </script>

</body>

</html>