<?php

namespace App\Controller;

use App\Entity\User;


use App\Form\UpdatePasswordUserType;
use App\Form\UpdateUserType;
use App\Form\UploadAvatarType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

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

    #[Route('/profil/delete/{id}', name:'app_user_delete', methods: ['POST'])]
    public function delete( Request $request, ManagerRegistry $em): Response
    {
        if ($this->getUser()) {
            $userInfos = $this->getUser();
            $id = $userInfos->getId();
            $user = $this->em->getRepository(User::class)->find($id);

            // Permet la suppresion de l'avatar.
            if ($this->isCsrfTokenValid('delete_picture' . $userInfos->getId(), $request->get('_token'))) {

                if ($userInfos->getAvatarToken() != "default.png") {
                    $path = "uploads/avatar/" . $userInfos->getAvatarToken();
                    if (!unlink($path)) {
                        return $this->redirectToRoute('app_user_edit');
                    } else {
                        $user->setAvatarToken('default.png');
                        $this->em->persist($user);
                        $this->em->flush();
                        $this->addFlash('success_avatar', 'Votre imge de profil a bien été supprimer.');
                        return $this->redirectToRoute('app_user_edit');
                    }
                }


            } else {
                return $this->redirectToRoute('app_home');
            }


            return $this->redirectToRoute('app_login');
        }
        return $this->redirectToRoute('app_home');
    }

    #[Route('/profil/edit', name: 'app_user_edit')]
    public function edit(Request $request, UserPasswordHasherInterface $userPasswordHasher, ManagerRegistry $em,): Response
    {
        if ($this->getUser()){
            $userInfos = $this->getUser();
            $id = $userInfos->getId();


            /* Pour les infos générales */
            $user = $this->em->getRepository(User::class)->find($id);
            $formUpdate = $this->createForm(UpdateUserType::class, $user);
            $formUpdate->handleRequest($request);

            if ($formUpdate->isSubmitted() && $formUpdate->isValid()) {
                $user->setUpdatedAt(new \DateTimeImmutable('now'));
                $this->em->persist($user);
                $this->em->flush();

                $this->addFlash('success', 'Vos modifications on bien été enregistrée.');
                return $this->redirectToRoute('app_user_edit');
            }

            /* pour le mot de passe */

            $formUpdatePass = $this->createForm(UpdatePasswordUserType::class, $user);
            $formUpdatePass->handleRequest($request);

            if ($formUpdatePass->isSubmitted() && $formUpdatePass->isValid()) {
                $user->setUpdatedAt(new \DateTimeImmutable('now'));
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $formUpdatePass->get('password')->getData()
                    )
                );
                $this->em->persist($user);
                $this->em->flush();

                $this->addFlash('success_pass', 'Votre mot de passe a bien été modifié.');
                return $this->redirectToRoute('app_user_edit');
            }

            /* pour l'avatar */

            $formUpdateAvatar = $this->createForm(UploadAvatarType::class, $user);
            $formUpdateAvatar->handleRequest($request);

            if ($formUpdateAvatar->isSubmitted() && $formUpdateAvatar->isValid()) {
                    $file = $formUpdateAvatar->get('AvatarToken')->getData();
                    $uniqid = md5(uniqid());
                    $fileName = $uniqid.'.'.$file->guessExtension();
                    $file->move($this->getParameter('avatar_directory'), $fileName);
                    $user->setAvatarToken($fileName);

                    $this->em->persist($user);
                    $this->em->flush();
                    $this->addFlash('success_avatar', 'Votre image de profil a bien été modifé.');
                    return $this->redirectToRoute('app_user_edit');


            }

            $user = $em->getRepository(User::class)->find($id);
            return $this->render('user/edit.html.twig', [
                'form_update_avatar' => $formUpdateAvatar->createView(),
                'form_update'   => $formUpdate->createView(),
                'form_update_pass'   => $formUpdatePass->createView(),
                'user' => $user,
            ]);


        } else {
            return $this->redirectToRoute('app_login');
        }

    }
}
