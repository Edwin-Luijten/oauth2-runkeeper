# Runkeeper Provider for OAuth 2.0 Client

[![Latest Version](https://img.shields.io/github/release/edwin-luijten/oauth2-runkeeper.svg?style=flat)](https://github.com/Edwin-Luijten/oauth2-runkeeper/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/Edwin-Luijten/oauth2-runkeeper/master.svg?style=flat-square)](https://travis-ci.org/Edwin-Luijten/oauth2-runkeeper)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/Edwin-Luijten/oauth2-runkeeper.svg?style=flat-square)](https://scrutinizer-ci.com/g/Edwin-Luijten/oauth2-runkeeper/?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/Edwin-Luijten/oauth2-runkeeper.svg?style=flat-square)](https://scrutinizer-ci.com/g/Edwin-Luijten/oauth2-runkeeper/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/edwin-luijten/oauth2-runkeeper.svg?style=flat-square)](https://packagist.org/packages/edwin-luijten/oauth2-runkeeper)

This package provides Runkeeper OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Install

Via Composer

``` bash
$ composer require edwin-luijten/oauth2-runkeeper
```

## Usage

Usage is the same as The League's OAuth client, using `\League\OAuth2\Client\Provider\Runkeeper` as the provider.

``` php
$provider = new League\OAuth2\Client\Provider\Runkeeper([
    'clientId'          => '{runkeeper-client-id}',
    'clientSecret'      => '{runkeeper-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getFirstName() . ' ' . $user->getLastName());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email :author_email instead of using the issue tracker.

## Credits

- [Edwin Luijten](https://github.com/Edwin-Luijten)
- [All Contributors](https://github.com/Edwin-Luijten/oauth2-runkeeper/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.