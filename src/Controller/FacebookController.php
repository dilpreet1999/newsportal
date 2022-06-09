<?php

namespace App\Controller;

use App\Entity\User;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class FacebookController extends AbstractController {

    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/facebook", name="connect_facebook_start")
     */
    public function connectAction(ClientRegistry $clientRegistry) {
        // on Symfony 3.3 or lower, $clientRegistry = $this->get('knpu.oauth2.registry');
        // will redirect to Facebook!
        return $clientRegistry
                        ->getClient('facebook') // key used in config/packages/knpu_oauth2_client.yaml
                        ->redirect(['public_profile', 'email' ]);
    }

    /**
     * After going to Facebook, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @Route("/connect/facebook/check", name="connect_facebook_check")
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry) {
        $em = $this->getDoctrine()->getManager();
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)

        /** @var FacebookClient $client */
        $client = $clientRegistry->getClient('facebook');

        try {
            // the exact class depends on which provider you're using
            /** @var FacebookUser $user */
            $fbUser = $client->fetchUser();
            $user = $em->getRepository(User::class)->findOneBy(['facebookId' => $fbUser->getId()]);
            if ($user) {
                $token = new UsernamePasswordToken($user, null, 'user_area', $user->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
                $this->container->get('session')->set('_security_main', serialize($token));
                return$this->redirectToRoute('homepage');
            } else {

                $password = sha1('10000,99999');
                $userEntity = new User();
                $userEntity->setFacebookId($fbUser->getId())
                        ->setName($fbUser->getName())
                        ->setRoles(['ROLE_USER'])
                        ->setPassword($password)
                        ->setCity('null')
                        ->setState('null')
                        ->setMobile('null')

                ;
                if ($fbUser->getEmail() == null) {
                    $userEntity->setEmail('null');
                } else {
                    $userEntity->setEmail($fbUser->getEmail());
                }
                if ($fbUser->getHometown() == null) {
                    $userEntity->setCity('null');
                } else {
                    $userEntity->setEmail($fbUser->getEmail());
                }
                $em->persist($userEntity);
                $em->flush($userEntity);
                $token = new UsernamePasswordToken($userEntity, null, 'user_area', $userEntity->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
                $this->container->get('session')->set('_security_main', serialize($token));
                return$this->redirectToRoute('homepage');
            }




            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
//            var_dump($user); die;
            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            var_dump($e->getMessage());
            die;
        }
    }

}
