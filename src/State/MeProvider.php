<?php

namespace App\State;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
class MeProvider implements ProviderInterface
{
    public function __construct(
        protected Security $security,
        protected UserRepository $userRepository,
    ) {
        //
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof Get and count($uriVariables) == 0) {
            return $this->security->getUser();
        }
        return null;
    }
}
