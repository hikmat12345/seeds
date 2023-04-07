<?php
$stop_words = array("a", "about", "above", "after", "again", "against", "all", "am", "an", "and", "any", "are", "aren't", "as", "at", "be", "because", "been", "before", "being", "below", "between", "both", "but", "by", "can't", "cannot", "could", "couldn't", "did", "didn't", "do", "does", "doesn't", "doing", "don't", "down", "during", "each", "few", "for", "from", "further", "had", "hadn't", "has", "hasn't", "have", "haven't", "having", "he", "he'd", "he'll", "he's", "her", "here", "here's", "hers", "herself", "him", "himself", "his", "how", "how's", "i", "i'd", "i'll", "i'm", "i've", "if", "in", "into", "is", "isn't", "it", "it's", "its", "itself", "let's", "me", "more", "most", "mustn't", "my", "myself", "no", "nor", "not", "of", "off", "on", "once", "only", "or", "other", "ought", "our", "ours", "ourselves", "out", "over", "own", "same", "shan't", "she", "she'd", "she'll", "she's", "should", "shouldn't", "so", "some", "such", "than", "that", "that's", "the", "their", "theirs", "them", "themselves", "then", "there", "there's", "these", "they", "they'd", "they'll", "they're", "they've", "this", "those", "through", "to", "too", "under", "until", "up", "very", "was", "wasn't", "we", "we'd", "we'll", "we're", "we've", "were", "weren't", "what", "what's", "when", "when's", "where", "where's", "which", "while", "who", "who's", "whom", "why", "why's", "with", "won't", "would", "wouldn't", "you", "you'd", "you'll", "you're", "you've", "your", "yours", "yourself", "yourselves");

$random_uid = rand(2, 121);

function processWord($word, $already_inserted_words_file)
{
    if (!empty($word)) {
        $word = preg_replace("/\r|\n/", "", $word);
        $word = ucfirst(strtolower($word));
        echo "Passed word : " . $word . "\n";
        $search_data = httpGet("http://api.urbandictionary.com/v0/define?term=" . urlencode($word));
        $search_obj = (json_decode($search_data));
        if ($search_obj->list) {
            foreach ($search_obj->list as $list) {
                echo $list->word . "\n";
                echo "calling function for " . $list->word . "\n";
                if (!in_array(strtolower($list->word), $GLOBALS['stop_words'])) {
                    echo "Passed " . $list->word . "\m";
                    if (createTopicOrPost(ucfirst(strtolower($list->word)), $list->definition)) {
                        break;
                    }
                }
            }
        } else {
            echo "No def found on urban dictionary \n";
        }
        return $word;
    } 
}

function createTopicOrPost($movie_category_name, $cid, $word, $definition, $first_post_title, $first_post_content, $search_term)
{
    $tokens = array('380588a6-84f5-45fb-b25a-00fd57d762c2');

    $search_term = str_replace('?', '', $search_term);
    $search_term = trim($search_term);

    echo "Creating topic or post \n";

    // Random shuffle tokens
    shuffle($tokens);

    $search_data = httpGet("https://discussplaces.com/api/search?term=" . urlencode($search_term) . "&in=titlesposts");
    $search_obj = (json_decode($search_data));

    $found_topic = false;
    $found_definition = false;

    $definition = str_replace('"', "", $definition);
    $definition = str_replace("'", "", $definition);
    //   echo escapeshellarg($definition) . "\n";

    if (!in_array(strtolower($word), $GLOBALS['stop_words'])) {
        if (strlen($definition) >= 18) {
            if ($search_obj->matchCount) {
                echo $movie_category_name . " has matching data on site search \n";
                // If word exactly matches one of the topic 
                $found_topic = true; 
                foreach ($search_obj->posts as $posts) {
                    // Finding all existing definitions
                    $definitions = array();
                    foreach ((json_decode(httpGet("https://discussplaces.com/api/topic/" . $posts->topic->tid))->posts) as $post) {
                        $definitions[] = trim(strip_tags($post->content));
                    } 
                    foreach ($definitions as $def) {  
                        if (soundex($def) == soundex($definition)) {
                            $found_definition = true;
                        }
                    }
                    // If one of the definition matches
                    if (!$found_definition) {
                        // add a post to the topic
                        echo "Adding a post to topic ID " . $posts->topic->tid . " as this definition is not there \n";
                        echo shell_exec('curl -H "Authorization: Bearer ' . $tokens[0] . '" --data "content=' . $definition . '&_uid=' . $GLOBALS['random_uid'] . '&cid=' . $cid . '" https://discussplaces.com/api/v2/topics/' . $posts->topic->tid . ' 2>&1');
                    } else {
                        echo "Doing nothing as " . $word . " has same definition on website \n";
                    }
                }
                // if (!$found_topic) {
                //     // Add new topic as the word doesn't exist.
                //     //  shell_exec('curl -H "Authorization: Bearer ' . $tokens[0] . '" --data "title=' . $word . '&content=' . $definition . '&cid=6" https://discussplaces.com/api/v1/topics 2>&1');
                //     echo "Adding a new topic as " . $movie_category_name . " is not on the website \n" . "\n\n";
                //     return true;

                // }
            } else {
                // Add new topic as the word doesn't exist.
                $title = $first_post_title;
                $content = $first_post_content;
                $curl_call = 'curl -H "Authorization: Bearer ' . $tokens[0] . '" --data "title=' . $title . '&content=' . $content . '&_uid=' . $GLOBALS['random_uid'] . '&cid=' . $cid . '" https://discussplaces.com/api/v2/topics 2>&1';
                echo $curl_call;
                shell_exec($curl_call);

                echo "Match Count None, Adding a new topic as " . $title . " is not on the website \n" . "\n\n";
                // echo $curl_call . "\n\n";

            }
        } else {
            echo "Content too short \n";
        }
    } else {
        echo "Stop word found \n";
    }
}

// create topic
function createTopic($movie_category_name, $cid, $word, $definition, $first_post_title, $first_post_content, $search_term, $tags = "")
{
    $tokens = array('380588a6-84f5-45fb-b25a-00fd57d762c2');

    $search_term = str_replace('?', '', $search_term);
    $search_term = trim($search_term);

    echo "Creating topic or post \n";

    // Random shuffle tokens
    shuffle($tokens);

    $search_data = httpGet("https://discussplaces.com/api/search?term=" . urlencode($search_term) . "&in=titles");
    $search_obj = (json_decode($search_data));

    $found_topic = false;
    $found_definition = false;

    $definition = str_replace('"', "", $definition);
    $definition = str_replace("'", "", $definition);
    //   echo escapeshellarg($definition) . "\n";

    if (!in_array(strtolower($word), $GLOBALS['stop_words'])) {
        if (strlen($definition) >= 18) {
            if ($search_obj->matchCount) {

                echo "Can't create a topic as there was a match found" . "\n";

                // }
            } else {
                // Add new topic as the word doesn't exist.
                $title = $first_post_title;
                $content = $first_post_content;

                $curl_call = 'curl -H "Authorization: Bearer ' . $tokens[0] . ' " --data "title=' . $title . '&content=' . $content . '&_uid=' . $GLOBALS['random_uid'] . '&cid=' . $cid  . $tags . '" https://discussplaces.com/api/v2/topics 2>&1';
                // echo $curl_call;
                return shell_exec($curl_call);

                echo "Adding a new topic as " . $title . " is not on the website \n" . "\n\n";
                // echo $curl_call . "\n\n";

            }
        } else {
            echo "Content too short \n";
        }
    } else {
        echo "Stop word found \n";
    }
}

// update cities id in strapi cms
function updateCms($routeUrl, $payload)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $routeUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => "$payload",
        CURLOPT_HTTPHEADER => array(
            'X-Auth-Token: ',
            'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    // print_r($response);
}
// create post
function createPost($cid, $word, $definition, $search_term)
{
    $tokens = array('380588a6-84f5-45fb-b25a-00fd57d762c2');

    $search_term = str_replace('?', '', $search_term);
    $search_term = trim($search_term);

    // Random shuffle tokens
    shuffle($tokens);

    $search_data = httpGet("https://discussplaces.com/api/search?term=" . urlencode($search_term) . "&in=titlesposts");
    $search_obj = (json_decode($search_data));
    $found_topic = false;
    $found_definition = false;

    $definition = str_replace('"', "", $definition);
    $definition = str_replace("'", "", $definition);
    //   echo escapeshellarg($definition) . "\n";

    if (!in_array(strtolower($word), $GLOBALS['stop_words'])) {
        if (strlen($definition) >= 18) {
            if ($search_obj->matchCount) {
                // If word exactly matches one of the topic

                $found_topic = true;

                foreach ($search_obj->posts as $posts) {
                    // Finding all existing definitions
                    $definitions = array();
                    foreach ((json_decode(httpGet("https://discussplaces.com/api/topic/" . $posts->topic->tid))->posts) as $post) {
                        $definitions[] = trim(strip_tags($post->content));
                    }

                    foreach ($definitions as $def) {

                        if (soundex($def) == soundex($definition)) {
                            $found_definition = true;
                        }
                    }
                    // If one of the definition matches
                    if (!$found_definition) {
                        // add a post to the topic
                        shell_exec('curl -H "Authorization: Bearer ' . $tokens[0] . '" --data "content=' . $definition . '&_uid=' . $GLOBALS['random_uid'] . '&cid=' . $cid . '" https://discussplaces.com/api/v1/topics/' . $posts->topic->tid . ' 2>&1');
                        echo "Adding a post to topic ID " . $posts->topic->tid . " as this definition is not there \n";
                        return true;
                    } else {
                        echo "Doing nothing as " . $word . " has same definition on website \n";
                        return false;
                    }
                }
                // if (!$found_topic) {
                //     // Add new topic as the word doesn't exist.
                //     //  shell_exec('curl -H "Authorization: Bearer ' . $tokens[0] . '" --data "title=' . $word . '&content=' . $definition . '&cid=6" https://discussplaces.com/api/v1/topics 2>&1');
                //     echo "Adding a new topic as " . $movie_category_name . " is not on the website \n" . "\n\n";
                //     return true;

                // }
            } else {

                echo "Match Count None, Adding a new topic as " . $title . " is not on the website \n" . "\n\n";
                // echo $curl_call . "\n\n";
                return true;
            }
        }
    }
}

// create post only for Best restaurants
function createPostBestRest($cid, $word, $definition, $search_term)
{
    $tokens = array('380588a6-84f5-45fb-b25a-00fd57d762c2');
    $search_term = str_replace('?', '', $search_term);
    $search_term = trim($search_term);
    // Random shuffle tokens
    shuffle($tokens);
    $search_data = httpGet("https://discussplaces.com/api/search?term=" . urlencode($search_term) . "&in=titlesposts");
    $search_obj = (json_decode($search_data));
    $definition = str_replace('"', "", $definition);
    $definition = str_replace("'", "", $definition);
    if (!in_array(strtolower($word), $GLOBALS['stop_words'])) {
        if (strlen($definition) >= 1) {
            if (!$search_obj->matchCount) {
                // If word exactly matches one of the topic
                echo "create post";
                shell_exec('curl -H "Authorization: Bearer ' . $tokens[0] . '" --data "title=' . $word . '&content=' . $definition . '&_uid=' . $GLOBALS['random_uid'] . '&cid=' . $cid . '" https://discussplaces.com/api/v1/topics 2>&1');
                return true;
            } else {
                echo "Match Count None, Adding a new topic as " . $word . " is not on the website \n" . "\n\n";
                return true;
            }
        } else {
            echo "word length too small";
        }
    } else {
        echo "stop word";
    }
}

function createCategory($name, $uid, $ParentCat_id)
{
    $token = '380588a6-84f5-45fb-b25a-00fd57d762c2';
    $curl_call = 'curl -H "Authorization: Bearer ' . $token . '" --data "name=' . $name . '&_uid=1&parentCid=' . $ParentCat_id . '" https://discussplaces.com/api/v3/categories 2>&1';
    shell_exec($curl_call);
}

function updateCategory($cid, $name, $description, $backgroundImage, $token)
{
    $tokens = array('380588a6-84f5-45fb-b25a-00fd57d762c2');
    $curl_call = "curl --location --request PUT 'https://discussplaces.com/api/v2/categories/" . $cid . "' --header 'Authorization: Bearer " . $tokens[0] . "' --header 'Content-Type: application/x-www-form-urlencoded' --header 'Cookie: __cfduid=d1da1b9f2b7aef2d14836bef28bef6d381564651542; express.sid=s%3A1nTB-SBZgFcPNpn1pS_Q-x5WMoarrXrb.hbLD8g9BjHGQJN57aIl3OjDkty2sqg3fxRSP168PZWM' --data-urlencode 'description=" . $description . "' --data-urlencode '_uid=1'";
    shell_exec($curl_call);
}

function randomUpvote()
{
    $tokens = array('380588a6-84f5-45fb-b25a-00fd57d762c2');

    $topics = (json_decode(httpGet("https://discussplaces.com/api/recent"))->topics);
    $pids = [];
    foreach ($topics as $topic) {
        $slug = explode("/", $category->slug)[1];
        if ($topic->teaserPid) {
            array_push($pids, $topic->teaserPid);
        } else {
            array_push($pids, $topic->mainPid);
        }
    }

    $pid = $pids[mt_rand(0, count($pids) - 1)];

    echo shell_exec('curl -H "Authorization: Bearer ' . $tokens[0] . '" --data "delta=1&_uid=' . $GLOBALS['random_uid'] . '" https://discussplaces.com/api/v2/posts/' . $pid . '/vote 2>&1');
}

function httpGet($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
