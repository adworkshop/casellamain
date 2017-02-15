<?php
set_time_limit(300);

// create curl resource
$ch = curl_init();

// set url
curl_setopt($ch, CURLOPT_URL, "http://8.225.179.58/export/locations/nondrop.xml");

//return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($ch, CURLOPT_PORT, 8080);

// $output contains the output string
$output = curl_exec($ch);

// close curl resource to free up system resources
curl_close($ch);

print $output;
