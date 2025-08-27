<?php
include_once "../config.php";
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

require '../vendor/autoload.php';

// use BotMan\BotMan\BotMan;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\BotManFactory;
// use BotMan\BotMan\Cache\CacheManager;
// use BotMan\BotMan\Cache\NullCache;
use BotMan\Drivers\Web\WebDriver;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Cache\SymfonyCache as BotManSymfonyCache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use BotMan\BotMan\Messages\Incoming\Answer;

// ---- DEBUG: show all errors while testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ---- Make sure the cache dir exists & is writable
$cacheDir = __DIR__ . '/botman-cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

// ---- Create the PSR-6 adapter, then wrap it for BotMan
$psr6  = new FilesystemAdapter('botman', 0, $cacheDir);
$cache = new BotManSymfonyCache($psr6);

// ---- Your driver config (example: WebDriver)
$config = [
    // put your driver configs here if needed
];

// ---- Create BotMan with the cache

DriverManager::loadDriver(WebDriver::class);

$botman = BotManFactory::create($config, $cache);
class OnboardingConversation extends Conversation
{
    protected $ticket_type;
    protected $response;

    protected $selected_text;

    public function startTicket()
    {
        $question = Question::create('About what?')
            ->fallback('Unable to create a new database')
            ->callbackId('create_database')
            ->addButtons([
                Button::create('Asset')->value('asset'),
                Button::create('Building')->value('Building'),
                Button::create('Room')->value('Room'),
                Button::create('Department')->value('Department'),
                Button::create('Other')->value('Other'),
            ]);
        $this->ask($question, function (Answer $answer) {
            // Detect if button was clicked:
            $selected_value = $answer->getValue(); // will be either 'yes' or 'no'
            $this->selected_text = $answer->getText(); // will be either 'Of course' or 'Hell no!'
            $this->askQuestion();
        });
        // $response->getText()
        //header("Location: http://localhost:3000/index.php");


    }
    public function askQuestion()
    {

        $this->ask('What seems to be the issue?', function (Answer $answer) {
            // Detect if button was clicked:
            $this->response = $answer->getText();
            $this->say('Thank you. Your ticket was received and will be reviewed by asset management.');
            $insert_q = "INSERT INTO ticket_table (email, type, input, ticket_status) VALUES (?, ?, ?, ?)";
            $insert_stmt = $dbh->prepare($insert_q);
            $insert_stmt->execute([$_SESSION['email'], $this->selected_text, $answer->getText(), 'Incomplete']);

        });
    }
    public function run()
    {
        // This will be called immediately
        $this->startTicket();
    }
}

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
$botman->hears(['.*bulk.*add.*', '.*add.*multiple.*asset.*'], function ($bot) {
    $bot->reply('Adding an asset, including bulk adding is currently not implemented.');
});
//----------------------------------------------------------------------------------------------------------
// URL LINKS
// 

// SEARCHING LINKS
$botman->hears(['.*seach.*asset {term}', 'asset {term}'], function ($bot, $term) {
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
$botman->hears('.*ticket.*', function ($bot) {
    $bot->startConversation(new OnboardingConversation);
});
$botman->fallback(function ($bot) {
    $bot->reply("I didn't get that. Try typing 'help'.");
});

$botman->listen();

