<?php

namespace Cruzeiro\Bundle\CepBundle\Crawler;

use Cruzeiro\Bundle\CepBundle\Entity\Cep;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\DomCrawler\Crawler;

class Correios
{
    private $browser;
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->browser = $this->container->get('buzz');
    }

    /**
     * Método efetua a busca do cep no banco de dados, caso não encontre
     * tenta buscar no site dos correios e atualizar a base local.
     * Nota: Tenta efetuar a busca no site dos correios 3 vezes em caso de erro.
     *
     * @param $cep
     * @param Cep $entity
     * @param int $repeat
     * @return Cep
     * @throws \Exception
     */
    public function consultaCep($cep, Cep $entity = null, $repeat = 0)
    {
        $cep = preg_replace( '/[^0-9]/', '', trim($cep));

        if (is_null($entity)) {
            $cepRepository = $this->container->get('doctrine')->getRepository('CruzeiroCepBundle:Cep');
            $entity = $cepRepository->findOneBy(array('cep' => $cep));

            if (!$entity instanceof Cep) {
                $entity = new Cep();
            }
        }
        /* URL do site dos correios (versão mobile) */
        $config = $this->container->getParameter('cruzeiro_cep');

        try {
            $url = $config['correios']['url'];
            $response = $this->browser->post($url, array(), array(
                    'cepEntrada' => $cep,
                    'tipoCep' => '',
                    'cepTemp' => '',
                    'metodo' => 'buscarCep'
                ));
        } catch (\Exception $e) {
            if ($repeat < 3) {
                $repeat++;
                return $this->consultaCep($cep, $entity, $repeat);
            } else {
                throw new \Exception('Falha de comunicação com o servidor.');
            }
        }

        try {
            $crawler = new Crawler($response->getContent());
            $this->addLogradouro($crawler, $entity);
            $this->addBairro($crawler, $entity);
            $this->addCidadeUf($crawler, $entity);
            $this->addCep($crawler, $entity);

            /* Grava consulta no banco */
            $this->persistConsulta($entity);

        } catch (\Exception $e) {
            $this->container->get('logger')->error($e);
            throw new \Exception('Consulta CEP[' .$cep. '] falhou');
        }

        return $entity;
    }

    private function persistConsulta(Cep $entity)
    {
        /* Se encontrar um erro retorna a entidade sem persistir */
        if (!$entity->error) {
            try {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($entity);
                $em->flush();
            } catch (\Exception $e) {
                if ($e instanceof ORMException || $e instanceof DBALException || $e instanceof \PDOException) {
                    $this->container->get('logger')->error($e);
                    throw new \Exception('CEP: '.$entity->getCep().'] Erro ao inserir dados na base de cep.');
                }
            }
        }
    }

    /**
     * @param Crawler $crawler
     * @param Cep $cep
     */
    private function addLogradouro(Crawler $crawler, Cep $cep)
    {
        try {
            $logradouro = $crawler->filter('.caixacampobranco .resposta:contains("Logradouro: ") + .respostadestaque');
            $numeracao = null;
            if ($logradouro->count()) {
                $logradouro = trim($logradouro->html());
                if (strpos($logradouro, '-') > 0) {
                    $arr_log = explode('-', $logradouro);
                    $logradouro = trim($arr_log[0]);
                    $numeracao = trim($arr_log[1]);
                }
                if (strpos($logradouro, ',') > 0) {
                    $arr_log = explode(',', $logradouro);
                    $logradouro = trim($arr_log[0]);
                    $numeracao = trim($arr_log[1]);
                }
                $cep->setNumeracao($numeracao);
                $cep->setLogradouro($logradouro);
            }
        } catch (\Exception $e) {
            $cep->setLogradouro(null);
        }
    }

    /**
     * @param Crawler $crawler
     * @param Cep $cep
     */
    private function addBairro(Crawler $crawler, Cep $cep)
    {
        try {
            $bairro = $crawler->filter('.caixacampobranco .resposta:contains("Bairro: ") + .respostadestaque');
            if ($bairro->count()) {
                $bairro = trim($bairro->html());
                $cep->setBairro($bairro);
            }
        } catch (\Exception $e) {
            $cep->setBairro(null);
        }
    }

    /**
     * @param Crawler $crawler
     * @param Cep $cep
     */
    private function addCidadeUf(Crawler $crawler, Cep $cep)
    {
        try {
            $cidade_uf = $crawler->filter('.caixacampobranco .resposta:contains("Localidade") + .respostadestaque');
            $cep->setDistrito(false);
            if ($cidade_uf->count()) {
                $distrito = false;
                $cidade_uf = trim($cidade_uf->html());
                $arr_cidade = explode('/', $cidade_uf);
                $cidade = $this->normalizeNome(trim($arr_cidade[0]));
                $uf = trim($arr_cidade[1]);
                if (strpos($uf, 'Distrito') > 0){
                    $uf = substr($uf, 0,2);
                    $distrito = true;
                }
                $cep->setCidade($cidade);
                $cep->setUf($uf);
                $cep->setDistrito($distrito);
            }
        } catch (\Exception $e) {
            $cep->setCidade(null);
            $cep->setUf(null);
        }
    }

    /**
     * @param Crawler $crawler
     * @param Cep $cep
     */
    private function addCep(Crawler $crawler, Cep $cep)
    {
        try {
            $cepSource = $crawler->filter('.caixacampobranco .resposta:contains("CEP: ") + .respostadestaque');

            if ($cepSource->count()) {
                $cepSource = trim($cepSource->html());
                $cep->setCep($cepSource);
            } else {
                $cep->error = true;
            }
        } catch (\Exception $e) {
            $cep->error = true;
        }
    }

    /**
     * Formata string para no padrão de nomes completos.
     * Ex.: 'NOmE sOBreNoME OUTro' -> 'Nome Sobrenome Outro'
     *
     * @param $string
     * @return string
     */
    private function normalizeNome($string)
    {
        $nome = '';
        $encode = mb_detect_encoding($string, mb_list_encodings(), true);
        $arr = explode(' ', trim($string));

        foreach ($arr as $s) {
            if (strlen($s) > 0) {
                $s = trim(mb_strtolower($s, $encode));
                $nome .= ucfirst($s) . ' ';
            }
        }

        return $nome;
    }
}