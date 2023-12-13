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

$customerIds = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['segment'])) {
    $segment = $_POST['segment'];
    $slicer = ($segment !== '') ? ['Segment' => $segment] : [];
    $cursor2 = $customers->find($slicer);
    $customerIds .= '(';
    foreach ($cursor2 as $customer) {
        $customerIds .= "'" . $customer['Customer ID'] . "', ";
    }
    $customerIds = rtrim($customerIds, ', ');
    $customerIds .= ')';
  }

  if (isset($_POST['year'])) {
    $year = $_POST['year'];

  }

  $sqlCommon = "SELECT ProductID, COUNT(ProductID) AS JumlahOrder
                FROM orders
                WHERE " . (empty($customerIds) ? '1' : "CustomerID IN $customerIds") . (empty($year) ? '' : " AND YEAR(OrderDate) = " . $year) . "
                GROUP BY ProductID
                ORDER BY JumlahOrder DESC
                LIMIT 5";
    
    $stmt2 = $conn->query($sqlCommon)->fetchAll();

  foreach ($stmt2 as $row) {
    $productID = $row['ProductID'];
    $productIDs[] = $productID;
  }

  $cursor3 = [];
  
  foreach ($productIDs as $productID) {
    $cursor33 = $products->find(['Product ID' => $productIDs]);
    $cursor3[] = $cursor33['Product Name'];
    echo $cursor33['Product Name'];
  }

//   $cursor3 = $products->find(['Product ID' => ['$in' => $productIDs]]);

  $response['stmt2'] = $stmt2;
//   $response['cursor3'] = iterator_to_array($cursor3);
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
  <!-- <link rel="stylesheet" href="https://code.jquery.com/mobile/1.5.0-alpha.1/jquery.mobile-1.5.0-alpha.1.min.css"> -->
  <!-- <script src="https://code.jquery.com/mobile/1.5.0-alpha.1/jquery.mobile-1.5.0-alpha.1.min.js"></script> -->
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
</head>
<body>
  <div class="container p-3">
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
          <div class="col-md-3">
              <select class="form-select" id="selectedSegment" aria-label="Default select example">
                  <option selected>All Segment</option>
                  <?php
                  foreach ($cursor as $segment) {
                      echo '<option value="' . $segment . '">' . $segment . '</option>';
                  }
                  ?>
              </select>
          </div>
          
          <!-- Go Button -->
          <div class="col-md-3">
              <button type="button" class="btn btn-primary" id="goBtn">Go</button>
          </div>
      </div>

      <div class="row mt-3">
          <div class="col-md-12">
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
      </div>
  </div>

  <script>
    $(document).ready(function() {
      loadData();

      function loadData(selectedSegment=null, selectedYear=[]) {
        $.ajax({
            method: 'POST',
            data: {
                'segment': selectedSegment,
                'year': selectedYear
            },
            success: function (e) {
                // var result = JSON.parse(e);
                // var jumlahOrders = result.stmt2;
                // var productsName = result.cursor3;
                // var tableBody = $('#ordersTable tbody');
                // tableBody.empty();
                // for (var i = 0; i < jumlahOrders.length; i++) {
                //     var jumlahOrder = jumlahOrders[i].JumlahOrder;
                //     var productName = productsName[i]['Product Name'];
                //     var newRow = "<tr>" +
                //         "<td>" + productName + "</td>" +
                //         "<td>" + jumlahOrder + "</td>" +
                //         "</tr>";
                //     tableBody.append(newRow);
                // }
            }
          });
      }

    $('.year-box').on('click', function () {
        var status = $(this).data('status');
        if (status == 'off') {
            $(this).addClass('selected-year');
            $(this).data('status', 'on');
        }else {
            $(this).removeClass('selected-year');
            $(this).data('status', 'off');
        }
        
        selectedYear = $(this).data('year');

        selectedSegment = $('#selectedSegment').val();
        if (selectedSegment == 'All Segment'){
            selectedSegment = null;
        }

        var selectedYears = $('.year-box.selected-year').map(function () {
            return $(this).data('year');
        }).get();

        loadData(selectedSegment, selectedYear);
    });

      $('#selectedSegment').on('change', function() {
          var selectedSegment = $('#selectedSegment').val();
          var selectedYear = $('#selectedYear').val();

          if (selectedYear == 'All Year') {
              selectedYear = null;
          }

          if (selectedSegment == 'All Segment') {
              selectedSegment = null;
          }

          loadData(selectedSegment, selectedYear);
      });

    })
  </script>
    
</body>
</html>