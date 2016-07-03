Elgg Web Services Client
========================

PHP Client for interfacing with Elgg's web services


## Usage

```php

use \hypeJunction\WebServices\Client;

$client = new Client('http://example.com/', 'apikey_abcdef123466');

// Get a list of user's blogs
$result = $client->get('blog.get_posts', [
    'username' => 'my-username',
]);

// Get a user token to interface on user's behalf
$token = $client->getAuthToken('my-username', 'my-password');

// Post a blog
$result = $client->post('blog.save_post', [
   'title' => 'My blog',
   'description' => 'This is what I am blogging about',
   'excerpt' => 'Me blogging',
   'access_id' => 2, // public
   'tags' => 'blog,misc',
], $token);

```