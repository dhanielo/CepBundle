<?php

namespace Cruzeiro\Bundle\CepBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends FOSRestController
{
    /**
     * @Get("/cep/consulta/{cep}")
     */
    public function consultaCepAction($cep)
    {
        $crawler = $this->get('cruzeiro.cep.crawler_correios');
        $consulta = $crawler->consultaCep($cep);

        if (!$consulta) {
            $consulta = array();
        }

        $view = $this->view($consulta, Response::HTTP_OK);

        return $this->handleView($view);
    }
}