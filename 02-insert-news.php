<?php

$ch = curl_init();

$headers = array(
    'Accept: application/json',
    'Content-Type: application/json',
);

curl_setopt($ch, CURLOPT_URL, "http://hcdnc611:20550/news/row1/fakekey");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_GSSNEGOTIATE);
curl_setopt($ch, CURLOPT_USERPWD, ":");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
    'Row' => array(
        array(
            'key' => base64_encode('newskey3'),
            'Cell' => array(
                array(
                    'column' => base64_encode('main:title'),
                    '$' => base64_encode('中文標題1'),
                ),
                array(
                    'column' => base64_encode('main:body'),
                    '$' => base64_encode('中文內容，使用編譯'),
                ),
            ),
        ),
        array(
            'key' => base64_encode('newskey4'),
            'Cell' => array(
                array(
                    'column' => base64_encode('main:title'),
                    '$' => base64_encode('中文標題1'),
                ),
                array(
                    'column' => base64_encode('main:body'),
                    '$' => base64_encode('中文內容，使用編譯'),
                ),
            ),
        ),
        array(
            'key' => base64_encode('newskey5'),
            'Cell' => array(
                array(
                    'column' => base64_encode('main:title'),
                    '$' => base64_encode('中文標題1'),
                ),
                array(
                    'column' => base64_encode('main:body'),
                    '$' => base64_encode('中文內容，使用編譯'),
                ),
            ),
        ),
        array(
            'key' => base64_encode('newskey6'),
            'Cell' => array(
                array(
                    'column' => base64_encode('main:title'),
                    '$' => base64_encode('中文標題1'),
                ),
                array(
                    'column' => base64_encode('main:body'),
                    '$' => base64_encode('中文內容，使用編譯'),
                ),
            ),
        ),
    ),
)));

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$page = curl_exec($ch);
curl_close($ch);

echo $page;

print_r(json_decode($page, true));
