<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Scrape Highlights of a country https://www.lonelyplanet.com/france/narratives/planning and britanica
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

// ----------- Globals ----------------------------------
require 'utilities.php';
require 'simple_html_dom.php';
require 'vendor/autoload.php';

use League\HTMLToMarkdown\HtmlConverter;

$converter = new HtmlConverter(array('strip_tags' => true, 'use_autolinks' => false, 'hard_break' => true));

// Get list of movie names from random category
$categories = (json_decode(httpGet("https://discussplaces.com/api/categories"))->categories);
$master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";

// Get relevant seed row from CMS
$seed_url = "https://api.craftypixels.com/seeds/4";
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
$url = "https://www.lonelyplanet.com/" . $slug . "/narratives/planning";

echo $url . "\n";

$country_html = file_get_html($url);

$britanica_html = file_get_html("https://www.britannica.com/place/" . $slug);

if ($country_html != null) {
    echo "Looking into " . $url . "\n";
    $content = "";

    foreach ($britanica_html->find('#ref1') as $element) {
        $content .= "# " . $random_category_name . "\n\n";
        $intro = explode('.', $element->find('p')[0]->plaintext);

        // Present as bullets
        foreach ($intro as $item) {
            if (strlen($item) > 2) {
                $content .= "* " . $item . ".\n";
            }
        }

        $content .= "\n\n";
    }

    // Get some meta from api
    foreach ($country_html->find('#highlights') as $element) {
        $content .= $converter->convert($element);

        foreach ($element->children() as $section) {

            if ((strlen($section->plaintext) > 5) && (trim($section->plaintext) != "Highlights")) {

                //         $content .=  $section . "\n\n";

                // $content .= $converter->convert($section);

            } 
        }
    }

    $content = str_replace("&amp;", "and", $content);
    $content = str_replace("&#x27;", "", $content);
    $content = str_replace("----------", "", $content);

    $content = str_replace("Highlights", "## " . $random_category_name . " has so much to offer, here are some of the things " . $random_category_name . " is known for.", $content);
    $content = preg_replace('/\[(.*?)\]\s*\((.*?)\)/', '$1:', $content);

    $first_post_title = "What is " . $random_category_name . " known for?";
    $first_post_content = $content;
    $search_term = $first_post_title;

    echo $content;

    if (strlen($content) > 5) {
        if (createTopic(ucfirst(strtolower($random_category_name)), $cid, $first_post_title, $first_post_content, $first_post_title, $first_post_content, $search_term)) {
            echo "Saving information " . $first_post_title . "\n";
            //       echo $first_post_content . "\n";

            $count++;
        }
    } else {
        echo "ending_paragraph->plaintext " . "\n";
    }

    // Update CMS row
    $seed->categories = $inserted_categories;
    $update_curl = "curl -X PUT '" . $seed_url . "' -H 'X-Auth-Token: ybVPd81oPuwIyygrONfhx1QRdXZ7_7xue0ZzqHBJHbq' -H 'X-User-Id: Ab68n9yNiGquXoenb' -H 'Content-type: application/json' -H 'Cookie: __cfduid=d5d2e4a3be95acf0de90dbe407705e6951606399091' --data-raw '" . json_encode($seed) . "'";
    shell_exec($update_curl);

}
