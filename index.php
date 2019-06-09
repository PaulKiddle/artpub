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

$app->get('/.well-known/webfinger', \Art\routes\Webfinger::class);

$app->get('/user/{id}', \Art\routes\User::class);

$app->get('/user/{id}/gallery', \Art\routes\Gallery::class)->setName('gallery');
$app->post('/user/{id}/inbox', \Art\routes\Inbox::class);
$app->get('/user/{id}/followers', \Art\routes\Followers::class);
$app->get('/submission/{id}[/]', \Art\routes\Submission::class)->setName('submission');
$app->get('/journal/{id}[/]', \Art\routes\Journal::class)->setName('journal');

$app->any('/settings', \Art\routes\Settings::class);
$app->any('/upload', \Art\routes\Upload::class);
$app->any('/write', \Art\routes\Write::class);
$app->any('/follow', \Art\routes\Follow::class);
$app->any('/notes', \Art\routes\Notes::class);

$app->any('/', \Art\routes\Index::class);


$app->get('/uploads/[{path:.*}]', function ($request, $response) {
  $file = __DIR__ . $request->getUri()->getPath();
  if(file_exists($file)) {
    $response = $response->withHeader('content-type', mime_content_type($file));
    return $response->write(file_get_contents($file));
  }
});

$app->run();
