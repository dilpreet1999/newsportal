<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Description of GoogleAuthenticator
 *
 * @author dev
 */
class GoogleAuthenticator extends SocialAuthenticator
{

    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * GoogleAuthenticator constructor.
     * @param ClientRegistry $clientRegistry
     * @param EntityManagerInterface $em
     * @param UserManagerInterface $userManager
     */
    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em, UserManagerInterface $userManager)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->userManager = $userManager;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    /**
     * @param Request $request
     * @return AccessToken|mixed
     */
    public function getCredentials(Request $request)
    {
        // this method is only called if supports() returns true

        return $this->fetchAccessToken($this->getGoogleClient());
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|null|object|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()
            ->fetchUserFromToken($credentials);

        $email = $googleUser->getEmail();

        // 1) have they logged in with Google before? Easy!
        $existingUser = $this->em->getRepository(User::class)
            ->findOneBy(['googleId' => $googleUser->getId()]);

        if ($existingUser) {
            $user = $existingUser;
        }
        else {
            // 2) do we have a matching user by email?
            $user = $this->em->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if (!$user) {
                /** @var User $user */
                $user = $this->userManager->createUser();
                $user->setEmail($email);
                $user->setPassword("your chosen password");
            }
        }
        $user->setGoogleId($googleUser->getId());
        $this->userManager->updateUser($user);


        // 2) do we have a matching user by email?
      

        // 3) Maybe you just want to "register" them by creating
        // a User object
        return $userProvider->loadUserByUsername($user->getUsername());

    }

    /**
     * @return GoogleClient
     */
    private function getGoogleClient()
    {
        return $this->clientRegistry->getClient('google');
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return null|Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     *
     * @param Request $request
     * @param AuthenticationException|null $authException
     *
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/connect/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
