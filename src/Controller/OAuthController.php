<?php

namespace App\Controller;

use Kerox\OAuth2\Client\Provider\Exception\SpotifyIdentityProviderException;
use Kerox\OAuth2\Client\Provider\SpotifyResourceOwner;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\SpotifyClient;
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
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)

        /** @var SpotifyClient $client */
        $client = $clientRegistry->getClient('spotify');

        try {
            /** @var SpotifyResourceOwner $user */
            $user = $client->fetchUser();

            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
            dd($user);
            // ...
        } catch (SpotifyIdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            dd($e->getMessage());
        }
    }
}