<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends BaseController {

    /**
     * @Route("/admin/login", name="admin_login")
     */
    public function login(AuthenticationUtils $auth) {
        $error = $auth->getLastAuthenticationError();
        $username = $auth->getLastUsername();
        return $this->render('admin/login.html.twig', [
                    'error' => $error,
                    'lastUser' => $username
        ]);
    }

    /**
     * @Route("/admin/logout", name="admin_logout")
     */
    public function logout() {
        return true;
    }

    /**
     * @Route("/user/login", name="user_login",methods={"POST","GET"})
     */
    public function userLogin(AuthenticationUtils $auth, Request $request) {

        $redirect = $request->get('redirect');
     
        $error = $auth->getLastAuthenticationError();
        $username = $auth->getLastUsername();

        return $this->render('main/login.html.twig', [
                    'error' => $error,
                    'redirect' => $redirect,
                    'lastUser' => $username
        ]);
    }

    /**
     * @Route("/user/logout", name="user_logout")
     */
    public function userLogout() {
        return true;
    }

}
