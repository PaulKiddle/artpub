# Artpub

A work-in-process image and media gallery that federates with ActivityPub. This project is in early development and not ready for production use.

## Features

### Uploading Submissions

A submission is an object that consists of one or more media files. Currently accepted media are image, audio and text.
Submissions can also have text descriptions.

### Following

Activitypub users can follow the activity of artpub users in the usual way by subscribing to `username@domain`.

Artpub users can also follow the activity of remote activitypub users in a similar way.
Notifications will be listed on the `/notes` page.

## Development

NB: If anything in this documentation is missing, unclear or incorrect, please raise an issue!

Requirements:

 - PHP7
  - pdo_mysql
 - Composer
 - docker
 - docker-compose

Create a `config.php` file with the following contents:

```php
<?php
$config = array(
 'db_name' => 'art',
 'db_user' => 'root',
 'db_pass' => 'root',
 'db_host' => '172.18.0.2',
 'disable_signup' => false
);
```

Run `composer install`.

Run `./start.sh` - your site should now be up and running on localhost:3030.

## Structure

 - Routing and general site configuration is in `index.php`.
 - Route views are in `src`.
 - Views may use components in `src/components`.
 - Models are in `src/models`.
