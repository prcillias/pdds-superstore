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