<?php

namespace App\Controller;

use App\Service\UserStoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserStoreService $userStoreService
    ) {
    }

    #[Route('/signup', name: 'app_signup', methods: ['GET', 'POST'])]
    public function signup(Request $request): Response
    {
        $error = null;

        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email'));
            $password = (string) $request->request->get('password');

            try {
                if (!$email || !$password) {
                    throw new \RuntimeException('Email and password are required.');
                }

                $this->userStoreService->createUser($email, $password);
                $request->getSession()->set('user_email', $email);

                return $this->redirectToRoute('app_dashboard');
            } catch (\Throwable $exception) {
                $error = $exception->getMessage();
            }
        }

        return $this->render('auth/signup.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        $error = null;

        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email'));
            $password = (string) $request->request->get('password');

            if ($this->userStoreService->validateCredentials($email, $password)) {
                $request->getSession()->set('user_email', $email);

                return $this->redirectToRoute('app_dashboard');
            }

            $error = 'Invalid email or password.';
        }

        return $this->render('auth/login.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): RedirectResponse
    {
        $request->getSession()->remove('user_email');

        return $this->redirectToRoute('app_login');
    }
}