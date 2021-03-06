<?php

namespace Art\models;

class User extends \Illuminate\Database\Eloquent\Model {
  protected $table = 'user';
  public $timestamps = false;

  public function submissions(){
    return $this->hasMany('Art\models\Submission', 'author_id');
  }

  public function journals(){
    return $this->hasMany('Art\models\Journal', 'author_id');
  }

  public function subscribers(){
    // Has many through?
    return $this->hasMany('Art\models\Subscriber', 'user_id');
  }

  public function following(){
    return $this->hasMany('Art\models\Following', 'user_id');
  }

  public function inbox(){
    return $this->hasMany('Art\models\Inbox', 'user_id');
  }

  protected function performInsert($q){
    $this->generateKeypair();
    return parent::performInsert($q);
  }

  protected function generateKeypair(){
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

  function setPassword($password) {
    $this->password = password_hash($password, PASSWORD_DEFAULT);
  }

  function checkPassword($password) {
    return password_verify($password, $this->password);
  }

  function getUrl() {
    $httpHost = $_SERVER['HTTP_HOST'];
    return "https://$httpHost/user/$this->username";
  }

  function getWebfinger() {
    $httpHost = $_SERVER['HTTP_HOST'];
    return "$this->username@$httpHost";
  }

  function activity($type, $object) {
    $httpHost = $_SERVER['HTTP_HOST'];
    $guid = time();

    return [
      'id' => "https://$httpHost/$guid",
      'type'=> $type,
      'actor'=> $this->getUrl(),
      'object'=> $object
    ];
  }

  public function send($activity, $inbox, $actor = false) {
    $activity['@context'] = 'https://www.w3.org/ns/activitystreams';
    if($actor) {
      $activity['to'] = 'https://www.w3.org/ns/activitystreams#Public';
      $activity['cc'] = $actor;
    }

    $privKey = $this->private_key;

    $requestTarget = "post " . parse_url($inbox, PHP_URL_PATH);
    $host = parse_url($inbox, PHP_URL_HOST);
    $date = gmdate('D, d M Y H:i:s T');

    $sign_string = "(request-target): $requestTarget\nhost: $host\ndate: $date";
    openssl_sign($sign_string, $signature, $privKey, OPENSSL_ALGO_SHA256);
    $signature = base64_encode($signature);
    $userUrl = $this->getUrl();
    $signHeader = "keyId=\"$userUrl\",headers=\"(request-target) host date\",signature=\"$signature\"";

    $headers = [
      "host" => "$host",
      "date" => "$date",
      "signature" => "$signHeader",
      "content-type" => "application/activity+json"
      #"content-type" => 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"'
    ];
    $body = json_encode($activity);

    $client = new \GuzzleHttp\Client();

    // error_log($body);

    try {
      $res = $client->request('POST', $inbox, ['body' => $body, 'headers' => $headers, 'debug'=>true]);
      error_log($res->getBody());
      error_log($res->getStatusCode());
    } catch (\GuzzleHttp\Exception\ConnectException $e) {
      // TODO: Send this later, or remove from followers list
    }
  }

  public function broadcast($activity) {
    foreach($this->subscribers as $subscriber) {
      $this->send($activity, $subscriber->actor->inbox, $subscriber->actor->url);
    }
  }

  public function addNote($message, $attachment, $url, $actor_id){
    $purifier = new \HTMLPurifier();
    $create = new \Art\models\Inbox();
    $create->message = $purifier->purify(substr($message, 0, 1000)) . $attachment;
    $create->url = $url;
    $create->remote_actor_id = $actor_id;
    $create->user_id = $this->id;
    $create->save();
    return $create;
  }
}
