<?php

namespace App\Controller;

use App\Entity\Program;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/my-profile", name="my-profile")
     */
    public function index(): Response
    {
        $userPrograms = $this->getDoctrine()->getRepository(Program::class)->findBy(['owner' => $this->getUser()]);
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'programs' => $userPrograms,
        ]);
    }
}
