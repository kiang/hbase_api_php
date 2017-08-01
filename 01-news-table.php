<?php

//system('kinit yipolc00@MOEA.NCHC -k -t ' . __DIR__ . '/yipolc00.keytab');

$ch = curl_init();

$headers = array(
    'Accept: application/json',
    'Content-Type: application/json',
);

curl_setopt($ch, CURLOPT_URL, "http://hcdnc611:20550/news/schema");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_GSSNEGOTIATE);
curl_setopt($ch, CURLOPT_USERPWD, ":");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{"name":"news","ColumnSchema":[{"name":"main"}]}');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$page = curl_exec($ch);
curl_close($ch);

echo $page;

print_r(json_decode($page, true));
