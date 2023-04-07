
<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Add city detail.
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

require 'utilities.php';
require 'simple_html_dom.php';
require 'vendor/autoload.php';

use League\HTMLToMarkdown\HtmlConverter;

$converter = new HtmlConverter(array('strip_tags' => true, 'use_autolinks' => false, 'hard_break' => true));
$master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";
for ($n=0;$n<100;$n++){ 
// get all cities ids in which would insert topic.
$cities_category_ids = "https://api.craftypixels.com/categories";
$city_cat_id = json_decode(httpGet($cities_category_ids));
$all_cities_cat_ids = array();
foreach ($city_cat_id[0]->categoriesId as $citiesId) {
    array_push($all_cities_cat_ids, $citiesId);
}
// get all ids which is already traversed
$seed_url = "https://api.craftypixels.com/seeds/9";
$seed = json_decode(httpGet($seed_url));
$inserted_categories = $seed->categories;

// remaing cities 
// get all ids which is already traversed
$rem_seed_url = "https://api.craftypixels.com/seeds/12";
$remainingseed = json_decode(httpGet($rem_seed_url));
$rem_inserted_categories = $remainingseed->categories;

// Generate a random category that is not already traversed

while (in_array(($random_category_id = $all_cities_cat_ids[array_rand($all_cities_cat_ids)]), $inserted_categories));
    $random_category = json_decode(httpGet("https://discussplaces.com/api/category/" . $random_category_id));
    echo "Looking into " . $random_category_id . "  " . $random_category->slug  . "\n";
    // Add newly traversed ID to seed log
    array_push($inserted_categories, $random_category_id);
    array_push($rem_inserted_categories, $random_category_id);
    // The following code is about assinging and filtering ids slug etc.
    $cid = $random_category->cid;
    $random_category_name = $random_category->name;
    $slug = explode("/", $random_category->slug)[1];


    // if category is a city then we'll also add country in search for accurate results.
    $parent_slug = "";
    if (isset($random_category->parent)) {
        $parent_slug = explode("/", $random_category->parent->slug)[1];
    }
    $wikiapi =   json_decode(httpGet("https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exintro&explaintext&redirects=1&titles=" . $random_category_name));
   
    $citydetails = isset($wikiapi->query)?$wikiapi->query->pages:"";
    $content = "";
    $i = 0;
    //    find limted lines of the city detail
    if(!empty($citydetails)){
    foreach ($citydetails as $citydetail) {
        if (isset($citydetail->extract) && !empty($citydetail->extract)) {
            if (strpos($citydetail->extract, $random_category->parent->name)) {
                $splitcontent = explode(".", $citydetail->extract);
                foreach ($splitcontent as $eachdetail) {
                    $content .= $eachdetail;
                    if ($i == 2) {
                        break;
                    }
                    $i++; 
                }
            } else {
                echo "<br> content object not found or empty <br>";
            }
        }
     }
   }
    //  find and insert backgroundImage to image array  m
    $gallery_image = json_decode(httpGet("https://en.wikipedia.org/w/api.php?action=query&titles=" . $random_category_name . "&prop=pageimages&format=json&pithumbsize=340"));
    $image = "";
    if(isset($gallery_image->query->pages)){
        foreach ($gallery_image->query->pages as $thumbnail) {
        if (isset($thumbnail->thumbnail->source) &&  !empty($thumbnail->thumbnail->source)) {
            $image .= $thumbnail->thumbnail->source;
        } else {
            echo "image object not found or empty <br>";
            }
          }
      }
    // echo $content;
    // echo "image :".$image;
    // Convert to markdown
    $content = $converter->convert($content);
    $content = str_replace("&amp;", "and", $content);
    $content = str_replace("&lt;", "<", $content);
    $content = str_replace("&gt;", ">", $content);
    $content = str_replace("&#x27;", "", $content);
    $content = str_replace("$", "USD", $content);
    $content = preg_replace('/\[(.*?)\]\s*\((.*?)\)/', '$1:', $content);

    $description = $content;
    if (!empty($description)) {
        // The following code is creating topic and the @createTopic function is in utilities.php
        updatecityinf($cid, $random_category_name, $description, $image);
        echo "Saving information " . $description . "\n";
        // The following code Update CMS row as Hi insert this city coz this one is inserted.
        $seed->categories = $inserted_categories;
        updateCms($seed_url, json_encode($seed));
      } else {
        echo "Content not found " . "\n";  
        $remainingseed->categories =  $rem_inserted_categories; 
        updateCms($rem_seed_url, json_encode($remainingseed));
    }
}
function updatecityinf($cid, $name, $description, $bgimage)
{
    $imagesrc = !empty($bgimage) ? "&backgroundImage=" . $bgimage : "";
    $tokens = array('380588a6-84f5-45fb-b25a-00fd57d762c2');
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://discussplaces.com/api/v2/categories/" . $cid . "",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => "description=" . $description . $imagesrc . "&_uid=1",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer " . $tokens[0] . "",
            'Content-Type: application/x-www-form-urlencoded',
            'Cookie: express.sid=s%3AGmASJKjkQPlSWu9FF5ERPw-nVypEBFw_.Bnf2K8fFKY53%2BWNiRncDVrdR5osyEOQU9Uad1ter59k'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    echo $response; 
  }

