<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api', name: 'app_api_')]
class SecurityController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer, // Inject any required services here
        private EntityManagerInterface $manager // pour persist et flush
    ) {
        // Constructor logic if needed
    }

    #[Route('/registration', name: 'registration', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(),User::class,'json');
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(['user' => $user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'app_api_login', methods: ['POST'])]
        public function login(#[CurrentUser] ?User $user): JsonResponse
        {
            if (null === $user) {
                return new JsonResponse(['message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
            }
            // If the user is authenticated, you can return their information
            return new JsonResponse(['user' => $user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()], Response::HTTP_OK);
        }



}
