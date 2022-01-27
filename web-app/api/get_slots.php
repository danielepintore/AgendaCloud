<?php
/*
$date = new DateTime('10:00:00');
$date->add(new DateInterval('PT10H30S'));
echo $date->format('H:i:s') . "\n";
*/


include("utils.php");
header('Content-Type: application/json; charset=utf-8');
$slots = get_slots();
print(json_encode($slots));
