<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Routing\Annotation\Route;



class GoogleController extends AbstractController {

    /**
     * Link to this controller to start the "connect" process
     * @param ClientRegistry $clientRegistry
     *
     * @Route("/connect/google", name="connect_google_start")
     *
     * @return RedirectResponse
     */
    public function connectAction(ClientRegistry $clientRegistry) {
        return $clientRegistry
                        ->getClient('google')
                        ->redirect([
                            'profile', 'email' // the scopes you want to access
                        ])
        ;
    }

    /**
     * After going to Google, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @param Request $request
     * @param ClientRegistry $clientRegistry
     *
     * @Route("/connect/google/check", name="connect_google_check")
     * @return RedirectResponse
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry) {
        $em = $this->getDoctrine()->getManager();
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)

        /** @var FacebookClient $client */
        $client = $clientRegistry->getClient('google');


        
        try {
//            // the exact class depends on which provider you're using
            /** @var FacebookUser $user */
            $googleUser = $client->fetchUser();
            $user = $em->getRepository(User::class)->findOneBy(['googleId' => $googleUser->getId()]);
            if ($user) {
                $token = new UsernamePasswordToken($user, null, 'user_area', $user->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
                $this->container->get('session')->set('_security_main', serialize($token));
                return$this->redirectToRoute('homepage');
            } else {

                $passwordstr = rand(10000,99999);
                $password = sha1($passwordstr);
                $userEntity = new User();
                $userEntity->setGoogleId($googleUser->getId())
                        ->setName($googleUser->getName())
                        ->setRoles(['ROLE_USER'])
                        ->setPassword($password)
                        ->setCity('null')
                        ->setState('null')
                        ->setMobile('null')
                        ->setEmail($googleUser->getEmail())
                        

                ;
              
               $em->persist($userEntity);
                $em->flush($userEntity);
                $token = new UsernamePasswordToken($userEntity, null, 'user_area', $userEntity->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
                $this->container->get('session')->set('_security_main', serialize($token));
                return$this->redirectToRoute('homepage');
            }




            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
            
            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            var_dump($e->getMessage());
            die;
        }
    }

}
