<?php

$data = $_POST['message'];
$signature = base64_decode($_POST['signature']);
$publicKey = $_POST['public_key'];

$pubKeyFile = tempnam(sys_get_temp_dir(), 'pubkey');

file_put_contents($pubKeyFile, $publicKey);

$pubkeyid = openssl_pkey_get_public('file://'.$pubKeyFile);
$ok = openssl_verify($data, $signature, $pubkeyid);

unlink($pubKeyFile);

if ($ok == 1) {
    echo "good";
} elseif ($ok == 0) {
    echo "bad";
} else {
    echo "error";
}
