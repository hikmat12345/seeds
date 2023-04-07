<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
House keeping API updater
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

// ----------- Globals ----------------------------------
require 'utilities.php';
require 'simple_html_dom.php';

$master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";

$countries = json_decode(httpGet("https://restcountries.eu/rest/v2/all"));
$discussplaces_categories = json_decode(httpGet("https://discussplaces.com/api/categories"))->categories;

$count = 0;
foreach ($countries as $country) {
    foreach ($discussplaces_categories as $category) {

        if ($category->name == $country->name) {

            if ($category->post_count > 4) {

                foreach ($category->posts as $post) {
                    echo $category->name . "\n";
                    $update_curl = "curl -X POST 'https://api.craftypixels.com/questions' -H 'X-Auth-Token: ybVPd81oPuwIyygrONfhx1QRdXZ7_7xue0ZzqHBJHbq' -H 'X-User-Id: Ab68n9yNiGquXoenb' -H 'Content-type: application/json' -H 'Cookie: __cfduid=db86d5b6b51be4ac6fd32f25a6c4628cb1613791974' --data-raw '{\"category_name\":\"" . $category->name . "\",\"cid\":\"" . $category->cid . "\",\"category_slug\":\"" . $category->slug . "\"}'";
                    // shell_exec($update_curl);
                }
            }

        }

    }
}
