<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+\n
Scrape cost of living content - What is the cost of living in {city}, {country}?
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
$seed_url = "https://api.craftypixels.com/seeds/6";
$seed = json_decode(httpGet($seed_url));
$inserted_categories = $seed->categories;

// Generate a random category that is not already traversed
while (in_array(($random_category_id = $all_cities_cat_ids[array_rand($all_cities_cat_ids)]), $inserted_categories));
$random_category = json_decode(httpGet("https://discussplaces.com/api/category/" . $random_category_id));
echo $random_category_id . "\n";

// find city curency name 
$country = json_decode(httpGet("https://countries.craftypixels.com/v2/name/" . $random_category->parent->name));
$currencyname= $country[0]->currencies[0]->code;
// Add newly traversed ID to seed log
array_push($inserted_categories, $random_category_id);

// The following code is about assinging and filtering ids slug etc.
$slug = explode("/", $random_category->slug)[1];
$cid = $random_category->cid;
$random_category_name = $random_category->name;
$getSlug = [" ", "+", "-"];
$dilReplace   = [" ", " ", " "];
$filterSlug = str_replace($getSlug, $dilReplace, $slug);
$cap_slug=ucwords($filterSlug);
$againremove = [" ", "+"];
$againreplace   = ["-", "-"];
$cap_filter_slug = str_replace($againremove, $againreplace, $cap_slug);
// The following code is about scraping content from numbeo.com
$url = "https://www.numbeo.com/cost-of-living/in/" . $cap_filter_slug."?displayCurrency=".$currencyname;
echo $url . "\n";
$country_html = file_get_html($url);
if ($country_html !== null) {
    $content = "";
    // Finding specefic content in page from numbeo.com 
    $i=0;
    foreach ($country_html->find('.seeding-call  ul li') as $prices) {
        $content .= $prices. " ";
        // insert only first two line after break the loop
        if($i===1){
            break;
         }
        $i++;
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

    // $WrongLetter   = [ "$", "R$", "C$", "Kč", "kr", "€", "&#36", "Ft", "₪", "₹",  "¥", "RM", "&#36", "kr", "&#36", "₱", "zł", "£", "kr", "Fr", "฿", "₺",];
    // $CorrectLetter = ["USD", "BRL", "CAD", "CZK", "DKK", "EUR", "HKD", "HUF", "ILS", "INR", "JPY", "MYR", "MXN", "NOK", "NZD", "PHP", "PLN", "GBP", "SEK", "CHF", "THB", "TRY",];
    // $content = str_replace($WrongLetter, $CorrectLetter, trim($content));
   
    $removecont= ["(using our estimator):.", "(using our estimator)."]; 
    $content_filtered = str_replace($removecont, [". ",". "], $content);
    $first_post_content = $content_filtered; 
    $first_post_title = "What is the cost of living in {$random_category_name}, {$random_category->parent->name} ?";
    echo  $first_post_content;

    if (!empty($first_post_content)) {
            // The following code is creating topic and the createTopic function is in utilities.php
            createTopic(ucfirst(strtolower($random_category_name)), $cid, $first_post_title, $first_post_content, $first_post_title, $first_post_content, $first_post_title);
            echo "Saving information " . $first_post_title . "\n" . $first_post_content . "\n";
            //The following code Update CMS row as Hi insert this city coz this one is inserted.
            $seed->categories = $inserted_categories;
            updateCms($seed_url, json_encode($seed));
        } else {
            echo "not found " . "\n";
        }
    } else {
        echo "content not found " . "\n";
   }
