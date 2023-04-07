<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Scrape Airlines that fly to city https://www.britannica.com/topic/flag-of-Nepal
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

// ----------- Globals ----------------------------------
require 'utilities.php';
require 'simple_html_dom.php';

// Get list of movie names from random category
$categories = (json_decode(httpGet("https://discussplaces.com/api/categories"))->categories);
$master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";

// Generate a random category
$random_category = $categories[array_rand($categories, 1)];
$slug = explode("/", $random_category->slug)[1];
$cid = $random_category->cid;
$random_category_name = $random_category->name;
$url = "https://www.britannica.com/topic/flag-of-" . $slug;

echo $url . "\n";

$country_html = file_get_html($url);

if ($country_html != null) {
    echo "Looking into " . $url . "\n";
    $content = "";
    // Get some meta from api
    $api_data = json_decode(httpGet("https://countries.craftypixels.com/v2/name/" . $random_category_name));
    $content .= "[![Picture of " . $random_category_name . " Flag](" . $api_data[0]->flag . ")](https://discussplaces.com/category/" . $random_category->slug . ")" . "\n";
    $content .= "## Description of flag of " . $random_category_name . "\n\n";
    foreach ($country_html->find('figcaption')[0] as $element) {
        $content .= trim($element->plaintext);
    }
    $first_post_title = "Flag of " . $random_category_name;
    $first_post_content = $content;
    $search_term = "Flag of " . $random_category_name;

    echo $content;
    if ($content != "") {
        if (createTopicOrPost(ucfirst(strtolower($random_category_name)), $cid, $first_post_title, $first_post_content, $first_post_title, $first_post_content, $search_term)) {
            echo "Saving information " . $first_post_title . "\n";
            //       echo $first_post_content . "\n";

            $count++;
        }
    } else {
        echo "ending_paragraph->plaintext " . "\n";
    }

}
