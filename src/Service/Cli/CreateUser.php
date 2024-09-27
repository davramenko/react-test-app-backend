<?php

namespace App\Service\Cli;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function Symfony\Component\String\u;

class CreateUser
{
    public function __construct(
        protected UserPasswordHasherInterface $userPasswordHasher,
        protected EntityManagerInterface $entityManager,
        protected UserRepository $userRepository,
    ) {
        //
    }

    public function createUser(SymfonyStyle $io, string $username, string $role, array $options = []): bool
    {
        if ($this->userRepository->findOneBy(['email' => $username])) {
            $io->error('Username already exists.');
            return false;
        }

        $password = $io->askHidden('Enter password (your type will be hidden): ', function ($plainPassword): string {
            if (empty($plainPassword)) {
                throw new InvalidArgumentException('The password can not be empty.');
            }

            if (u($plainPassword)->trim()->length() < 6) {
                throw new InvalidArgumentException('The password must be at least 6 characters long.');
            }

            return $plainPassword;
        });
        $io->askHidden('Re-enter password: ', function ($plainPassword) use ($password): void {
            if ($plainPassword !== $password) {
                throw new InvalidArgumentException('The passwords do not match.');
            }
        });
        if (!preg_match('/^([^@]+)@/i', $username, $matches)) {
            throw new InvalidArgumentException('Invalid username.');
        }
        $user = new User();
        $user->setEmail($username);
        $user->setFirstName($matches[1]);
        $user->setRoles([$role]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
        if (!empty($options['first-name'])) {
            $user->setFirstName($options['first-name']);
        }
        if (!empty($options['last-name'])) {
            $user->setLastName($options['last-name']);
        }
        if (!empty($options['phone'])) {
            $user->setPhone($options['phone']);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }
}