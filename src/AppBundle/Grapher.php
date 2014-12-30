<?php
/**
 * Created by PhpStorm.
 * User: roinir
 * Date: 26/12/14
 * Time: 14:32
 */

namespace AppBundle;
use Doctrine\ORM\EntityManager;

class Grapher {

    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function pathBetween($user1,$user2)
    {
        // check the users are different
        if($user1 == $user2) return [$user1];
        // check both users have repos on github
        $srcPackages = $this->userPackages($user1);
        $targetPackages = $this->userPackages($user2);
        if(!$srcPackages || !$targetPackages) return null;

        // start the algorithm
        $pathsQueue = [[$user1]];
        $visited = [];

        while($pathsQueue)
        {
            $path = array_pop($pathsQueue);
            $currentNode = end($path);
            foreach($this->neighbours($currentNode) as $dstNode => $package)
            {
                $newPath = array_merge($path,[$package, $dstNode]);
                if($dstNode == $user2) return $newPath;
                elseif(!in_array($dstNode,$visited)) array_unshift($pathsQueue, $newPath);
            }
            $visited[] = $currentNode;
        }

        return null;
    }

    private function neighbours($user, $excludedPackages = [])
    {
        $srcPackages = array_diff($this->userPackages($user), $excludedPackages);
        $neighbours = [];
        foreach($srcPackages as $package)
        {
            $packageContributors = array_fill_keys(array_diff($this->contributorsList($package),[$user]), $package);
            $neighbours = array_merge($neighbours, $packageContributors);
        }

        return $neighbours;
    }

    public function contributorsList($project)
    {
        $package = $this->em->getRepository('AppBundle:Package')->findOneBy(['name' => $project]);
        if($package) return $package->getContributors()->map(function($item) { return $item->getName(); })->toArray();
        else return [];
    }

    public function userPackages($user_name)
    {
        $user = $this->em->getRepository('AppBundle:Contributor')->findOneBy(['name' => $user_name]);
        if($user) return $user->getPackages()->map(function($item) { return $item->getName(); })->toArray();
        else return [];
    }
}