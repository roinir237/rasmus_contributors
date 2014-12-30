<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


class ApiController extends Controller
{
    /**
     * @Route("/api/{org}/{project}/contributors", name="get_package_contributors")
     */
    public function getContributorsAction($org, $project)
    {
        $package = "{$org}/{$project}";
        $grapher = $this->get("grapher");
        $contributors = $grapher->contributorsList($package);
        if(!$contributors) return new JsonResponse(array("message"=> "Package not found"), 404);

        return new JSONResponse(array('contributors'=>$contributors));
    }

    /**
     * @Route("/api/path/{user1}/{user2}", name="get_path")
     */
    public function getPath($user1,$user2)
    {
        $grapher = $this->get("grapher");
        $path = $grapher->pathBetween($user1,$user2);
        if($path) return new JSONResponse(array('path'=>$path));
        else return new JsonResponse(array("message"=> "No path found"), 404);
    }

    /**
     * @Route("/api/{user}/packages", name="get_user_packages")
     */
    public function getUserPackagesAction($user)
    {
        $repository = $this->getDoctrine()->getRepository("AppBundle:Contributor");
        $contributor = $repository->findOneByName($user);
        if(!$contributor) return new JsonResponse(array("message"=> "Contributor not found"), 404);

        return new JSONResponse(array('packages'=>$contributor->packages));
    }
}
