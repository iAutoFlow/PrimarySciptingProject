<?php


$requestBody = "grant_type=password&username=YOUR_USERNAME&password=YOUR_PASSWORD&client_id=YOUR_APP_ID&redirect_uri=YOUR_URL&client_secret=YOUR_APP_SECRET";

$authRefreshRequestUrl = "https://podio.com/oauth/token?grant_type=refresh_token&client_id=iautoflow&client_secret=QKCdDrc5JQMCzYJtmNX7QkqzmBEdiLZq6MdQoL5YGTaIc3jrBRnFJBnXjBtd8HXL&refresh_token=REFRESH_TOKEN";
//When the access token expires, you can use the refresh_token to "refresh" your access, and gain another access_token. To use the refresh_token you need to do a POST request to our token-endpoint with the grant_type set to refresh_token:

$requestAuthCodeUrl = "https://podio.com/oauth/authorize?client_id=YOUR_APP_ID&redirect_uri=YOUR_URL&state=YOUR_CSRF_TOKEN";
//When you make the request to obtain the authorization code include the state parameter like so:

$requestAuthCodeUrl = "http://YOUR_URL?code=A_CODE_GENERATED_BY_SERVER&state=YOUR_CSRF_TOKEN";
//When the Podio API redirects the user back to your application the state parameter is included and you can validate it:
//The state parameter is optional. Any string value can be used.

$refreshTokenRequestObject = {
  "access_token": ACCESS_TOKEN,
  "token_type": "bearer",
  "expires_in": EXPIRES_IN,
  "refresh_token": REFRESH_TOKEN,
  "scope": GRANTED_SCOPE_STRING,
  "ref":
  {
    "type": "user",
    "id": USER_ID
  }
}


?>