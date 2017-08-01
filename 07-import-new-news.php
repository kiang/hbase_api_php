<?php

include '/media/disk/news/config.php';
$filePath = '/media/disk/news/cache/news';
$lastYear = 0;
$lastYearUrl = '';

system('/usr/bin/kinit yipolc00@MOEA.NCHC -k -t ' . __DIR__ . '/yipolc00.keytab');

$lineCount = 0;
$rows = array();

$headers = array(
    'Accept: application/json',
    'Content-Type: application/json',
);

$mainTitle = base64_encode('main:title');
$mainBody = base64_encode('main:body');
$mainMeta = base64_encode('main:meta');

for ($i = 2013; $i <= date('Y'); $i ++) {
    $lastYearUrl = $listUrl . $i . '/';
    $list = file_get_contents($lastYearUrl);
    $maxUrlLength = 0;

    $pos = strpos($list, 'href="');
    while (false !== $pos) {
        $pos += 6;
        $posEnd = strpos($list, '"', $pos);
        $url = substr($list, $pos, $posEnd - $pos);
        if (false === strpos($url, '-diff')) {
            $dateParts = array(
                substr($url, 0, 4),
                substr($url, 4, 2),
                substr($url, 6, 2),
            );
            $targetFile = "{$filePath}/" . implode('/', $dateParts) . ".gz";
            if (!file_exists(dirname($targetFile))) {
                mkdir(dirname($targetFile), 0777, true);
            }
            if (!file_exists($targetFile) || filesize($targetFile) === 0) {
                $url = $lastYearUrl . $url;
                echo "getting {$url}\n";
                file_put_contents($targetFile, file_get_contents($url));

                error_log("processing {$targetFile}");
                $fh = gzopen($targetFile, 'r');
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
                if (!empty($rows)) {
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
        }
        $pos = strpos($list, 'href="', $posEnd);
    }
}
