<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;


class SecurityController extends AbstractController
{

    public function __construct(EntityManagerInterface $em, Environment $renderer)
    {
        $this->em = $em;
        $this->renderer = $renderer;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setAvatarToken("default.png");
            $user->setConfirme(0);
            $user->setConfirmeToken(md5(uniqid()));
            $user->setCreatedAt(new \DateTimeImmutable('now'));
            $user->setUpdatedAt(new \DateTimeImmutable('now'));
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            $this->addFlash('success', 'Votre compte a bien été crée.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
               return $this->redirectToRoute('app_home');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }


    #[Route(path: '/confirmation', name: 'app_confirm_account')]
    public function confirm(Request $request): Response
    {
        if ($this->getUser()) {
            $userInfos = $this->getUser();
            $id = $userInfos->getId();
            $mail = $userInfos->getEmail();
            $confirmToken = $userInfos->getConfirmeToken();
            // recuperationd de toutes les infos de l'utilisateur connecter
            $user = $this->em->getRepository(User::class)->find($id);

            if ($this->isCsrfTokenValid('send_confirmation', $request->get('_token'))) {

                // Plusieurs destinataires
                $to  = $mail;

                // Sujet
                $subject = 'Confirmez votre compte maintenant.';

                // message
                $message = $this->renderer->render('emails/confirm.html.twig', ['token' => $confirmToken, 'id' => $id]);

                // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-type: text/html; charset=utf-8';

                $headers[] = 'From: noreply@epsyz.com';
                // Envoi
                mail($to, $subject, $message, implode("\r\n", $headers));

                $this->addFlash('success', 'Un mail a été envoyer à '. $mail .'');
            }

            return $this->render('security/confirm.html.twig',[
                'user' => $user,
            ]);
        }
        return $this->redirectToRoute('app_home');
    }

    #[Route(path: '/confirmation/verifications/id={id}&token={token}', name: 'app_confirm_validation_account')]
    public function validation(ManagerRegistry $em, int $id, string $token): Response
    {
        $urlId = $id;
        $urlToken = $token;

        $userInfos = $this->getUser();
        $idSession = $userInfos->getId();
        $TokenSession = $userInfos->getConfirmeToken();

        return new Response('url id: ' . $urlId .'</br> url token: ' . $urlToken. '</br> id session: ' . $idSession .'</br> Token Session: ' . $TokenSession);
    }


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
