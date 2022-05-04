<?php

namespace App\Controller\admin\structures;

use App\Entity\Structure;
use App\Entity\User;
use App\Form\AddStructureType;
use App\Form\AdminUserUpdateType;
use App\Repository\StructureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class StructureController extends AbstractController
{
    public function __construct(StructureRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    #[Route('/admin/structure', name: 'app_admin_structure')]
    public function index(): Response
    {
        $structures = $this->repository->findAll();

        return $this->render('admin/structure/list.html.twig', [
            'controller_name' => 'StructureController',
            'structures' => $structures,
        ]);
    }

    #[Route('/admin/structure/edit/{id}', name: 'app_admin_structure_edit')]
    public function edit(Request $request, Structure $structure,ManagerRegistry $em, int $id): Response
    {
        $structure = $em->getRepository(Structure::class)->find($id);

        $form = $this->createForm(AddStructureType::class, $structure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->em->flush();
            return $this->redirectToRoute('app_admin_structure');
        }

        return $this->render('admin/structure/edit.html.twig', [
            'controller_name' => 'StructureController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/structure/add', name: 'app_admin_structure_add')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $structure = new Structure();
        $formAdd = $this->createForm(AddStructureType::class, $structure);
        $formAdd->handleRequest($request);

        if ($formAdd->isSubmitted() && $formAdd->isValid()) {

            $entityManager->persist($structure);
            $entityManager->flush();
            $this->addFlash('success_structure', 'La structure à bien été ajouté.');
            return $this->redirectToRoute('app_admin_structure');
        }

        return $this->render('admin/structure/add.html.twig', [
            'controller_name' => 'StructureController',
            'form' => $formAdd->createView(),
        ]);
    }

    #[Route('/admin/structure/delete/{id}', name:'app_admin_structure_delete', methods: ['GET', 'POST'])]
    public function DeleteStructure(Structure $structure, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_structure' . $structure->getId(), $request->get('_token'))) {
            $this->em->remove($structure);
            $this->em->flush();
            $this->addFlash('success_structure', 'La structure à bien été supprimer.');
        }
        return $this->redirectToRoute('app_admin_structure');
    }
}
