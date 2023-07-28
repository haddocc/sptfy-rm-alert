<?php

namespace App\Controller;

use Kerox\OAuth2\Client\Provider\SpotifyResourceOwner;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\SpotifyClient;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OAuthController extends AbstractController
{
    /**
     * After going to Spotify, you're redirected back here
     * because this is the "redirect_route" you configured
     * in @see config/packages/knpu_oauth2_client.yaml
     */
    #[Route("/connect/spotify/check", name:"connect.spotify.check")]
    public function connectSpotifyCheckAction(Request $request, ClientRegistry $clientRegistry): void
    {
        /** @var SpotifyClient $client */
        $client = $clientRegistry->getClient('spotify');
        try {
            /** @var SpotifyResourceOwner $user */
            $accessToken = $client->getAccessToken();
            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
            dd($user);
            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            dd($e->getMessage());
        }
    }
}