<?php

$lineCount = 0;
$rows = array();

$headers = array(
    'Accept: application/json',
    'Content-Type: application/json',
);

$keys = array();
foreach (glob('/media/disk/gcis/ronnywang-twcompany.s3-website-ap-northeast-1.amazonaws.com/files/*.jsonl.gz') AS $gzFile) {
    error_log("processing {$gzFile}");
    $fh = gzopen($gzFile, 'r');
    while (!gzeof($fh)) {
        $uuid = uuid_create(1);
        $biz = json_decode(gzgets($fh, 80000), true);
        $row = array(
            'key' => base64_encode($uuid),
            'Cell' => array(),
        );
        if (!is_array($biz)) {
            continue;
        }
        foreach ($biz AS $k => $v) {
            if (!isset($keys[$k])) {
                $keys[$k] = base64_encode('main:' . $k);
            }
            if (!is_string($v)) {
                $v = json_encode($v);
            }
            $row['Cell'][] = array(
                'column' => $keys[$k],
                '$' => base64_encode($v),
            );
        }
        $rows[] = $row;
        if (++$lineCount % 200 === 0) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "http://hcdnc611:20550/biz/row1/fakekey");
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

    curl_setopt($ch, CURLOPT_URL, "http://hcdnc611:20550/biz/row1/fakekey");
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
