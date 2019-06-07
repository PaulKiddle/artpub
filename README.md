# Artpub

A work-in-process image and media gallery that federates with ActivityPub. This project is in early development and not ready for production use.

If you'd like to help out with this project, send me a message or read how to get started bellow and on the [contributing](CONTRIBUTING.MD) page.

## Features

### Uploading Submissions

A submission is an object that consists of one or more media files. Currently accepted media are image, audio and text.
Submissions can also have text descriptions.

### Writing notes

Write notes at `/write`; they act as microblog statuses.

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

More information is in the [contributing](CONTRIBUTING.MD) document.

### Test server

To run the test server:

1. Run `npm i`
2. Copy test-server.json to node_modules/bot-node/config.json
3. Apply the patches inbox.patch and api.patch to their respective js fields in node_modules/bot-node/routes
4. cd to node_modules/bot-node and run `node index.js`
5. Follow the instructions in node_modules/bot-node/README.md to create test users and post messages
