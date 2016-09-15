<?php

namespace Alexsoft\LaravelSocialiteBitbucket;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

class BitbucketSocialiteProvider extends AbstractProvider
{
    /**
     * Get the authentication URL for the provider.
     *
     * @param  string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://bitbucket.org/site/oauth2/authorize', $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return 'https://bitbucket.org/site/oauth2/access_token';
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param  string $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $userUrl = 'https://api.bitbucket.org/2.0/user?access_token='.$token;

        $response = $this->getHttpClient()->get(
            $userUrl
        );

        $user = json_decode($response->getBody(), true);

        $user['email'] = $this->getEmailByToken($token);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['uuid'], 'nickname' => $user['username'], 'name' => Arr::get($user, 'display_name'),
            'email' => Arr::get($user, 'email'), 'avatar' => Arr::get($user, 'avatar_url'),
        ]);
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }

    /**
     * Get the email for the given access token.
     *
     * @param  string  $token
     * @return string|null
     */
    protected function getEmailByToken($token)
    {
        $emailsUrl = 'https://api.bitbucket.org/2.0/user/emails?access_token='.$token;

        try {
            $response = $this->getHttpClient()->get(
                $emailsUrl
            );
        } catch (\Exception $e) {
            return;
        }

        foreach (json_decode($response->getBody(), true)['values'] as $email) {
            if ($email['is_primary'] && $email['is_confirmed']) {
                return $email['email'];
            }
        }

        return '';
    }
}
