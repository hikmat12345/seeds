<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Scrape stock exchange trading hours
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

$slug = "united-kingdom";
$cid = 89;
$random_category_name = "Germany";

$url = "https://www.tradinghours.com/exchanges";

echo $url . "\n";
$exchange_html = file_get_html($url);

if ($exchange_html != null) {
    echo "Looking into " . $url . " " . $random_category_name . "\n";
    $content = "";

    foreach ($categories as $country) {
        $content = "";
        $cid = $country->cid;
        $random_category_name = $country->name;

        foreach ($exchange_html->find('#exchangetable') as $element) {
            foreach ($element->find('tr') as $row) {
                foreach ($row->find('.flag-small') as $flag) {
                    if ($random_category_name == $flag->title) {
                        foreach ($row->find('a') as $anchor) {
                            if (strlen($anchor->plaintext) >= 2) {
                                $content .= "* " . trim($anchor->plaintext) . " opens at " . trim($anchor->parent()->parent()->next_sibling()->plaintext) . " \n";
                            }

                        }
                    }

                }
            }
        }

        $api_data = json_decode(httpGet("https://countries.craftypixels.com/v2/name/" . $random_category_name));
        $first_post_title = "When does " . $api_data[0]->demonym . " stock market open?";
        $first_post_content = $content;
        $search_term = "When does " . $api_data[0]->demonym . " stock market open?";

      //   echo $content;
        if (strlen($content) >= 50) {
            if (createTopicOrPost(ucfirst(strtolower($random_category_name)), $cid, $first_post_title, $first_post_content, $first_post_title, $first_post_content, $search_term)) {
                echo "Saving information " . $first_post_title . "\n";
                //       echo $first_post_content . "\n";

                $count++;
            }
        } else {
            echo "no content found " . "\n";
        }
    }
}
