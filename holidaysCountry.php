<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Scrape holidays of country: example https://www.officeholidays.com/countries/pakistan/2021
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

// ----------- Globals ----------------------------------
require 'utilities.php';
require 'simple_html_dom.php';

// Get list of movie names from random category
$categories = (json_decode(httpGet("https://discussplaces.com/api/categories"))->categories);
$master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";

// Get relevant seed row from CMS so we don't relook exisitng categories
$seed_url = "https://api.craftypixels.com/seeds/5";
$seed = json_decode(httpGet($seed_url));
$inserted_categories = $seed->categories;

$all_category_ids = [];
// Lets save all category ids in an array
foreach ($categories as $category) {
    array_push($all_category_ids, $category->cid);
}

// Generate a random category that is not already traversed
while (in_array(($random_category_id = mt_rand(2, count($all_category_ids))), $inserted_categories));
$random_category = json_decode(httpGet("https://discussplaces.com/api/category/" . $random_category_id));

// Add newly traversed ID to seed log
array_push($inserted_categories, $random_category_id);
echo "CID : " . $random_category_id . "\n";

$slug = explode("/", $random_category->slug)[1];
$cid = $random_category->cid;
$random_category_name = $random_category->name;
$url = "https://www.officeholidays.com/countries/" . $slug;

echo $url . "\n";

$country_html = file_get_html($url);

if ($country_html != null) {
    echo "Looking into " . $url . "\n";
    $content = "";
    $count = 0;
    // Get some meta from api
    foreach ($country_html->find('tr') as $element) {
        $count++;
        if ($element->find('td')) {
            $content .= "* ";
        }
        foreach ($element->find('td') as $td) {
            //      echo $td->class. "\n";
            if ($td->class == "comments") {
                $content .= "is a " . trim($td->plaintext) . " ";
            } else if ($td->first_child()->itemprop == "startDate") {
                $content .= "**" . trim($td->plaintext) . ", 2021** ";
            } else if ($td->class == "hide-ipadmobile" && $td->plaintext) {
                $content .= "(" . trim($td->plaintext) . ") ";
            } else {
                $content .= trim($td->plaintext) . " ";
            }
        }
        $content .= "\n";
    }

    $content = str_replace('* \n', "", $content);
    $content = str_replace('&#039;', "", $content);
    $content = "# There are around " . $count . " holidays observed in " .
        $random_category_name . ". \n" .
        "## Here is the list of holidays in 2021 \n" .
        $content;

    $first_post_title = "Holidays in " . $random_category_name;
    $first_post_content = $content;
    $search_term = "holidays in " . $random_category_name;

    echo $content;
    if ($content != "") {
        if (createTopic(ucfirst(strtolower($random_category_name)), $cid, $first_post_title, $first_post_content, $first_post_title, $first_post_content, $search_term)) {
            echo "Saving information " . $first_post_title . "\n";
            //       echo $first_post_content . "\n";

            $count++;
        }
    } else {
        echo "ending_paragraph->plaintext " . "\n";
    }

}

// Update CMS row
$seed->categories = $inserted_categories;
$update_curl = "curl -X PUT '" . $seed_url . "' -H 'X-Auth-Token: ybVPd81oPuwIyygrONfhx1QRdXZ7_7xue0ZzqHBJHbq' -H 'X-User-Id: Ab68n9yNiGquXoenb' -H 'Content-type: application/json' -H 'Cookie: __cfduid=d5d2e4a3be95acf0de90dbe407705e6951606399091' --data-raw '" . json_encode($seed) . "'";
shell_exec($update_curl);
