<?php
require 'vendor/autoload.php';
require 'config.php';

if (PHP_SAPI == 'cli-server') {
  $_SERVER['SCRIPT_NAME'] = '/index.php';
}

$dbname = $config['db_name'];
$dbuser = $config['db_user'];
$dbpass = $config['db_pass'];
$dbhost = $config['db_host'];

$db = new PDO("mysql:dbname=$dbname;host=$dbhost", $dbuser, $dbpass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$app = new Slim\App();
$app->add(new \Slim\Middleware\Session([
  'name' => 'login',
  'autorefresh' => true,
  'lifetime' => '1 hour'
]));

$container = $app->getContainer();

function getUser($user) {
  global $db;
  $q = $db->prepare("SELECT * FROM user WHERE username = ?");
  $q->execute(array($user));
  $r = $q->fetch();
  return $r;
}

$container['getUser'] = function($u) {
  return getUser;
};
$container['host'] = function () {
  return $_SERVER['HTTP_HOST'];
};
$container['db'] = function () {
  global $db;
  return $db;
};
$container['session'] = function ($c) {
  return new \SlimSession\Helper;
};

$app->get('/.well-known/webfinger', \Art\Webfinger::class);

$app->get('/user/{id}', \Art\User::class);

$app->any('/upload', \Art\Upload::class);

$app->any('/', \Art\Index::class);


$app->get('/uploads/[{path:.*}]', function ($request, $response) {
  $file = __DIR__ . $request->getUri()->getPath();
  if(file_exists($file)) {
    $response = $response->withHeader('content-type', mime_content_type($file));
    return $response->write(file_get_contents($file));
  }
});

$app->run();
