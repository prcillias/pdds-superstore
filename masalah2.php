<?php
require 'connect.php';

// year filter
$sql = "SELECT DISTINCT YEAR(OrderDate) AS OrderYear FROM orders";
$stmt = $conn->query($sql)->fetchAll();

$ship_line_2015 = "SELECT 
                group_duration,
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
                    WHERE YEAR(o.orderdate) = 2015
                ) AS subquery
                GROUP BY group_duration";

                $stmt0 = $conn->query($ship_line_2015)->fetchAll();

                foreach ($stmt0 as $ship2015) {
                    $xLabel2015[] = $ship2015['group_duration'];
                    $yLabel2015[] = $ship2015['rating_percentage'];
                }

                $ship_line_2016 = "SELECT 
                group_duration,
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
                    WHERE YEAR(o.orderdate) = 2016
                ) AS subquery
                GROUP BY group_duration";

                $stmt2 = $conn->query($ship_line_2016)->fetchAll();

                foreach ($stmt2 as $ship) {
                    $xLabel2016[] = $ship['group_duration'];
                    $yLabel2016[] = $ship['rating_percentage'];
                }

                $ship_line_2017 = "SELECT 
                group_duration,
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
                    WHERE YEAR(o.orderdate) = 2017
                ) AS subquery
                GROUP BY group_duration";

                $stmt3 = $conn->query($ship_line_2017)->fetchAll();

                foreach ($stmt3 as $ship) {
                    $xLabel2017[] = $ship['group_duration'];
                    $yLabel2017[] = $ship['rating_percentage'];
                }

                $ship_line_2018 = "SELECT 
                group_duration,
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
                    WHERE YEAR(o.orderdate) = 2018
                ) AS subquery
                GROUP BY group_duration";

                $stmt4 = $conn->query($ship_line_2018)->fetchAll();

                foreach ($stmt4 as $ship) {
                    $xLabel2018[] = $ship['group_duration'];
                    $yLabel2018[] = $ship['rating_percentage'];
                }


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
    echo json_encode($stmt_ship);
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
    width: 90%;
    display: flex;
    font-family: 'Poppins', sans-serif;
    background-color: bisque;
    align-items: center;
    margin: 0 auto; /* Center the content */
}

.row {
    align-items: center;
    text-align: center;
    justify-content: center;
}

.year-box {
    display: inline-block;
    margin: 5px;
    padding: 10px;
    cursor: pointer;
    border: 1px solid #000;
    border-radius: 10px;
    background-color: pink;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.shipTable {
    font-size: 20px;
    padding: 20px;
}

.selected-year {
    background-color: gray;
    color: white;
}

main {
    margin: 0 auto; /* Center the content */
    padding: 10px;
}
</style>

<body>
    
<?php include "navbar.php" ?>
    <main class="container-fluid flex-column justify-content-center align-items-center" style="flex-grow: 1;">
        <div class="row">
            <h1>Processing Time</h1>
            <!-- Filter Year -->
            <div class="col-md-6">
                <?php
                foreach ($stmt as $order) {
                    echo '<div class="year-box" data-status="off" data-year="' . $order['OrderYear'] . '">' . $order['OrderYear'] . '</div>';
                }
                ?>
            </div>
        </div>
        <div class="row mt-3 justify-content-center align-items-center">
            <div class="col-md-12">
                <table id="shipTable" class="table table-striped table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Duration</th>
                            <th>Average Rating</th>
                            <th>Average Rating Percentage</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <canvas id="lineChart" width="600" height="400"></canvas>
            </div>
        </div>
    </main>


    <script>
        $(document).ready(function () {
            linechart();
            updateData();
            // for filter year
            selectedYears = [];
            $('.year-box').on('click', function () {
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
                    updateData(selectedYears)
                }

            });
            
            function linechart() {
                new Chart('lineChart', {
    type: "line",
    data: {
        labels: <?php echo json_encode($xLabel2015); ?>,
        datasets: [
            {
                label: "2015",
                data: <?php echo json_encode($yLabel2015); ?>,
                yAxisID: 'y-axis-1',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
            },
            {
                label: "2016",
                data: <?php echo json_encode($yLabel2016); ?>,
                yAxisID: 'y-axis-2',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
            },
            {
                label: "2017",
                data: <?php echo json_encode($yLabel2017); ?>,
                yAxisID: 'y-axis-3',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
            },
            {
                label: "2018",
                data: <?php echo json_encode($yLabel2018); ?>,
                yAxisID: 'y-axis-4',
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1,
            },
            // Add more datasets for other years as needed
        ]
    },
    options: {
        scales: {
            yAxes: [
                {
                    id: 'y-axis-1',
                    type: 'linear',
                    position: 'left',
                },
                {
                    id: 'y-axis-2',
                    type: 'linear',
                    position: 'right',
                },
                {
                    id: 'y-axis-3',
                    type: 'linear',
                    position: 'right',
                },
                {
                    id: 'y-axis-4',
                    type: 'linear',
                    position: 'right',
                },
                // Add more y-axes configurations if needed
            ]
        }
    }
});


}


            function updateData(selectedYear = []) {
                var tableBody = $('#shipTable tbody');
                tableBody.empty();

                $.ajax({
                    method: 'POST',
                    data: {
                        'year': selectedYear
                    },
                    success: function (e) {
                        var result = JSON.parse(e);
                        result.forEach(function (row) {
                            var newRow = '<tr>' +
                                '<td>' + row.group_duration + '</td>' +
                                '<td>' + row.avg_rating + '</td>' +
                                '<td>' + row.rating_percentage + '%</td>' +
                                '</tr>';
                            tableBody.append(newRow);

            
            

                        }
                        );
                        
                    }

                })


            }
     


        })
    </script>
    
</body>

</html>