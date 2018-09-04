<?php
function createHeaderCallback($data) {
    return function ($ch, $header) use ($data) {
        preg_match('/^Set-Cookie:\s*([^=]+)=([^;]+)/mi', $header, $cookie) === 1 and $data->responseCookies[$cookie[1]] = trim($cookie[2], " \n\r\t\0\x0B");
        $data->rawResponseHeaders .= $header;
        return strlen($header);
    };
}
