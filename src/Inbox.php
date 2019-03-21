<?php
namespace Art;

class Inbox extends Route {
  function view ($req, $res, $args) {
    $user_id = $args['id'];

    $signature_header = [];

    foreach(explode(',', $req->getHeaderLine('signature')) as $pair) {
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
      function($signature_header_name) use($req, $user_id) {
        if($signature_header_name === '(request-target)') {
          return "(request-target): post /user/$user_id/inbox";
        } else {
          return $signature_header_name . ": " . $req->getHeaderLine($signature_header_name);
        }
      },
      explode(' ', $headers)
    ));

    error_log($comparison_string);

    if(!openssl_verify($comparison_string, $signature, $key, OPENSSL_ALGO_SHA256)) {
      return $res->withStatus(401);
    }

    $user = \Art\models\User::where('username', $user_id)->first();

    if(!$user->public_key) {
      $user->generateKeypair()->save();
    }

    $json = $req->getBody();
    $data = json_decode($json, true);

    error_log($json);

    $type = strtolower($data['type']);

    switch($type){
      case 'follow':
        \Art\models\Subscriber::create([
          'url' => $actor['id'],
          'inbox' => $actor['inbox'],
          'user_id' => $user->id
        ]);

        $privKey = $user->private_key;

        $requestTarget = "post " . parse_url($actor['inbox'], PHP_URL_PATH);
        $host = parse_url($url, PHP_URL_HOST);
        $date = gmdate('D, d M Y H:i:s T');

        $sign_string = "(request-target): $requestTarget\nhost: $host\ndate:$date";
        openssl_sign($sign_string, $signature, $privKey);
        $signHeader = "keyId=\"https://$this->host/user/$user->username\",headers=\"(request-target) host date\",signature=\"$signature\"";

        $r = new HttpRequest($actor['inbox'], HttpRequest::METH_POST);
        $r->setHeaders([
          "host" => $host,
          "date" => $date,
          "signature" => $signHeader
        ]);
        $r->setBody(json_encode([
          "@context" => "https://www.w3.org/ns/activitystreams",
          "id" => "https://$this->host/$date",
          "type" => "Accept",
          "object" => $data['id']
        ]));
        try {
          echo $r->send()->getBody();
        } catch (HttpException $ex) {
          echo $ex;
        }
    }

    return $res->withStatus(501);
  }
}
/*
Array
(
      [@context] => Array
        (
              [0] => https://www.w3.org/ns/activitystreams
            [1] => https://w3id.org/security/v1
            [2] => Array
                (
                      [manuallyApprovesFollowers] => as:manuallyApprovesFollowers
                    [sensitive] => as:sensitive
                    [movedTo] => Array
                        (
                              [@id] => as:movedTo
                            [@type] => @id
                        )

                    [alsoKnownAs] => Array
                        (
                              [@id] => as:alsoKnownAs
                            [@type] => @id
                        )

                    [Hashtag] => as:Hashtag
                    [ostatus] => http://ostatus.org#
                    [atomUri] => ostatus:atomUri
                    [inReplyToAtomUri] => ostatus:inReplyToAtomUri
                    [conversation] => ostatus:conversation
                    [toot] => http://joinmastodon.org/ns#
                    [Emoji] => toot:Emoji
                    [focalPoint] => Array
                        (
                              [@container] => @list
                            [@id] => toot:focalPoint
                        )

                    [featured] => Array
                        (
                              [@id] => toot:featured
                            [@type] => @id
                        )

                    [schema] => http://schema.org#
                    [PropertyValue] => schema:PropertyValue
                    [value] => schema:value
                )

        )

    [id] => https://example.com/db2a2e6a-ae83-4737-a02c-a3fc7c620635
    [type] => Follow
    [actor] => https://example.com/users/example
    [object] => https://mb.mrkiddle.co.uk/user/test
)


*/
