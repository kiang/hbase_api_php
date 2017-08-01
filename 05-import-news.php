<?php

$lineCount = 0;
$rows = array();

$headers = array(
    'Accept: application/json',
    'Content-Type: application/json',
);

$mainTitle = base64_encode('main:title');
$mainBody = base64_encode('main:body');
$mainMeta = base64_encode('main:meta');
foreach (glob('/media/disk/news/cache/news/*/*/*.gz') AS $gzFile) {
    error_log("processing {$gzFile}");
    $fh = gzopen($gzFile, 'r');
    while (!gzeof($fh)) {
        $uuid = uuid_create(1);
        $meta = gzgets($fh, 4096);
        $title = gzgets($fh, 4096);
        $body = gzgets($fh, 80000);
        $rows[] = array(
            'key' => base64_encode($uuid),
            'Cell' => array(
                array(
                    'column' => $mainMeta,
                    '$' => base64_encode($meta),
                ),
                array(
                    'column' => $mainTitle,
                    '$' => base64_encode($title),
                ),
                array(
                    'column' => $mainBody,
                    '$' => base64_encode($body),
                ),
            ),
        );
        if (++$lineCount % 200 === 0) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "http://hcdnc611:20550/news/row1/fakekey");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_GSSNEGOTIATE);
            curl_setopt($ch, CURLOPT_USERPWD, ":");

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
                'Row' => $rows
            )));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $page = curl_exec($ch);
            curl_close($ch);
            error_log("pushed {$lineCount}");
            $rows = array();
        }
    }
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://hcdnc611:20550/news/row1/fakekey");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_GSSNEGOTIATE);
    curl_setopt($ch, CURLOPT_USERPWD, ":");

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
        'Row' => $rows
    )));

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $page = curl_exec($ch);
    curl_close($ch);
    error_log("pushed {$lineCount}");
    $rows = array();
}
