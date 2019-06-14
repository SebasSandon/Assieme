<?php

namespace App\Controller\Manager;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends AbstractController
{
    /**
     * @Route("/manager/welcome", name="manager_welcome")
     */
    public function index()
    {
        return $this->render('manager/welcome/index.html.twig', [
        ]);
    }
}
