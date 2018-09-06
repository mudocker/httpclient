<?php
$urls = array(
    "http://qq.com/",
    "http://www.hao123.com/?1536217914",
);

$mh = curl_multi_init();

foreach ($urls as $i => $url) {
    $conn[$i] = curl_init($url);
    curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
    curl_multi_add_handle($mh, $conn[$i]);
}

do {
    $status = curl_multi_exec($mh, $active);
    $info = curl_multi_info_read($mh);
    if (false !== $info) var_dump($info);

} while ($status === CURLM_CALL_MULTI_PERFORM || $active);
$stop=1;
foreach ($urls as $i => $url) $res[$i] = curl_multi_getcontent($conn[$i]);



//curl_close($conn[$i]);
//$data=curl_multi_info_read($mh);

foreach ($urls as $i => $url) $res[$i] = curl_multi_getcontent($conn[$i]);


curl_multi_close($mh);
