# Bitbucket OAuth2 Provider for Laravel Socialite

[![Packagist](https://img.shields.io/packagist/v/alexsoft/laravel-socialite-bitbucket.svg?maxAge=2592000)](https://packagist.org/packages/alexsoft/laravel-socialite-bitbucket)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![StyleCI](https://styleci.io/repos/67543422/shield)](https://styleci.io/repos/67543422)

This package allows you to use Laravel Socialite using OAuth 2 of Bitbucket.

## Installation

You can install the package via composer:

```
composer require alexsoft/laravel-socialite-bitbucket
```

Then you should register service provider in your `config/app.php` file:

```
'providers' => [
    // Other service providers
    
    Alexsoft\LaravelSocialiteBitbucket\BitbucketSocialiteProvider::class,

]
```

You will also need to add credentials for the OAuth application that you can get on the Oauth settings page of you Bitbucket account. They should be placed in your `config/services.php` file. You may copy the example configuration below to get started:

```
'bitbucket' => [
    'client_id' => env('BITBUCKET_CLIENT_ID'),
    'client_secret' => env('BITBUCKET_CLIENT_SECRET'),
    'redirect' => env('BITBUCKET_REDIRECT'),
],
```

## Basic usage

So now, you are ready to authenticate users! You will need two routes: one for redirecting the user to the OAuth provider, and another for receiving the callback from the provider after authentication. We will access Socialite using the Socialite facade:

```php
<?php

namespace App\Http\Controllers\Auth;

use Socialite;

class AuthController extends Controller
{
    /**
     * Redirect the user to the Bitbucket authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('bitbucket2')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('bitbucket2')->user();

        // $user->token;
    }
}
```

Of course, you will need to define routes to your controller methods:

```php
Route::get('auth/bitbucket', 'Auth\AuthController@redirectToProvider');
Route::get('auth/bitbucket/callback', 'Auth\AuthController@handleProviderCallback');
```

The redirect method takes care of sending the user to the OAuth provider, while the user method will read the incoming request and retrieve the user's information from the provider.

Bitbucket Oauth2 does not support scopes on request, all scopes are configured in Oauth application settings.

## Retrieving user details

Once you have a user instance, you can grab a few more details about the user:

```php
$user = Socialite::driver('bitbucket2')->user();

// OAuth Two Providers
$token = $user->token;
$refreshToken = $user->refreshToken; // may not always be provided
$expiresIn = $user->expiresIn;

// OAuth One Providers
$token = $user->token;
$tokenSecret = $user->tokenSecret;

// All Providers
$user->getId();
$user->getNickname();
$user->getName();
$user->getEmail();
$user->getAvatar();
```

## Nota bene

Unlike Github Bitbucket provides you only one hour valid tokens so you will need to refresh access tokens.

Here is the piece of code that refreshes your token (requires Guzzle):

```
$options = [
    'auth' => [config('services.bitbucket.client_id'), config('services.bitbucket.client_secret')],
    'form_params' => [
        'grant_type' => 'refresh_token',
        'refresh_token' => "BITBUCKET_REFRESH_TOKEN"
    ]
];

$response = (new GuzzleHttp\Client)
    ->post(https://bitbucket.org/site/oauth2/access_token, $options)
    ->getBody()->getContents();

$response = json_decode($response, true);

$newAccessToken = $response['access_token'];
```

By some reason, after this operation refresh token is not updated, so you do not have to update it in your storage.