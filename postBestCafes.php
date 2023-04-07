<?php

// echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
// Scrape Best Resturants - What are best resturant in {city}?
// +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

// ----------- Globals ----------------------------------
require 'utilities.php';
require 'simple_html_dom.php';
require 'vendor/autoload.php';

use League\HTMLToMarkdown\HtmlConverter;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();


$converter = new HtmlConverter(array('strip_tags' => true, 'use_autolinks' => false, 'hard_break' => true));
// Get list of cities names from random category
$cities_category_ids = "https://api.craftypixels.com/categories";
$city_cat_id = json_decode(httpGet($cities_category_ids));
$master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";

// Get relevant seed row from CMS
$seed_url = "https://api.craftypixels.com/seeds/10";
$seed = json_decode(httpGet($seed_url));
$inserted_categories = $seed->categories;
$all_category_ids = [];
// Lets save all category ids in an array
foreach ($city_cat_id[0]->categoriesId as $CityId) {
    array_push($all_category_ids, $CityId);
}

// Generate a random category that is not already traversed
while (in_array(($random_category_id = $all_category_ids[array_rand($all_category_ids)]), $inserted_categories));
$random_category = json_decode(httpGet("https://discussplaces.com/api/category/" . $random_category_id));

// Add newly traversed ID to seed log
array_push($inserted_categories, $random_category_id);
echo "CID : " . $random_category_id . "\n";

$cid = $random_category->cid;
$random_category_name = $random_category->name;
$slug = explode("/", $random_category->slug)[1];
if ($random_category_name) {
    echo $slug . "\n";

    // if category is a city then we'll also add country in search for accurate results.
    $parent_slug = "";
    $parent_name = "";
    if (isset($random_category->parent)) {
        $parent_slug = explode("/", $random_category->parent->slug)[1];
        $parent_name = $random_category->parent->name;
    }

    $search_url = "https://www.tripadvisor.com/Search?ssrc=e&q=best%20cafes%20in%20" . $slug . "%20" . $parent_name . "&searchSessionId=6CFD68BA234093C1604C74B84D0587181640003067207ssid&sid=0E6A36ED2BEA4823BE3FBB06D786E1481640219902178&blockRedirect=true&queryParsed=true";
    echo $search_url . "\n";

    // Clear all files from the data folder
    $files = glob('scraper/data/*'); // get all file names
    foreach ($files as $file) { // iterate files
        if (is_file($file)) {
            unlink($file);
        }
        // delete file
    }

    $files = glob('scraper/data/captera_files/*'); // get all file names
    foreach ($files as $file) { // iterate files
        if (is_file($file)) {
            unlink($file);
        }
        // delete file
    }

    // Call the scraper based on environment and OS
    if ($_ENV['ENV'] == "DEV") {
        // This is to run the scraper with a GUI based scrapper
        $ret = exec("cd scraper && node scraper.js " . escapeshellcmd($search_url) . "  2>&1", $output,  $err);
    } else {
        // This is to run scraper on Linux
        $ret = exec("cd scraper/; xvfb-run -a node scraper.js " . escapeshellcmd($search_url) . " 2>&1", $out, $err);
    }

    echo $ret . "\n";
    if ($ret == "success") {
        $url = "scraper/data/captera.html";
        $html = file_get_html($url);
        if ($html != null) {
            $NameOfResturant = "Best cafes in " . $random_category_name;
            foreach ($html->find('.location-meta-block') as $article) {
                $reviews = $article->find('.review_count', 0)->plaintext;

                // check if the article object has city name in its content
                if (strpos($article->plaintext, $parent_name) !== false) {
                    $number = filter_var($reviews, FILTER_SANITIZE_NUMBER_INT);
                    if ($number >= 20) {
                        echo "---" . $number;
                        $title = $article->find('.result-title')[0];
                        $address = $article->find('.mobile-address-text')[0];
                        $content .= "* ";
                        $content .= "**" . $converter->convert($title) . "** ";
                        $content .= "*(" . $converter->convert($address) . ")*";
                        $content .= "\n";
                    }
                }
            }

            $SearchTerm =  $NameOfResturant;
            if (!empty($content)) {
                $content = "Here are some of the top rated cafes in $random_category_name \n" . $content;
                $content = str_replace("&amp;", "and", $content);
                $content = str_replace("&lt;", "<", $content);
                $content = str_replace("&gt;", ">", $content);
                $content = str_replace("&#x27;", "", $content);
                $content = str_replace("$", "USD", $content);
                $content = str_replace("----------", "", $content);
                // $content = preg_replace('/\[(.*?)\]\s*\((.*?)\)/', '$1:', $content);
                $FilterContent = $content;
                // The following code is creating post and the @createTopic function is in utilities.php
                echo  $NameOfResturant . ":   &nbsp&nbsp&nbsp     " . $FilterContent . "<br>";
                createPostBestRest($cid, $NameOfResturant, $FilterContent, $SearchTerm);
                $seed->categories = $inserted_categories;
                updateCms($seed_url, json_encode($seed));
            }
        } else {
            echo "Page is empty \n";
            // Update CMS row
            $seed->categories = $inserted_categories;
            updateCms($seed_url, json_encode($seed));
            echo "only cms has updated";
        }
    } else {
        echo "No success in downloading content" . "\n";
    }
} else {
    echo "Category is invalid or all categories have been traversed" . "\n";
}
