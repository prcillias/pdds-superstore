<?php
require 'connect.php';

// year filter
$sql = "SELECT DISTINCT YEAR(OrderDate) AS OrderYear FROM orders";
$stmt = $conn->query($sql)->fetchAll();


$xLabel = [];
$yLabel = [];

$ship_line = "SELECT 
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
                    WHERE " . (empty($year) ? '1' : "YEAR(o.orderdate) IN (" . $year . ")") . "
                ) AS subquery
                GROUP BY group_duration";

                $stmt = $conn->query($ship_line)->fetchAll();

                foreach ($stmt as $ship) {
                    echo $ship['group_duration'];
                    echo $ship['rating_percentage'];
                }





?>

