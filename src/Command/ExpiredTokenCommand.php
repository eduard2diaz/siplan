<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\Common\Persistence\ObjectManager;

class ExpiredTokenCommand extends Command
{
    protected static $defaultName = 'token:delete-expired';

    protected $manager;

    public function __construct(ObjectManager $manager = null)
    {
        $this->manager = $manager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Comando que elimina los token de seguridad caducos')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $fecha=new \DateTime('-2 hours');
        $fecha=$fecha->format('d-m-Y H:i');
        $db = $this->manager->getConnection();
        $query = 'Delete from api_token n where n.expires_at < :fecha';
        $stmt = $db->prepare($query);
        $stmt->execute(['fecha'=>$fecha]);
        $io->success('Los token caducos fueron eliminados satisfactoriamente.');
    }
}
