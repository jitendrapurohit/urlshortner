This is a complete source code for building your own short url.

Please follow below steps to execute the project.

1) Run schema.php to setup the database and tables needed for the project (URL - http://localhost/urlshortner/schema.php).
2) This project contains a config.php file where you can provide a custom name for your DB and tables.
3) Open http://localhost/urlshortner/ to see the sample web page which inputs a long url(eg. http://example.com)
   and converts it into a shorter format - Sorry for no CSS;)

Use the API-

I've written the api into the generateTinyUrl.php file in api folder. Execute this file with "long_url" as a parameter to which shortnening need to be done.

Eg : http://localhost/urlshortner/api/generateTinyURL.php?long_url=http://example.com

Currently there is no authentication done for this(i.e I've not used any api_key to authenticate the user etc.)