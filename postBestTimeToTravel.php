<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Best time to travel a city or country topics.
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

require 'utilities.php';
require 'simple_html_dom.php';
require 'vendor/autoload.php';

use League\HTMLToMarkdown\HtmlConverter;

$converter = new HtmlConverter(array('strip_tags' => true, 'use_autolinks' => false, 'hard_break' => true));
$master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";
// get all cities ids in which would insert topic.
$cities_category_ids = "https://api.craftypixels.com/categories";
$city_cat_id = json_decode(httpGet($cities_category_ids));
$all_cities_cat_ids = array();
foreach ($city_cat_id[0]->categoriesId as $citiesId) {
    array_push($all_cities_cat_ids, $citiesId);
}
// get all ids which is already traversed
$seed_url = "https://api.craftypixels.com/seeds/8";
$seed = json_decode(httpGet($seed_url));
$inserted_categories = $seed->categories;

// Generate a random category that is not already traversed
while (in_array(($random_category_id = $all_cities_cat_ids[array_rand($all_cities_cat_ids)]), $inserted_categories));
    $random_category = json_decode(httpGet("https://discussplaces.com/api/category/" . $random_category_id));
    echo "Looking into ". $random_category_id . "  " . $random_category->slug  . "\n";
    // Add newly traversed ID to seed log
    array_push($inserted_categories, $random_category_id);
    // The following code is about assinging and filtering ids slug etc.
    $cid = $random_category->cid;
    $random_category_name = $random_category->name;
    $slug = explode("/", $random_category->slug)[1];

    // if category is a city then we'll also add country in search for accurate results.
    $parent_slug = "";
    if (isset($random_category->parent)) {
        $parent_slug = explode("/", $random_category->parent->slug)[1];
    }

    // The following code is about scraping content from yahoo.com
    $url = "https://search.yahoo.com/search?fr=mcafee&type=E210US91215G91497&p=what+is+the+best+time+to+travel+to+".$slug."%3F". $parent_slug."%3F";
    $country_html = file_get_html($url);
        if ($country_html !== null) {
            $content = "";
            // Finding specefic content in page from yahoo.com
            foreach ($country_html->find('#web .compContainerUL li.va-top.ov-h') as $prices) {
                        $content .= trim($prices->plaintext);
                    }
            }
        // Convert to markdown
        $content = $converter->convert($content);
        $content = str_replace("&amp;", "and", $content);
        $content = str_replace("&lt;", "<", $content);
        $content = str_replace("&gt;", ">", $content);
        $content = str_replace("&#x27;", "", $content);
        $content = str_replace("$", "USD", $content);
        $content = str_replace("----------", "", $content);
        $content = preg_replace('/\[(.*?)\]\s*\((.*?)\)/', '$1:', $content);
        $PostContent = $content;
        $NameOfPost = "What is the best time to travel to  {$random_category_name} ?";
        echo  $PostContent . "\n";
        if (!empty($PostContent)) {
            // The following code is creating topic and the @createTopic function is in utilities.php
            createPostBestRest($cid, $NameOfPost, $PostContent, $NameOfPost);
            echo "Saving information " . $NameOfPost . "\n";
            //The following code Update CMS row as Hi insert this city coz this one is inserted.
            $seed->categories = $inserted_categories;
            updateCms($seed_url, json_encode($seed));
        } else {
            echo "Content not found " . "\n";
        }