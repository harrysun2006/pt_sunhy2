<?php

$apnsHost = '17.149.34.141';
$apnsPort = 2195;
$apnsCert = dirname(__FILE__).'/../data/apns-product.pem';
$token = '90e35af2 e0b8b9be 33c3293b d604bb00 8c49e92d e57bdc07 bb29b3fb e44053b2';

$payload['aps'] = array(
	'alert' => 'Test Apple Apns',
	'badge' => 1,
	'sound' => 'default'
	);

$streamContext = stream_context_create();
stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
stream_context_set_option($streamContext, 'ssl', 'passphrase', 'kai_kai');

$apns = stream_socket_client('ssl://'.$apnsHost.':'.$apnsPort, $error, $errorString, 5, STREAM_CLIENT_CONNECT, $streamContext);

if ($apns) {

	$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $token)) . chr(0) . chr(strlen(js_encode($payload))) . json_encode($payload);
	
	fwrite($apns, $apnsMessage);
	fclose($apns);
}