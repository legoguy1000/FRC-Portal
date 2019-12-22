<?php

use FrcPortal\Utilities\IniConfig;

namespace FrcPortal\Traits;

/**
 * Class HasAccountTrait
 *
 * @package App
 */
trait Encryption {

  public function encrypt($decrypted) {
  	$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
  	$key = hex2bin(IniConfig::iniDataProperty('encryption_key'));
  	$ciphertext = sodium_crypto_secretbox($decrypted, $nonce, $key);
  	$encrypted = base64_encode($nonce.$ciphertext);
  	return $encrypted;
  }

  public function decrypt($encrypted) {
  	$decoded = base64_decode($encrypted);
  	$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
  	$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
  	$key = hex2bin(IniConfig::iniDataProperty('encryption_key'));
  	$decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
  	return $decrypted;
  }
}

?>
