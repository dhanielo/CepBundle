<?php

namespace Cruzeiro\Bundle\CepBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PDOException;

/* Corrige erro de campos nulos */
class ScriptsSetNullableCommand extends ContainerAwareCommand
{
    protected $bundle = 'CepBundle';

    protected function configure()
    {
        $this
            ->setName('scripts:'.$this->bundle.':setNullableFields')
            ->setDescription('Set null fields enabled from Database Table, when it was not done by Doctrine mssqlBundle');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $sql = array();
        $sql[] =  'ALTER TABLE cec_cep ALTER COLUMN logradouro NVARCHAR(255) NULL;';
        $sql[] =  'ALTER TABLE cec_cep ALTER COLUMN numeracao NVARCHAR(255) NULL;';
        $sql[] =  'ALTER TABLE cec_cep ALTER COLUMN bairro NVARCHAR(255) NULL;';
        $sql[] =  'ALTER TABLE cec_cep ALTER COLUMN cidade NVARCHAR(255) NULL;';
        $sql[] =  'ALTER TABLE cec_cep ALTER COLUMN uf NVARCHAR(2) NULL;';
        $sql[] =  'ALTER TABLE cec_cep ALTER COLUMN distrito BIT NULL;';

        try {
            foreach ($sql as $query) {
                $em->getConnection()->exec($query);
            }
        } catch (\Exception $e) {
            throw new \Exception('Erro ao executar query: ' . $query);
        }

        $output->writeln($this->bundle . ': SetNullableFields ............................. OK');
    }
}