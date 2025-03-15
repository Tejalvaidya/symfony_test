<?php

namespace App\Controller;

use League\OAuth1\Client\Server\Twitter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\TwitterUserDetails;

class TwitterAuthController extends AbstractController
{
    private $entityManager;
    private $server;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        // Initialize Twitter OAuth 1.0a Client
        $this->server = new Twitter([
            'identifier'    => $_ENV['TWITTER_CLIENT_ID'],  // API Key
            'secret'        => $_ENV['TWITTER_CLIENT_SECRET'],  // API Secret
            'callback_uri'  => 'your callback url', // Adjust as needed
        ]);
    }

    /**
     * Step 1: Redirect user to Twitter for authentication
     */

     #[Route('/auth/twitter', name: 'auth_twitter')]

    public function redirectToTwitter(SessionInterface $session): RedirectResponse
    {
        // Obtain temporary credentials
        $temporaryCredentials = $this->server->getTemporaryCredentials();

        // Store credentials in session for later use
        $session->set('oauth_token', $temporaryCredentials->getIdentifier());
        $session->set('oauth_token_secret', $temporaryCredentials->getSecret());

        // Redirect user to Twitter authorization URL
        return new RedirectResponse($this->server->getAuthorizationUrl($temporaryCredentials));
    }

    /**
     * Step 2: Handle Twitter Callback
     */

    #[Route('/auth/twitter/callback', name: 'auth_twitter_callback')]

    public function handleTwitterCallback(Request $request, SessionInterface $session)
    {
        // Retrieve stored temporary credentials from session
        $storedOAuthToken       = $session->get('oauth_token');
        $storedOAuthTokenSecret = $session->get('oauth_token_secret');

        // Retrieve tokens returned from Twitter
        $receivedOAuthToken = $request->query->get('oauth_token');
        $oauthVerifier      = $request->query->get('oauth_verifier');

        
        // Ensure session data matches the received token
        if ($storedOAuthToken !== $receivedOAuthToken) {
            die("Error: OAuth token mismatch! Possible session loss or multiple login attempts.");
        }

        // Restore temporary credentials from session
        $temporaryCredentials = $this->server->getTemporaryCredentials();
        $temporaryCredentials->setIdentifier($storedOAuthToken);
        $temporaryCredentials->setSecret($storedOAuthTokenSecret);

        // Exchange request token for an access token
        $tokenCredentials = $this->server->getTokenCredentials(
            $temporaryCredentials,
            $receivedOAuthToken,
            $oauthVerifier
        );

        // Fetch user details from Twitter
        $twitterUser = $this->server->getUserDetails($tokenCredentials);

        
       // Extract user details
        $twitterId  = $twitterUser->uid;
        $username   = $twitterUser->nickname;
        $email      = $twitterUser->email ?? null; // Twitter does not always return email
        $avatar     = $twitterUser->imageUrl;
        $created_at = $twitterUser->created_at;
        
        
        // Check if user already exists in DB
        $user = $this->entityManager->getRepository(TwitterUserDetails::class)->findOneBy(['twitterId' => $twitterId]);
       
        if (!$user) {
            // Create new user
            $user = new TwitterUserDetails();
            $user->setTwitterId($twitterId);
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setAvatar($avatar);
            $user->setCreatedAt($created_at);


            $userdata = [
                'twitter_id' => $user->getTwitterId(),
                'username'   => $user->getUsername(),
                'email'      => $user->getEmail(),
                'avatar'     => $user->getAvatar(),
                'created_at' => $user->getCreatedAt(),
            ];
          

            $this->entityManager->persist($user);
            
            $this->entityManager->flush();

            return $this->json(
                [
                    'message'  => 'User Authenticated Successfully',
                    'UserData' => $userdata,
                ], 
                200, 
                ['Content-Type'         => 'application/json'], 
                [ 'json_encode_options' => JSON_PRETTY_PRINT ] // Correctly passing JSON_PRETTY_PRINT inside an array
            );
        }
        else{
            return $this->json(
                [
                    'message'  => 'User Details already exist in database',
                ]
                );

        }


        // Clear session OAuth tokens to prevent reuse
        $session->remove('oauth_token');
        $session->remove('oauth_token_secret');

        

        // Redirect back to the app with user_id
       // return $this->redirect('your_app://auth_success?user_id=' . $user->getId());
    }
}
