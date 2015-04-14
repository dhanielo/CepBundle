<?php

namespace Cruzeiro\Bundle\CepBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cep
 *
 * @ORM\Table(name="cec_cep")
 * @ORM\Entity(repositoryClass="CepRepository")
 */
class Cep
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="logradouro", type="string", length=255, nullable=true)
     */
    private $logradouro;

    /**
     * @var string
     *
     * @ORM\Column(name="numeracao", type="string", length=255, nullable=true)
     */
    private $numeracao;

    /**
     * @var string
     *
     * @ORM\Column(name="bairro", type="string", length=255, nullable=true)
     */
    private $bairro;

    /**
     * @var string
     *
     * @ORM\Column(name="cidade", type="string", length=255, nullable=true)
     */
    private $cidade;

    /**
     * @var string
     *
     * @ORM\Column(name="uf", type="string", length=2, nullable=true)
     */
    private $uf;

    /**
     * @var integer
     *
     * @ORM\Column(name="cep", type="integer", nullable=true)
     */
    private $cep;

    /**
     * @var boolean
     *
     * @ORM\Column(name="distrito", type="boolean", nullable=true)
     */
    private $distrito;

    public $error = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getLogradouro()
    {
        return $this->logradouro;
    }

    /**
     * @param string $logradouro
     */
    public function setLogradouro($logradouro)
    {
        $this->logradouro = $logradouro;
    }

    /**
     * @return string
     */
    public function getBairro()
    {
        return $this->bairro;
    }

    /**
     * @param string $bairro
     */
    public function setBairro($bairro)
    {
        $this->bairro = $bairro;
    }

    /**
     * @return string
     */
    public function getCidade()
    {
        return $this->cidade;
    }

    /**
     * @param string $cidade
     */
    public function setCidade($cidade)
    {
        $this->cidade = $cidade;
    }

    /**
     * @return string
     */
    public function getUf()
    {
        return $this->uf;
    }

    /**
     * @param string $uf
     */
    public function setUf($uf)
    {
        $this->uf = $uf;
    }

    /**
     * @return int
     */
    public function getCep()
    {
        return $this->cep;
    }

    /**
     * @param int $cep
     */
    public function setCep($cep)
    {
        $this->cep = $cep;
    }

    /**
     * @return boolean
     */
    public function getDistrito()
    {
        return $this->distrito;
    }

    /**
     * @param boolean $distrito
     */
    public function setDistrito($distrito)
    {
        $this->distrito = $distrito;
    }

    /**
     * @return string
     */
    public function getNumeracao()
    {
        return $this->numeracao;
    }

    /**
     * @param string $numeracao
     */
    public function setNumeracao($numeracao)
    {
        $this->numeracao = $numeracao;
    }
}