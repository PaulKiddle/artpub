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

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
  'driver'    => 'mysql',
  'host'      => $dbhost,
  'database'  => $dbname,
  'username'  => $dbuser,
  'password'  => $dbpass,
  'charset'   => 'utf8mb4'
]);
$capsule->bootEloquent();

$app = new Slim\App();
$app->add(new \Slim\Middleware\Session([
  'name' => 'login',
  'autorefresh' => true,
  'lifetime' => '1 hour'
]));

$container = $app->getContainer();

$container['session'] = function ($c) {
  return new \SlimSession\Helper;
};

$app->get('/.well-known/webfinger', \Art\Webfinger::class);

$app->get('/user/{id}', \Art\User::class);

$app->get('/gallery/{id}', \Art\Gallery::class);
$app->post('/user/{id}/inbox', \Art\Inbox::class);

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
