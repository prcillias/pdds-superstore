<?php 
require_once 'autoload.php'; 
require 'connect2.php';

$client = new MongoDB\Client();
$customers = $client->superstore->customers;

$regionList = $customers->distinct('Region');

$refundsJoin = "SELECT * FROM refunds";
$result2 = $conn2->query($refundsJoin);

$pie = "SELECT Region, Count(RefundID) AS Total
         FROM REFUNDS
         GROUP BY Region";
$resultPie = $conn2->query($pie);
$pieLabels = [];
$pieValues = [];
while ($row = $resultPie->fetch_assoc()) {
    $pieLabels[] = $row['Region'];
    $pieValues[] = $row['Total'];
}

$customerIDsArray = [];
while ($row = $result2->fetch_assoc()) {
    $customerIDsArray[] = $row['CustomerID'];
}
$cursor = $customers->find(
    ['Customer ID' => ['$in' => $customerIDsArray]],
    ['projection' => ['_id' => 0, 'Customer ID' => 1, 'Region' => 1, 'State' => 1, 'City' => 1]]
);

foreach ($cursor as $document) {
    $customerID = $document['Customer ID'];
    $region = $document['Region'];
    $state = $document['State'];
    $city = $document['City'];

    $updateQuery = "UPDATE refunds SET Region = '$region', State = '$state', City = '$city' WHERE CustomerID = '$customerID'";
    $conn2->query($updateQuery);
}

$customerIds = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['region'])) {
        $selectedRegion = isset($_POST['region']) ? $_POST['region'] : null;
    
        $sql = "SELECT State, COUNT(RefundID) as Total
                FROM refunds
                WHERE Region = '$selectedRegion'
                GROUP BY State
                ORDER BY Total DESC
                LIMIT 3";
        $result = $conn2->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode($data);
        exit;
    }

    if (isset($_POST['state'])) {
        $selectedState = isset($_POST['state']) ? $_POST['state'] : null;
        $getRegion = "SELECT Region
                    FROM refunds
                    WHERE State = '$selectedState'
                    LIMIT 1";
        $get = $conn2->query($getRegion);
        $get2 = $get->fetch_all(MYSQLI_ASSOC);
    
        $sql = "SELECT City, COUNT(RefundID) as Total
                FROM refunds
                WHERE State = '$selectedState'
                GROUP BY City
                ORDER BY Total DESC
                LIMIT 3";
        $result = $conn2->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['region' => $get2,'data' => $data, 'state2' => $selectedState]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>State with the Most Count of Refunds in a Region</title>
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- AJAX -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <!-- SWEET ALERT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CHART -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <script src="https://cdn.canvasjs.com/ga/canvasjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>

</head>
<body>
<?php include "navbar.php" ?>
<div class="flex-column" style="flex-grow: 1;">
<div class="p-0 m-0 modal fade" id="modalsucc" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel"><b>Test</b></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="row isimodalbagi2 suksesmodal">
                <div class="baris">
                    <div ><b id="suksestit">City</b></div>
                </div>
                <div class="baris">
                    <div class="">-</div>
                </div>
            </div>
            <div class="row isimodalbagi2 gagalmodal">
                <div class="baris">
                    <div ><b id="gagaltit">Count</b></div>
                </div>
                <div class="baris">
                    <div class=""><b>-</b></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>

</div>
<div class="filterWrap">
    <h2>State with the Most Count of Refunds in a Region</h2>
    <div class="filterWrapMini">
        <div class="input-group mb-3">
            <select class="form-select" id="filter" aria-label="Floating label select example">
                <option selected hidden>Region</option>
                <option>None</option>
                <?php foreach($regionList as $r): ?>
                <option ><?= $r ?></option>
                <?php endforeach; ?>
            </select> 
        <button class="btn btn-primary" type="submit" id="getData">Submit</button>
        </div>
    </div>
    <div class="noData"></div>
</div>
<div >
    <div class="chart-container">
        <!-- <canvas id="barChart" width="400" height="150"></canvas> -->
        <canvas id="pieChart" width="600" height="150"></canvas>
    </div>
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
<div >
    <div class="chart-container">
        <div class="chart-wrap">
            <canvas id="barChart" width="500" height="150"></canvas>
            <!-- <canvas id="pieChart" width="400" height="150"></canvas> -->
        </div>

    </div>
</div>
</div>
<script>
     $(document).ready(function(){
        pieChart();
        $('#getData').on('click',function(){
            $inputRegion = $('#filter').val()

            if($inputRegion == 'Region' || $inputRegion == 'None'){
                exit;
            }
            $.ajax({
                method: 'POST',
                data: {
                    region: $inputRegion
                },

                success: function (response) {
                    // alert(response);
                    var filteredData = JSON.parse(response);

                    var pie = $('#pieChart');
                    pie.remove();

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

                    var labels = [];
                    var dataValues = [];

                    filteredData.forEach(function (item) {
                        var row = '<tr>';
                        row += '<td><button class="btn btn-secondary getCities">' + (item["State"] || '') + '</button></td>';
                        row += '<td>' + (item["Total"] || '') + '</td>';
                        row += '</tr>';
                        tbody.append(row);
                        labels.push(item["State"] || '');
                        dataValues.push(item["Total"] || '');
                    });

                    new Chart('barChart', {
                        type: "horizontalBar",
                        data: {
                            labels: labels,
                            datasets: [{
                                fill: false,
                                lineTension: 0,
                                backgroundColor: "#e5ada8",
                                borderColor: "#e5ada8",
                                data: dataValues
                            }]
                        },
                        options: {
                            legend: {
                                display: false
                            },
                            layout: {
                                padding: {
                                    top: -30
                                }
                            },
                            scales: {
                                yAxes: [{}],
                                xAxes: [{
                                    ticks: {
                                        beginAtZero: true
                                    }
                                    // maxBarThickness: 30,
                                }],
                            },
                            title: {
                                display: true,
                                text: '',
                                fontColor: '#725c3f',
                                fontSize: 20
                            }
                        }
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

        function pieChart(){
            new Chart('pieChart', {
                type: "pie",
                data: {
                    labels: <?php echo json_encode($pieLabels); ?>,
                    datasets: [{
                        backgroundColor: ["#e5ada8", "#ffa07a", "#98fb98"], // Adjust colors as needed
                        borderColor: "#fff",
                        borderWidth: 1,
                        data: <?php echo json_encode($pieValues); ?>
                    }]
                },
                options: {
                    legend: {
                        display: false
                    },
                    layout: {
                        padding: {
                            top: -30
                        }
                    },
                    plugins: {
                        datalabels: {
                            formatter: function (value, context) {
                                const label = context.chart.data.labels[context.dataIndex];
                                const percentage = ((value / 112) * 100).toFixed(2) + '%';
                                return `${label}\n${percentage}`;
                            }
                        }
                     },
                    title: {
                        display: true,
                        text: '',
                        fontColor: '#725c3f',
                        fontSize: 20
                    }
                }
        });
        }

        $(document.body).on('click', '.getCities', function () {
            $state = $(this).text();
            // console.log($state)
            $.ajax({
                method: 'POST',
                data: {
                    state: $state
                },

                success: function (response) {
                    // alert(response);

                    var data = JSON.parse(response);
                    var filteredData = data.data;

                    var citycolumn = '<div class="baris"><div ><b id="suksestit">City</b></div></div>';
                    var countcolumn = '<div class="baris"><div ><b id="gagaltit">Count</b></div></div>';

                    filteredData.forEach(function (item) {

                        citycolumn = citycolumn + '<div class="baris"><div class="">' + (item["City"] || '') + '</div></div>';
                        countcolumn = countcolumn + '<div class="baris"><div class="">' + (item["Total"] || '') + '</div></div>';
                    });

                    $(".modal-title").html("<b>"+data.state2+"</b>");

                    $(".suksesmodal").html(citycolumn);
                    $(".gagalmodal").html(countcolumn);
                    $("#modalsucc").modal('show');
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
        });
    });
</script>
</body>
<style>
    h2{
        width: 100%;
        text-align: center;
        color: #FF8787;
    }

    body{
        width: 90%;
        display: flex;
        /* justify-content: center; */
        /* flex-wrap: wrap; */
        background-color: #FFEECC;
    }
    .filterWrap{
        width: 100%;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        margin: 30px 0 0 0 ;
    }
    .filterWrapMini{
        width: 100%;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        max-width: 500px;
    }
    .table-wrap{
        width: 100%;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        margin: 30px 0 0 0 ;
    }
    .table-wrap-mini{
        width: 95%;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        max-width: 1000px;
        overflow-x: auto;
    }
    table {
        border-collapse: collapse;
        width: 100%;
    }
    th, td {
        border: 1px solid black;
        text-align: center;
        padding: 8px;
        vertical-align: middle;
    }
    th{
        background-color: #FF8787 !important; 
    }
    thead {
        text-align: center;
    }
    button{
        background-color: #FF8787 !important;
        border: 1px solid pink Imp !important;
        border-radius: 50px;
    }
    
    .fullscreen{
        width: 100vw;
    }

    .ufwrap-wrap{
        text-align: center;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
    }

    .ufwrap {
        /* margin-bottom: 20px; */
        width: 95%;
        max-width: 500px;
    }

    #csvdd{
        margin: 0 0 20px 0;
    }

    #file-input {
        margin-bottom: 10px;
    }

    .modalsubtitle{
        /* color: red; */
        margin-bottom: 10px;
    }

    #status {
        font-weight: bold;
        color: green;
    }

    .modal-body{
        margin: 10px 0 0 0;
        padding: 0 40px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        width: 97%;
    }

    /* On screens that are 600px or less, set the background color to olive */
    @media screen and (max-width: 600px) {
        .modal-body{
            padding: 0 10px;
        }
    }

    .isimodalbagi2{
        width: 50%;
    }

    .gagalmodal{
        display: flex !important;
        justify-content: flex-end !important;
    }

    .gagalmodal .baris div{
        text-align: end;
    }

    .modal-body {
    margin: 10px 0 0 0;
    padding: 0 40px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    width: 97%;
    text-align: center; 
    }

    .isimodalbagi2 div {
        text-align: center;
    }

    .baris {
        text-align: center;
    }

    .modal-title {
        text-align: center;
    }

    body {
        width: 100%;
    }
    .chart-container{
        width: 100%;
        display: flex;
        justify-content: center;
    }
    .chart-wrap{
        width: 70%;
        padding-bottom: 40px;
    }
</style>
</html>