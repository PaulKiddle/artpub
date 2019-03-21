<?php

namespace Art\models;

class User extends \Illuminate\Database\Eloquent\Model {
  protected $table = 'user';
  public $timestamps = false;

  public function submissions(){
    return $this->hasMany('Art\models\Submission', 'author_id');
  }

  public function generateKeypair(){
    $config = array(
      "digest_alg" => "sha256",
      "private_key_bits" => 2048,
      "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );

    // Create the private and public key
    $res = openssl_pkey_new($config);

    // Extract the private key from $res to $privKey
    openssl_pkey_export($res, $privKey);

    // Extract the public key from $res to $pubKey
    $pubKey = openssl_pkey_get_details($res);
    $pubKey = $pubKey["key"];

    $this->private_key = $privKey;
    $this->public_key = $pubKey;

    return $this;
  }
}
