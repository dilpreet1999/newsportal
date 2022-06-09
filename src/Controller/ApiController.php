<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController {

    /**
     * @Route("/api", name="api")
     */
    public function index(): Response {
        return $this->render('api/index.html.twig', [
                    'controller_name' => 'ApiController',
        ]);
    }

    /**
     * @Route("/api/login", name="api_login", methods={"POST","GET"})
     */
    public function apilogin(): Response {
        $data = $_POST;

        print_r($data);
        $em = $this->getDoctrine()->getManager();
        $email = json_decode($data['email']);
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user) {
            $response = ['message' => 'AuthenticatedSuccessfully', 'key' => $email . time()];
            $response = json_encode($response);
        } else {
            $response = ['message' => 'AuthenticatedFailed'];
            $response = json_encode($response);
        }
        return new JsonResponse($response, 200);
    }

}
