<?php

namespace App\Service;

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterOAuthService
{
    private string $apiKey;
    private string $apiSecret;
    private string $callbackUrl;

    public function __construct(string $apiKey, string $apiSecret, string $callbackUrl)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->callbackUrl = $callbackUrl;
    }

    public function getRequestToken()
    {
        $connection = new TwitterOAuth($this->apiKey, $this->apiSecret);
        $requestToken = $connection->oauth('oauth/request_token', ['oauth_callback' => $this->callbackUrl]);

        return $requestToken;
    }

    public function getAccessToken(string $oauthToken, string $oauthVerifier)
    {
        $connection = new TwitterOAuth($this->apiKey, $this->apiSecret, $oauthToken);
        return $connection->oauth('oauth/access_token', ['oauth_verifier' => $oauthVerifier]);
    }

    public function getUserDetails(string $accessToken, string $accessTokenSecret)
    {
        $connection = new TwitterOAuth($this->apiKey, $this->apiSecret, $accessToken, $accessTokenSecret);
        return $connection->get('account/verify_credentials', ['include_email' => 'true']);
    }
}
