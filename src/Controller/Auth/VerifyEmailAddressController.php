<?php

namespace App\Controller\Auth;

use App\Repository\UserRepository;
use App\Service\Utils\AuthUrlsCreator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class VerifyEmailAddressController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        protected AuthUrlsCreator $authUrlsCreator,
        protected UserRepository $userRepository,
        protected EntityManagerInterface $entityManager,
    ) {
        //
    }

    public function __invoke(Request $request): Response
    {
        $this->logger->info('CheckEmailAvailableController: content: ' . $request->getContent());
        $data = json_decode($request->getContent(), true);

        $result = $this->authUrlsCreator->verifyEmailConfirmationData($data);
        if ($result) {
            $user = $this->userRepository->findOneBy(['email' => $data['email']]);
            if (!$user) {
                $this->logger->error('User with email ' . $data['email'] . ' not found');
                return $this->json(
                    ['success' => false, 'message' => 'User with email ' . $data['email'] . ' not found'],
                    Response::HTTP_NOT_FOUND
                );
            }
            if (!$user->isVerified()) {
                $user->setEmailVerifiedAt(new DateTimeImmutable());
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
            return $this->json(
                ['success' => true],
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        $this->logger->error('Email verification has failed for ' . $data['email']);
        return $this->json(
            ['success' => false, 'message' => 'Email verification has failed for ' . $data['email']],
            Response::HTTP_FORBIDDEN
        );
    }
}
