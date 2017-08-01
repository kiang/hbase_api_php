<?php

$headers = array(
    'Accept: application/json',
    'Content-Type: application/json',
);

if (!file_exists(__DIR__ . '/tmp')) {
    mkdir(__DIR__ . '/tmp', 0777);
}

system('/usr/bin/kinit yipolc00@MOEA.NCHC -k -t ' . __DIR__ . '/yipolc00.keytab');

$scannerFile = __DIR__ . '/tmp/scanner_biz';

if (!file_exists($scannerFile)) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://hcdnc611:20550/biz/scanner");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_GSSNEGOTIATE);
    curl_setopt($ch, CURLOPT_USERPWD, ":");

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
        'batch' => 500,
    )));

    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $page = curl_exec($ch);

    file_put_contents($scannerFile, $page);

    curl_close($ch);
} else {
    $s = file_get_contents($scannerFile);
    $parts = explode('Location: http://hcdnc611:20550/biz/scanner/', $s);
    $parts = explode("\n", $parts[1]);
    $parts[0] = trim($parts[0]);

    $pageCount = 0;
    $hasNext = true;
    while ($hasNext) {
        ++$pageCount;
        error_log("fetching page {$pageCount}");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://hcdnc611:20550/biz/scanner/" . $parts[0]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_GSSNEGOTIATE);
        curl_setopt($ch, CURLOPT_USERPWD, ":");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $page = curl_exec($ch);
        $json = json_decode($page);
        if (empty($json->Row)) {
            $hasNext = false;
        } else {
            foreach ($json->Row AS $k => $r) {
                $json->Row[$k]->key = base64_decode($json->Row[$k]->key);
                foreach ($json->Row[$k]->Cell AS $c => $b) {
                    $json->Row[$k]->Cell[$c]->column = base64_decode($json->Row[$k]->Cell[$c]->column);
                    $json->Row[$k]->Cell[$c]->{'$'} = base64_decode($json->Row[$k]->Cell[$c]->{'$'});
                }
            }
            file_put_contents(__DIR__ . '/tmp/biz_' . $pageCount, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            error_log("page {$pageCount} count: " . count($json->Row));
        }
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://hcdnc611:20550/biz/scanner/" . $parts[0]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_GSSNEGOTIATE);
    curl_setopt($ch, CURLOPT_USERPWD, ":");

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $page = curl_exec($ch);

    file_put_contents(__DIR__ . '/tmp/biz_delete', $page);

    curl_close($ch);
}
