<?php
require_once('../config.php');

/* CONFIG */

// Generate key:
$privateKey = openssl_pkey_new(array(
	'private_key_bits'	=>	1024,
	'private_key_type'	=>	OPENSSL_KEYTYPE_RSA
));
// Save private part:
openssl_pkey_export_to_file($privateKey, 'private.key');
// Save public part:
$a_key = openssl_pkey_get_details($privateKey);
file_put_contents('public.key', $a_key['key']);
openssl_free_key($privateKey);


/* USE */

// Get public key:
$publicKey = openssl_get_publickey('file://public.key');

$plaintext = 'Hello';
$encrypted = '';
// Encrypt plaintext:
openssl_public_encrypt($plaintext, $encrypted, $publicKey) or die('Failed to encrypt data');
openssl_free_key($publicKey);

print $encrypted;