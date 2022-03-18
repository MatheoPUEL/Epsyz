<?php

namespace App\Controller\admin\user;

use App\Entity\User;
use App\Form\AdminUserUpdateType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    public function __construct(UserRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;

    }
    #[Route('/admin/user/list', name: 'app_admin_user_list')]
    public function index(): Response
    {
        $users = $this->repository->findAll();

        return $this->render('admin/user/list.html.twig', [
            'controller_name' => 'AdminUserController',
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/show/{id}', name: 'app_admin_user_show')]
    public function show(Request $request, User $user, ManagerRegistry $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        $form = $this->createForm(AdminUserUpdateType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $this->em->flush();
            return $this->redirectToRoute('app_admin_user_list');
        }

        return $this->render('admin/user/show.html.twig', [
            'controller_name' => 'AdminUserControllerShow',
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }


    #[Route('/admin/user/delete/avatar/{id}', name:'app_admin_avatar_delete', methods: ['POST'])]
    public function delete( Request $request, ManagerRegistry $em, int $id): Response
    {
            $user = $em->getRepository(User::class)->find($id);

            // Permet la suppresion de l'avatar.
            if ($this->isCsrfTokenValid('delete_picture' . $id, $request->get('_token'))) {

                if ($user->getAvatarToken() != "default.png") {
                    $path = "uploads/avatar/" . $user->getAvatarToken();
                    if (!unlink($path)) {
                        return $this->redirectToRoute('/admin/user/show/' . $id);
                    } else {
                        $user->setAvatarToken('default.png');
                        $this->em->persist($user);
                        $this->em->flush();
                        $this->addFlash('success_avatar', 'L\'image a bine été supprimer');
                        return $this->redirect('/admin/user/show/' . $id);
                    }
                }


            } else {
                return $this->redirectToRoute('app_home');
            }


            return $this->redirectToRoute('app_login');
    }

    #[Route('/admin/user/delete/{id}', name:'app_admin_user_delete', methods: ['GET', 'POST'])]
    public function DeleteUser(User $user, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_user' . $user->getId(), $request->get('_token'))) {
            $this->em->remove($user);
            $this->em->flush();
        }
        return $this->redirectToRoute('app_admin_user_list');
    }
}
