<?php
require dirname(__DIR__) . '/../vendor/autoload.php';
use mdocker\lib\curl\Curl;
$data = array();
isset($_GET['after']) and  $data['after'] = $_GET['after'];
$curl = new Curl();
$curl->get('https://www.reddit.com/r/pics/top/.json', $data);

echo '<ul>';

foreach ($curl->response->data->children as $result) {
    $pic = $result->data;
    echo '<li>' . '<a href="' . $pic->url . '" target="_blank">' . $pic->title . '<br />' . '<img alt="" src="' . $pic->thumbnail . '" />' . '</a> ' .
           $pic->score . ' pts ' . $pic->num_comments . ' comments by ' . $pic->author . '</li>';
}

echo '</ul>';
echo '<a href="?after=' . $curl->response->data->after . '">Next</a>';
