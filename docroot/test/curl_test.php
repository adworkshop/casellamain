<?php
set_time_limit(600);

print 'Current IP: ' . $_SERVER['SERVER_ADDR'] . "\n<br />";
print 'Attempting to curl: "http://8.225.179.58/export/locations/nondrop.xml"' . "\n<br />";
print 'using curl_setopt to set the CURLOPT_PORT to 8080' . "\n<br />";

// create curl resource
$ch = curl_init();

// set url
curl_setopt($ch, CURLOPT_URL, "http://8.225.179.58/export/locations/nondrop.xml");

//return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($ch, CURLOPT_PORT, 8080);

// $output contains the output string
$output = curl_exec($ch);
$errors = curl_error($ch);

// close curl resource to free up system resources
curl_close($ch);

print 'output: ' . "\n<br />";
print $output . "\n<br />";
print 'errors?: '  . "\n<br />";
print $errors;

print "\n\n<br /><br />";

print 'Current IP: ' . $_SERVER['SERVER_ADDR'] . "\n<br />";
print 'Attempting to curl: "http://8.225.179.58:8080/export/locations/nondrop.xml"' . "\n<br />";

// create curl resource
$ch = curl_init();

// set url
curl_setopt($ch, CURLOPT_URL, "http://8.225.179.58:8080/export/locations/nondrop.xml");

//return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// $output contains the output string
$output = curl_exec($ch);
$errors = curl_error($ch);

// close curl resource to free up system resources
curl_close($ch);

print 'output: ' . "\n<br />";
print $output . "\n<br />";
print 'errors?: '  . "\n<br />";
print $errors;
