<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Scrape food - What are some popular country dishes?
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

// ----------- Globals ----------------------------------
require 'utilities.php';
require 'simple_html_dom.php';

// Get list of movie names from random category
$categories = (json_decode(httpGet("https://discussplaces.com/api/categories"))->categories);
$master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";

// Get relevant seed row from CMS
$seed_url = "https://api.craftypixels.com/seeds/3";
$seed = json_decode(httpGet($seed_url));
$inserted_categories = $seed->categories;

$all_category_ids = [];
// Lets save all category ids in an array
foreach ($categories as $category) {
    array_push($all_category_ids, $category->cid);
}

// Generate a random category that is not already traversed
while (in_array(($random_category_id = mt_rand(5, count($all_category_ids))), $inserted_categories));
$random_category = json_decode(httpGet("https://discussplaces.com/api/category/" . $random_category_id));

// Add newly traversed ID to seed log
array_push($inserted_categories, $random_category_id);
echo "CID : " . $random_category_id . "\n";

$slug = explode("/", $random_category->slug)[1];
$cid = $random_category->cid;
$random_category_name = $random_category->name;
if ($slug) {

    $url = "https://www.tasteatlas.com/most-popular-food-in-" . $slug;

    echo $url . "\n";

    $search_url = $url;

// Clear data folder
    $files = glob('../scraper/lib/data/*'); // get all file names
    foreach ($files as $file) { // iterate files
        if (is_file($file)) {
            unlink($file);
        }
        // delete file
    }

    $files = glob('../scraper/lib/data/captera_files/*'); // get all file names
    foreach ($files as $file) { // iterate files
        if (is_file($file)) {
            unlink($file);
        }
        // delete file
    }

    $ret = exec("cd ../scraper/lib/; xvfb-run -a node scraper.js " . $search_url . " 2>&1", $out, $err);
//    $ret = exec("cd ../scraper/lib/; node scraper.js " . $search_url . " 2>&1", $out, $err);

    echo $ret . "\n";

    if ($ret == "success") {

        $count++;
        $url = '../scraper/lib/data/captera.html';
        echo "Looking into " . $search_url . "\n";
        $html = file_get_html($url);

        if ($html != null) {

            $content = "";
            $api_data = json_decode(httpGet("https://countries.craftypixels.com/v2/name/" . $random_category_name));
            $content .= "Here are a few popular " . $api_data[0]->demonym . " dishes. Please share your favorites as a reply. \n\n";

            foreach ($html->find('.top-list-article__item') as $element) {

                foreach ($element->find('.h1--bold') as $row) {
                    $content .= "* " . trim($row->plaintext) . " ";
                }

                foreach ($element->find('.h3--light') as $row) {
                    $content .= "(" . trim($row->plaintext) . ") \n";
                }

            }

            $first_post_title = "What are some famous " . $api_data[0]->demonym . " dishes?";
            $first_post_content = $content;
            $search_term = "What are some famous " . $api_data[0]->demonym . " dishes?";

            if (strlen($content) >= 100) {
                if (createTopicOrPost(ucfirst(strtolower($random_category_name)), $cid, $first_post_title, $first_post_content, $first_post_title, $first_post_content, $search_term)) {
                    echo "Saving information " . $first_post_title . "\n";
                    //       echo $first_post_content . "\n";

                    $count++;
                }
            } else {
                echo "ending_paragraph->plaintext " . "\n";
            }

        } else {
            echo "Page is empty \n";

        }

        // Update CMS row
        $seed->categories = $inserted_categories;
        $update_curl = "curl -X PUT '" . $seed_url . "' -H 'X-Auth-Token: ybVPd81oPuwIyygrONfhx1QRdXZ7_7xue0ZzqHBJHbq' -H 'X-User-Id: Ab68n9yNiGquXoenb' -H 'Content-type: application/json' -H 'Cookie: __cfduid=d5d2e4a3be95acf0de90dbe407705e6951606399091' --data-raw '" . json_encode($seed) . "'";
        shell_exec($update_curl);

    } else {
        echo "No Success" . "\n";

    }
} else {
    echo "Category is invalid or all categories have been traversed" . "\n";
}
