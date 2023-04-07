<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Script to create a category for each country
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

// ----------- Globals ----------------------------------
require 'utilities.php';
require 'simple_html_dom.php';

$master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";

$countries = json_decode(httpGet("https://restcountries.eu/rest/v2/all"));

foreach ($countries as $country) {
    echo $country->name . "\n";
    createCategory($country->name, $master_token);
    sleep(2);

}
