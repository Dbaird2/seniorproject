<?php
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);
file_put_contents('botman.log', "Request received: " . json_encode($_POST) . "\n", FILE_APPEND);

require 'vendor/autoload.php';
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\CacheManager;
use BotMan\BotMan\Cache\NullCache;
use BotMan\Drivers\Web\WebDriver;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

$config = [];

DriverManager::loadDriver(WebDriver::class);

$botman = BotManFactory::create($config);

$botman->hears('hi', fn($bot) => $bot->reply('Hey there!'));
$botman->hears('hello', fn($bot) => $bot->reply('Hello!'));
$botman->hears('help', fn($bot) => $bot->reply('Try asking me about adding assets,searching assets, auditing, or bulk upload.'));
$botman->hears(['.*add.*asset.*'], function ($bot) {
    $bot->reply('Adding an asset is currently not implemented');
});
$botman->hears(['.*how.*audit.*'], function ($bot) {
    $bot->reply('To perform an audit, navigate to Start an Audit at the top left and enter a valid excel file.');
    $bot->reply('On the right you should see an input field saying Enter Tags. Simply enter a tag then click submit.');
    $bot->reply('You can change the room number, and notes after you submit the tag.');
    $bot->reply('Once you are done, click the Complete Audit button to finalize the audit.');
});
$botman->hears(['.*bulk.*add.*','.*add.*multiple.*asset.*'], function ($bot) {
    $bot->reply('Adding an asset, including bulk adding is currently not implemented.');
});
//----------------------------------------------------------------------------------------------------------
// URL LINKS
// 

// SEARCHING LINKS
$botman->hears(['.*seach.*asset {term}','asset {term}'], function ($bot, $term) {
    $url = 'https://dataworks-7b7x.onrender.com/search/search.php?query=' . urlencode($term);
    $bot->reply("Searching for assets related to: $term");
    $bot->reply("<a href='$url' target='_blank'>Click here to search</a>");
});
$botman->hears('.*search.*assets.*', function ($bot) {
    $url = 'https://dataworks-7b7x.onrender.com/search/search.php';
    $bot->reply("Search url: <a href='$url' target='_blank'>Click here to search</a>");
});
//----------------------------------------------------------------------------------------------------------
$botman->hears(['what is your name.*', 'who are you.*'], function ($bot) {
    $bot->reply('I am Chatbot, your assistant for managing assets and audits.');
});
$botman->hears('what can you do', function ($bot) {
    $bot->reply('I can help you with adding assets, performing audits, and bulk uploading. Just ask!');
});
$botman->hears('thank you', function ($bot) {
    $bot->reply('You are welcome! If you have any more questions, feel free to ask.');
});
$botman->hears('bye', function ($bot) {
    $bot->reply('Goodbye! Have a great day!');
});
$botman->fallback(function ($bot) {
    $bot->reply("I didn't get that. Try typing 'help'.");
});

$botman->listen();
