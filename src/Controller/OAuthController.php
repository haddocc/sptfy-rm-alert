<?php

namespace App\Controller;

use App\Services\StringEncryptionService;
use Kerox\OAuth2\Client\Provider\SpotifyResourceOwner;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\SpotifyClient;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\UnicodeString;

class OAuthController extends AbstractController
{
    /**
     * After going to Spotify, you're redirected back here
     * because this is the "redirect_route" you configured
     * in @see config/packages/knpu_oauth2_client.yaml
     */
    #[Route("/connect/spotify/check", name:"connect.spotify.check")]
    public function connectSpotifyCheckAction(
        Request $request,
        ClientRegistry $clientRegistry,
        StringEncryptionService $encryptionService
    ): void
    {
        /** @var SpotifyClient $client */
        $client = $clientRegistry->getClient('spotify');
        try {
            /** @var SpotifyResourceOwner $user */
            $code = $request->get('code');
            $redirectUri = new UnicodeString($request->getUri());

            $accessToken = $client->getAccessToken([
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirectUri->before('?')->toString()
                ]);


            //$user = $client->fetchUser();

            $cache = new FilesystemAdapter();
            // encrypt the access token using the secrets service
            $encryptionService->encrypt($cache->getItem('latestAccessTokenKey')->get(), $accessToken);

            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
            //dd($user);
            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            throw $e;
        } catch (\Exception $e) {
        }
    }
}