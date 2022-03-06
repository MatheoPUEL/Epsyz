<?php

namespace App\Controller;

use App\Entity\User;


use App\Form\UpdateUserType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/profil/id={id}', name:'app_user')]
    public function profil(ManagerRegistry $em, int $id): Response
    {

        $user = $em->getRepository(User::class)->find($id);
        return $this->render('user/index.html.twig', ['user' => $user]);
    }


    #[Route('/profil/edit', name: 'app_user_edit')]
    public function edit(Request $request, User $user): Response
    {

        $userInfos = $this->getUser();
        $id = $userInfos->getId();

        /* Update infos */
        $user = $this->em->getRepository(User::class)->find($id);
        $formUpdate = $this->createForm(UpdateUserType::class, $user);
        $formUpdate->handleRequest($request);

        if ($formUpdate->isSubmitted() && $formUpdate->isValid()) {
            $this->em->persist($user);
            $this->em->flush();
            return $this->redirectToRoute('app_user_edit');
        }

        return $this->render('user/edit.html.twig', [
            'formUpdate' => $formUpdate->createView(),
        ]);
    }
}
