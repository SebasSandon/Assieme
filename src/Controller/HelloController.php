<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    /**
     * @Route("/hello", name="hello")
     */
     public function index($name)
     {
         // template is stored in src/Resources/views/hello/index.html.php
         return $this->render('views/hello/index.html.php', [
                'name' => $name
                ]);
     }
}
