<?php

namespace Art\models;

function verify_sig($signature, $r_headers, $request_target){
    error_log('');
    $signature_header = [];

    foreach(explode(',', $signature) as $pair) {
      list($key, $value) = explode('=', $pair);
      $signature_header[trim($key, '"')] = trim($value, '"');
    }

    error_log(print_r($signature_header, true));

    $key_id = $signature_header['keyId'];
    $headers = $signature_header['headers'];
    $signature = base64_decode($signature_header['signature']);

    $get_opts = array(
      'http'=>array(
        'method'=>"GET",
        'header'=>"Accept: application/json\r\n"
      )
    );
    $actor = json_decode(file_get_contents($key_id, false, stream_context_create($get_opts)), true);
    $key = $actor['publicKey']['publicKeyPem'];
    $comparison_string = implode("\n", array_map(
      function($signature_header_name) use($r_headers, $request_target) {
        if($signature_header_name === '(request-target)') {
          return "(request-target): post $request_target";
        } else {
          return $signature_header_name . ": " . $r_headers[$signature_header_name];
        }
      },
      explode(' ', $headers)
    ));

    error_log($comparison_string);
    error_log($signature);
    error_log($key);

    return openssl_verify($comparison_string, $signature, $key, OPENSSL_ALGO_SHA256);
}


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
    if(!$this->public_key) {
      $this->generateKeypair()->save();
    }

    $privKey = $this->private_key;
    $httpHost = $_SERVER['HTTP_HOST'];;

    $requestTarget = "post " . parse_url($inbox, PHP_URL_PATH);
    $host = parse_url($inbox, PHP_URL_HOST);
    $date = gmdate('D, d M Y H:i:s T');

    $sign_string = "(request-target): $requestTarget\nhost: $host\ndate: $date";
    openssl_sign($sign_string, $signature, $privKey, OPENSSL_ALGO_SHA256);
    error_log('SIGNED:');
    error_log($signature);
    error_log(openssl_verify($sign_string, $signature, $this->public_key, OPENSSL_ALGO_SHA256));
    $signature = base64_encode($signature);
    $signHeader = "keyId=\"https://$httpHost/user/$this->username\",headers=\"(request-target) host date\",signature=\"$signature\"";

    $headers = [
      "host" => "$host",
      "date" => "$date",
      "signature" => "$signHeader"
    ];
    error_log(print_r($headers,true));
    $body = json_encode($activity);

    $client = new \GuzzleHttp\Client();
    error_log(verify_sig($signHeader, $headers, parse_url($inbox, PHP_URL_PATH)));

    $res = $client->request('POST', $inbox, ['body' => $body, 'headers' => $headers, 'debug'=>true]);
    error_log($res->getBody());
    error_log($res->getStatusCode());
  }

  public function broadcast($activity) {
    error_log('BROADCAST');
    error_log(print_r($activity, true));
    foreach($this->subscribers as $subscriber) {
      error_log($subscriber->inbox);
      $this->send($activity, $subscriber->inbox);
    }
  }
}
