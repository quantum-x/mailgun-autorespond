<?php
require_once __DIR__ . '/vendor/autoload.php';
use Mailgun\Mailgun;
$klein = new \Klein\Klein();
$klein->respond(function ($request, $response, $service, $app) {
    $app->register('config', function() {
        $config = new stdClass();
    //Domain that matches a domain in your MailGun account
		$config->domain = 'domain.com';
    
    //Sender address that will be used
		$config->from = 'Your Name <your_name@domain.com>';
		$config->subject = 'RE: Your recent email';
		$config->api_key = 'key-example';
		$config->body = file_get_contents(__DIR__ . "/body.template");
		$config->address_file = __DIR__ . '/.addresses';
        return $config;
    });
});
$klein->respond('POST', '/mail/', function ($request, $response, $service, $app) {
        //Pull in the post variables
        $postParams = $request->paramsPost();

        //Check everything is setup
        if ($app->config->body === false) { $response->code(500); }
        if (!isset($postParams['sender']) || empty($postParams['sender'])) { $response->code(501); }
        $sender = (isset($postParams['Reply-To']))?$postParams['Reply-To']:$postParams['sender'];

        //Check if we've already received an email from this sender
        if (checkAddress($postParams['sender'], $app->config->address_file)) {
                //We'll send them an email
                //If the email is successfully sent, we'll add their address to the white/blacklist
                file_put_contents('log.txt', var_export($postParams,true), FILE_APPEND);
                file_put_contents('log.txt', serialize($postParams), FILE_APPEND);
                $mg = Mailgun::create($app->config->api_key);
                # Now, compose and send your message.
                # $mg->messages()->send($domain, $params);
                $result = $mg->messages()->send($app->config->domain, [
                  'from'    => $app->config->from,
                  'to'      => $sender,
                  'subject' => $app->config->subject,
                  'text'    => $app->config->body
                ]);

                if ($result->getMessage() === "Queued. Thank you.") {
                        addAddress($sender, $app->config->address_file);
                } else {
                        $response->code(500);
                }

        }
});

$klein->dispatch();

function checkAddress($address, $address_file) {
        if (!file_exists($address_file)) { touch($address_file); }
        $addresses = file($address_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return !(array_search($address, $addresses) === FALSE)?FALSE:TRUE;
}

function addAddress($address, $address_file) {
        if (!file_exists($address_file)) { touch($address_file); }
        return file_put_contents($address_file, $address."\n", FILE_APPEND | LOCK_EX);
}
