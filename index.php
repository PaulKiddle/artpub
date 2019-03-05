<?php
require 'vendor/autoload.php';
require 'config.php';

$dbname = $config['db_name'];
$dbuser = $config['db_user'];
$dbpass = $config['db_pass'];

$db = new PDO("mysql:dbname=$dbname;host=localhost", $dbuser, $dbpass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$app = new Slim\App();

function getUser($user) {
  global $db;
  $q = $db->prepare("SELECT * FROM user WHERE username = ?");
  $q->execute(array($user));
  $r = $q->fetch();
  return $r;
}

$app->get('/.well-known/webfinger', function ($request, $response, $args) {
  global $db;
  $resource = $request->getQueryParam('resource');
  list($scheme, $account) = explode(':', $resource);
  list($user, $domain) = explode('@', $account);

  $username = getUser($user)[0];
  $profile = 'http://mb.mrkiddle.co.uk/user/'.$username;
  $json = array(
    'subject' => $resource,
    'aliases' => [$profile],
    'links' => [
      [
        'rel' => "self",
        'type' => 'application/activity+json',
        'href' => $profile
      ]
    ]
  );
  return $response->withJson($json);
});

$app->get('/user/{id}', function($req, $res, $args) {
  $user = getUser($args['id'])[0];
  $json = array(
    '@context' => [
      "https://www.w3.org/ns/activitystreams",
      "https://w3id.org/security/v1"
    ],
    "id" => "http://mb.mrkiddle.co.uk/user/$user",
    "type" => "Person",
    "preferredUsername" => $user,
    "inbox" => "http://mb.mrkiddle.co.uk/user/$user/inbox"
/*    "publicKey" => [
      "id" => "https://mb.mrkiddle.co.uk/user/$user#main-key",
      "owner" => "https://mb.mrkiddle.co.uk/user/$user",
 */
  );
  return $res->withJson($json);
});

$app->any('/', function () {
  global $db;
  if(isset($_POST['submit'])){
    $user = getUser($_POST['username']);
    if($user) {
      if(password_verify($_POST['password'], $user['password'])) {
	echo 'Correct';
      } else {
        echo 'Wrong';
      }
      die;
    }
    $q = $db->prepare("INSERT INTO user (username, password) VALUES(?, ?)");
    $r = $q->execute(array(
      $_POST['username'],
      password_hash($_POST['password'], PASSWORD_DEFAULT)
    ));
    if($r) {
      header("location: /"); die;
    } else {
      print_r($q->errorInfo());
    }
  }
?>
<form method="POST">
  <label>Username <input name="username"></label><br>
  <label>Password <input type="password" name="password"></label>
  <button name="submit">Sign up</button>
</form>

<ol>
  <li>Login
  <li>Upload
  <li>View Profile/Gallery
  <li>Follow from remote
  <li>Local follow
  <li>Follow remote
  <li>User roles
  <li>Moderation
</ol>
<?php
});

$app->run();
