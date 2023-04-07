<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Scrape Answers.com
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

// ----------- Globals ----------------------------------
require 'utilities.php';
require 'simple_html_dom.php';

// // Get list of movie names from random category
// $categories = (json_decode(httpGet("https://discussplaces.com/api/categories"))->categories);
// $master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";

// $random_category_id = mt_rand(2, count($categories));
// echo "CID : " . $random_category_id . "\n";

// $random_category = $categories[$random_category_id];
// $slug = explode("/", $random_category->slug)[1];
// $cid = $random_category->cid;
// $random_category_name = $random_category->name;



// Get list of movie names from random category
$categories = (json_decode(httpGet("https://discussplaces.com/api/categories"))->categories);
$master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";

// Get relevant seed row from CMS
$seeds_url = "https://api.craftypixels.com/questions";
$seeds = json_decode(httpGet($seeds_url));
// $inserted_categories = $seed->cid;

// Lets save inserted category ids in an array
// foreach ($seeds as $category) {
//     array_push($inserted_categories, $category->cid);
// }

$all_category_ids = [];
// Lets save all category ids in an array
foreach ($categories as $category) {
    array_push($all_category_ids, $category->cid);
}

var_dump( $seeds);

// // Generate a random category that is not already traversed
// while (in_array(($random_category_id = mt_rand(5, count($all_category_ids))), $inserted_categories));
// $random_category = json_decode(httpGet("https://discussplaces.com/api/category/" . $random_category_id));

// // Add newly traversed ID to seed log
// array_push($inserted_categories, $random_category_id);
// echo "CID : " . $random_category_id . "\n";

// $slug = explode("/", $random_category->slug)[1];
// $cid = $random_category->cid;
// $random_category_name = $random_category->name;


// $file = 'questions/' . $slug . '.txt';


// if($slug){
// }

// $page_completed = 1;
// $questions_log_url = "https://api.craftypixels.com/questions?category=" . $slug;
// $questions_log = json_decode(httpGet($questions_log_url));
// if (json_decode(httpGet($questions_log_url))->page_completed) {
//     $page_completed = json_decode(httpGet($questions_log_url))->page_completed;
// } else {
//     for ($x = 1; $x <= 200; $x += 1) {

//         $url = "https://www.answers.com/t/" . $slug . "/best?page=" . $x;

//         echo $url . "\n";
//         $country_html = file_get_html($url);

//         if ($country_html != null) {
//             foreach ($country_html->find('[property=name]') as $element) {
//                 echo $element->children(0)->href . "\n";

//                 $content .= $element->children(0)->href . "\n";
//             }
//             // $first_post_title = "Flag of " . $random_category_name;
//             // $first_post_content = $content;
//             // $search_term = "Flag of " . $random_category_name;

//             file_put_contents($file, $content . PHP_EOL, FILE_APPEND | LOCK_EX);

//             echo "-- The page is: $x \n";
//             $lines = file($file);
//             $lines = array_unique($lines);
//             $lines = str_replace('&#039;', "'", $lines);
//             $lines = str_replace('&#x27;', '"', $lines);

//             file_put_contents($file, implode($lines));

//             if ($questions_log) {
//                 $questions_log->page_completed = $x;
//                 $update_curl = "curl -X PUT '" . $questions_log_url . "' -H 'X-Auth-Token: ybVPd81oPuwIyygrONfhx1QRdXZ7_7xue0ZzqHBJHbq' -H 'X-User-Id: Ab68n9yNiGquXoenb' -H 'Content-type: application/json' -H 'Cookie: __cfduid=d5d2e4a3be95acf0de90dbe407705e6951606399091' --data-raw '" . json_encode($questions_log) . "'";

//             } else {
//                 $questions_log = json_decode(httpGet("https://api.craftypixels.com/questions")[0]);
//                 $questions_log->category = $slug;
//                 $questions_log->page_completed = $x;
//                 $update_curl = "curl -X POST '" . $questions_log_url . "' -H 'Content-type: application/json' -H 'Cookie: connect.sid=s%3A8-V9CS5ycV_UH3DnE4crYPDJWxJwGZdU.Y1jXSv0KZqKo4bmI5EBIyvrUaKFFZ3fQXMRzjSGOyHc; datadome=DNIT3TEaY20Vm24mMQ.KyDzDd.RkYdE.zqgdl.jwLv6dw97VPGLBUJJ5vevrXTk6JKnTu8o.uw2wYzuZI57To6nAe0ats.Pay.oCY3yzRi; __cfduid=d321022b74cc588a68f107c2a9c356e9a1605539864' --data-raw '" . json_encode($questions_log) . "'";

//             }
//             shell_exec($update_curl);

//             sleep(5);

//         } else {

//             $questions_log->is_complete = true;
//             $update_curl = "curl -X PUT '" . $questions_log_url . "' -H 'X-Auth-Token: ybVPd81oPuwIyygrONfhx1QRdXZ7_7xue0ZzqHBJHbq' -H 'X-User-Id: Ab68n9yNiGquXoenb' -H 'Content-type: application/json' -H 'Cookie: __cfduid=d5d2e4a3be95acf0de90dbe407705e6951606399091' --data-raw '" . json_encode($questions_log) . "'";
//             shell_exec($update_curl);
//             break;

//         }

//     }
// }

