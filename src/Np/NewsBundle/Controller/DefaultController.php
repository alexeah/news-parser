<?php

namespace Np\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('NpNewsBundle:Default:index.html.twig', array('name' => 1));
    }
}