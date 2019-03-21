<?php

namespace Art\models;

class User extends \Illuminate\Database\Eloquent\Model {
  protected $table = 'user';
  public $timestamps = false;

  public function submissions(){
    return $this->hasMany('Art\models\Submission', 'author_id');
  }

  public function subscribers(){
    return $this->hasMany('Art\models\Subscriber', 'user_id');
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

  public function send($activity, $inbox) {
    // TODO: Remove this
    if(!$user->public_key) {
      $user->generateKeypair()->save();
    }

    $privKey = $this->private_key;
    $httpHost = $_SERVER['HTTP_HOST'];;

    $requestTarget = "post " . parse_url($inbox, PHP_URL_PATH);
    $host = parse_url($inbox, PHP_URL_HOST);
    $date = gmdate('D, d M Y H:i:s T');

    $sign_string = "(request-target): $requestTarget\nhost: $host\ndate:$date";
    openssl_sign($sign_string, $signature, $privKey);
    $signHeader = "keyId=\"https://$httpHost/user/$this->username\",headers=\"(request-target) host date\",signature=\"$signature\"";

    $r = new HttpRequest($inbox, HttpRequest::METH_POST);
    $r->setHeaders([
      "host" => $host,
      "date" => $date,
      "signature" => $signHeader
    ]);
    $r->setBody(json_encode($activity));
    try {
      echo $r->send()->getBody();
    } catch (HttpException $ex) {
      echo $ex;
    }
  }

  public function broadcast($activity) {
    foreach($this->subscribers() as $subscriber) {
      $this->send($activity, $subscriber->inbox);
    }
  }
}
