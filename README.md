# PHP Mailgun Autoresponder
Simple PHP based tool / framework to allow for 'one-time' autoresponder emails via mailgun.

## Use Case
We have this script setup on our job recruitment email. For each person that sends in an email, they get an automatic response.
If their reply is successfully sent, their email is white-listed - subsequent emails from that person will not get the auto-reply.

## Environment Setup
* Requires composer (`php composer.phar install`)
* Designed to be run in a domain root / subdomain root
* Requires URL rewriting. IE, for apache based configs, place the following in your `.htaccess`
```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^.+$ /index.php [NC,L,QSA]
```

## Script Setup
* Edit the index.php file
* Insert / update your configuration settings (domain / sender / subject / api_key, etc)
* Create your response body, by editing `body.template`

## Mailgun Setup
We need to get mailgun to send a copy of all emails to the script. The script URL is of the format http://domain.com/mail/
The /mail/ part is obligatory.

* In the `routes` page of mailgun for your domain, add the URL to your script in the `forward` box, ie: 
`http://autorespond.mydomain.com/mail/`

## Testing
Send an email to the route you've created in mailgun.
You should soon receive a reply matching your configuration.
The email address of the sender should appear in the .addresses file.

## Debugging
Check the `logs` page in mailgun to check if the mail was correctly triggered, and if the script was contacted, and what the response code was.
