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
$botman->hears('help', fn($bot) => $bot->reply('Try asking me about adding assets, auditing, or bulk upload.'));
$botman->hears(['how to add asset','how to add assets'], function ($bot) {
    $bot->reply('To add an asset, go to the "Assets" section and click "Add Asset". Fill in the details and save.');
});
$botman->hears(['how to audit','how to perform an audit'], function ($bot) {
    $bot->reply('To perform an audit, navigate to the "Audits" section and select "New Audit". Follow the prompts to complete the audit.');
});
$botman->hears(['how to bulk upload','how to upload multiple assets'], function ($bot) {
    $bot->reply('To bulk upload assets, go to the "Assets" section and click "Bulk Upload". Follow the instructions to upload your files.');
});
$botman->hears('bye', function ($bot) {
    $bot->reply('Goodbye! Have a great day!');
});
$botman->hears(['what is your name', 'who are you'], function ($bot) {
    $bot->reply('I am HelpBot, your assistant for managing assets and audits.');
});
$botman->hears('what can you do', function ($bot) {
    $bot->reply('I can help you with adding assets, performing audits, and bulk uploading. Just ask!');
});
$botman->hears('thank you', function ($bot) {
    $bot->reply('You are welcome! If you have any more questions, feel free to ask.');
});
//----------------------------------------------------------------------------------------------------------
$botman->hears('.*asset {term}', function ($bot, $term) {
    $url = 'https://dataworks-7b7x.onrender.com/search/search.php?query=' . urlencode($term);
    $bot->reply("Searching for assets related to: $term");
    $bot->reply("<a href='$url' target='_blank'>Click here to search</a>");
});
$botman->hears('.*search.*asset.*', function ($bot) {
    $url = 'https://dataworks-7b7x.onrender.com/search/search.php';
    $bot->reply("Search url: <a href='$url' target='_blank'>Click here to search</a>");
});
//----------------------------------------------------------------------------------------------------------
$botman->fallback(function ($bot) {
    $bot->reply("I didn't get that. Try typing 'help'.");
});

$botman->listen();
