<?php
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);
file_put_contents('botman.log', "Request received: " . json_encode($_POST) . "\n", FILE_APPEND);
include_once(__DIR__ . "/../config.php");
require '../vendor/autoload.php';

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\CacheManager;
use BotMan\BotMan\Cache\NullCache;
use BotMan\Drivers\Web\WebDriver;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;

$config = [];

DriverManager::loadDriver(WebDriver::class);

$botman = BotManFactory::create($config);

$botman->hears('hi', fn($bot) => $bot->reply('Hey there!'));
$botman->hears('hello', fn($bot) => $bot->reply('Hello!'));
$botman->hears('help', fn($bot) => $bot->reply('Try asking me about adding assets,searching assets, auditing, or bulk upload.'));
$botman->hears(['.*add.*asset.*'], function ($bot) {
        if ($_SESSION['role'] === 'admin') {
                    $url1 = 'https://dataworks-7b7x.onrender.com/add/bulk_app.php';
                            $url2 = 'https://dataworks-7b7x.onrender.com/add/add_asset.php';
                            $bot->reply("For adding assets, please use the following links:<br><a href='$url1' target='_blank'>Bulk Add Asset</a><br><a href='$url2' target='_blank'>Add Single Asset</a>");
                                } else {
                                            $bot->reply('To add or edit an asset, department, building, or room, please contact the asset management team at distribution@csub.edu.');
                                                }
});
$botman->hears(['.*how.*audit.*'], function ($bot) {
        $bot->reply('To perform an audit, there are two options to start:<br>
                One. You can upload an Excel file containing the asset tags. <a href="https://dataworks-7b7x.onrender.com/audit/upload.php" target="_blank">Click here to upload</a><br>
                    Two. You can search for your department on the asset search page and click Audit. <a href="https://dataworks-7b7x.onrender.com/search/search.php" target="_blank">Click here to search</a>');
});
$botman->hears(['.*bulk.*add.*', '.*add.*multiple.*asset.*'], function ($bot) {
        $bot->reply('Adding an asset, including bulk adding is currently not implemented.');
});
$botman->hears(['.*search.*asset.*', '.*search.*assets.*'], function ($bot) {
        $url = 'https://dataworks-7b7x.onrender.com/search/search.php';
            $bot->reply("You can search for assets here: <a href='$url' target='_blank'>Click here to search</a>");
});
$botman->hears(['.*search.*audit.*', '.*view.*audit.*history.*', '.*view.*audit.*'], function ($bot) {
        $url = 'https://dataworks-7b7x.onrender.com/audit/audit-history/search-history.php';
            $bot->reply("You can view the audit history here: <a href='$url' target='_blank'>Click here to view</a>");
});


$botman->hears('sticker', function ($bot) {
        $bot->sendRequest('sendSticker', [
                    'sticker' => '1'
                        ]);
});
$botman->hears(['.*search.*asset {term}', 'asset {term}'], function ($bot, $term) {
        $url = 'https://dataworks-7b7x.onrender.com/search/search.php?query=' . urlencode($term);
            $bot->reply("Searching for assets related to: $term <br><a href='$url' target='_blank'>Click here to search</a>");
});

$botman->hears('.*search.*assets.*', function ($bot) {
        $url = 'https://dataworks-7b7x.onrender.com/search/search.php';
            $bot->reply("Search url: <a href='$url' target='_blank'>Click here to search</a>");
});

$botman->hears(['what is your name.*', 'who are you.*'], function ($bot) {
        $bot->reply('I am Chatbot, your assistant for managing assets and audits.');
});

$botman->hears('what can you do', function ($bot) {
        $bot->reply('I can help you with adding assets, performing audits, and bulk uploading. Just ask!');
});
$botman->hears('thank you', function ($bot) {
        $bot->reply('You are welcome! If you have any more questions, feel free to ask.');
});
$botman->hears('help', function ($bot) {
        $bot->reply('<a href="https://dataworks-7b7x.onrender.com/help.php" target="_blank">Click here</a> for the help page<br>');
});

$botman->hears('bye', function ($bot) {
        $bot->reply('Goodbye! Have a great day!');
});

$botman->fallback(function ($bot) {
        $bot->reply('I am sorry, I did not understand that. Please try asking something else.');
});
$botman->group(['namespace' => 'App\Http\Controllers'], function ($bot) {
        $bot->hears('start', 'BotManController@startConversation');
            $bot->hears('stop', 'BotManController@stopConversation');
});
$botman->hears('.*', function ($bot) {
        $bot->reply('I am not sure how to respond to that. Please try asking something else.');
});

$botman->listen();

