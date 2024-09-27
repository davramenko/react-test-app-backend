<?php

namespace App\Controller\Auth;

use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\User;
use App\Service\Api\Normalizer\JsonLdNormalizer;
use App\Service\Utils\AuthUrlsCreator;
use App\Service\Utils\MailerUtility;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[AsController]
class RegisterUserController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        protected MailerUtility $utility,
        protected ValidatorInterface $validator,
        protected EntityManagerInterface $entityManager,
        protected JsonLdNormalizer $normalizer,
        protected UserPasswordHasherInterface $userPasswordHasher,
        protected MailerInterface $mailer,
        protected AuthUrlsCreator $authUrlsCreator,
    ) {
        //
    }

    /**
     * @throws ExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function __invoke(Request $request, User $user): Response
    {
        $user->setRoles(['ROLE_USER']);
        $user->setEmailVerifiedAt(null);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getNewPassword()));
        $this->logger->info('RegisterUserController: user: ' . $user);

        $this->validator->validate($user, ['groups' => ['user:write', 'user:register', 'email:check']]);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $email = (new TemplatedEmail())
            ->from($this->utility->getSender())
            ->to($user->getEmail())
            ->subject('Registration on React Test Application')
            ->htmlTemplate('email/registration_confirmation.html.twig')
            ->context([
                'user' => $user,
                'url' => $this->authUrlsCreator->getEmailConfirmationUrl($user->getEmail()),
            ]);
        $this->mailer->send($email);

        $data = $this->normalizer->normalize($user, ['user:read']);
        return new JsonResponse($data);
    }
}
