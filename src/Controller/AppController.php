<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route('/', name: 'app')]
    public function index()
    {
        // On peut ici rendre un twig minimal avec juste la div app React
        return $this->render('app/app.html.twig');
    }
}

?>
