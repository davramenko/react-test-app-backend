<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use ApiPlatform\Validator\ValidatorInterface;

#[AsController]
class CheckEmailAvailableController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        protected UserRepository $userRepository,
        protected ValidatorInterface $validator,
    ) {
        //
    }

    public function __invoke(Request $request, User $user): Response
    {
        $this->logger->info('CheckEmailAvailableController: user: ' . $user);
        $this->validator->validate($user, ['groups' => ['email:check']]);
        $foundUser = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
        return $this->json(['success' => empty($foundUser)]);
    }
}
