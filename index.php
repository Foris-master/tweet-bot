<?php
/**
 * Created by PhpStorm.
 * User: evari
 * Date: 1/22/2018
 * Time: 10:07 AM
 */

use Codebird\Codebird;
use Dotenv\Dotenv;
use MonkeyLearn\Client as MonkeyLearn;

require 'vendor/autoload.php';
require 'helper.php';
$dotenv = new Dotenv(__DIR__);
$dotenv->load();

Codebird::setConsumerKey(getenv('TWITTER_KEY'), getenv('TWITTER_SECRET_KEY'));

$key_words = ['RD Congo', 'Congo', 'Côte d’Ivoire', 'Cote d ivoire', 'Cameroun', 'Cameroon', 'Sénégal', 'Gabon', 'Burkina Faso', 'Mali', 'Togo', 'Congo Brazzaville',
    'Tchad', 'Bénin', 'Niger'];
$cb = Codebird::getInstance();
$ml = new MonkeyLearn(getenv('MONKEY_LEARN_KEY'));
$cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
$cb->setToken(getenv('TWITTER_TOKEN'), getenv('TWITTER_TOKEN_SECRET'));
$followers = $cb->followers_list();

$tweets = [];

try {
    $db = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASSWORD'));
// set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}


$tweets = get_tweets($tweets, $followers['users'], $db, $cb);
$friends = $cb->friends_list();
$tweets = get_tweets($tweets, $friends['users'], $db, $cb);

$tweets = filter_matches($tweets, $key_words);

foreach ($tweets as $tweet) {
    $cb->statuses_retweet_ID("id=" . $tweet['id_str']);
}
$cb->logout();
echo "bot successfully proceed";
