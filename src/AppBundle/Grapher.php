<?php

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
        // check both users have repos in the db
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

    public function potentialContributors($package)
    {
        $res = [];
        foreach($this->contributorsList($package) as $contributor) {
            foreach ($this->neighbours($contributor) as $dstNode => $packages) {
                if (!is_array($packages)) $packages = [$packages];
                if(!in_array($package, $packages))
                {
                    $res = array_merge_recursive($res, [$dstNode => ['workedOn' => array_fill_keys($packages, ['with' => $contributor])]]);
                    $res[$dstNode]["connections"] = array_key_exists("connections", $res[$dstNode]) ? $res[$dstNode]["connections"] + 1 : 1;
                }
            }
        }

        $res = array_map(function($key,$item){
            return array_merge(['name'=>$key], $item);
        }, array_keys($res), $res);

        usort($res, function ($a, $b){
            if ($a["connections"] == $b["connections"]) {
                return 0;
            }
            return ($a["connections"] < $b["connections"]) ? 1 : -1;
        });

        return $res;
    }

    private function neighbours($user, $excludedPackages = [])
    {
        $srcPackages = array_diff($this->userPackages($user), $excludedPackages);
        $neighbours = [];
        foreach($srcPackages as $package)
        {
            $packageContributors = array_fill_keys(array_diff($this->contributorsList($package),[$user]), $package);
            $neighbours = array_merge_recursive($neighbours, $packageContributors);
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