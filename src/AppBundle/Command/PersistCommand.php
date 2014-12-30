<?php

namespace AppBundle\Command;

use AppBundle\Entity\Package;
use AppBundle\Entity\Contributor;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class PersistCommand extends ContainerAwareCommand{
    protected $em;
    protected $persists;
    protected $batchSize = 20;

    protected function configure()
    {
        $this->setName('app:persist')
        ->addArgument("package_name")
        ->addArgument('contributors', InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->persists = 0;
        $this->em = $this->getContainer()->get('doctrine')->getManager();

        $package_name = $input->getArgument('package_name');
        $contributors = $input->getArgument('contributors');

        $package = $this->persistPackage($package_name);
        foreach ($contributors as $contributor_name) {
            $contributor = $this->persistContributor($contributor_name);
            $this->associateUserWithPackage($contributor, $package);
        }
        $this->em->flush();
    }

    function persistContributor($contributor_name)
    {
        $contributor_name = strtolower($contributor_name);
        if(!($contributor = $this->em->getRepository('AppBundle\Entity\Contributor')->findOneByName($contributor_name)))
        {
            $contributor = new Contributor();
            $contributor->setName($contributor_name);
            $this->batchPersist($contributor);
        }
        return $contributor;
    }

    function associateUserWithPackage(Contributor $user,Package $package)
    {
        echo "{$package->getName()} <-> {$user->getName()}\n";
        if(!$package->getContributors()->contains($user)) $package->addContributor($user);
    }

    function persistPackage($package_name)
    {
        if(!($package = $this->em->getRepository('AppBundle\Entity\Package')->findOneByName($package_name)))
        {
            $package = new Package();
            $package->setName($package_name);
            $this->batchPersist($package);
        }
        return $package;
    }

    private function batchPersist($obj)
    {
        $this->persists++;
        $this->em->persist($obj);
        if(($this->persists%20) === 0) $this->em->flush();
    }
}