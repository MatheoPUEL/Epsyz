<?php

namespace App\Controller\admin;

use App\Entity\User;
use App\Repository\StructureRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function __construct(UserRepository $userRepository, StructureRepository $structureRepository)
    {
        $this->UserRepository = $userRepository;
        $this->StructureRepository = $structureRepository;
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $UserCount = $this->UserRepository->CountAllUsers();
        $StructureCount = $this->StructureRepository->CountAllStructure();
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'AllUsers' => $UserCount,
            'AllStructure' => $StructureCount,
        ]);
    }
}
