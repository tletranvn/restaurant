<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Entity\Restaurant;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use DateTimeImmutable;

#[Route('/api/picture', name: 'app_api_picture_')]
class PictureController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private PictureRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    ) {
        // Constructeur
    }

    // une route pour lister toutes les images

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $pictures = $this->repository->findAll();
        $responseData = $this->serializer->serialize($pictures, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true); // true = JSON déjà encodé
    }

    // une route pour créer une image
    #[Route(name: 'new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Récupération du restaurant existant
        $restaurant = $this->manager->getRepository(Restaurant::class)->find($data['restaurantId']);

        if (!$restaurant) {
            return new JsonResponse(['error' => 'Restaurant not found'], Response::HTTP_NOT_FOUND);
        }

        // Création de l'objet Picture
        $picture = new Picture();
        $picture->setTitle($data['title']);
        $picture->setSlug($data['slug']);
        $picture->setImagePath($data['imagePath']);
        $picture->setCreatedAt(new DateTimeImmutable());
        $picture->setRestaurant($restaurant);

        $this->manager->persist($picture);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($picture, 'json', ['circular_reference_handler' => fn ($object) => $object->getId(),]);

        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    // une route pour afficher une image
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $picture = $this->repository->find($id); // si un jour veux findOneBy(['title' => ...]), le changer ici

        if ($picture) {
            $responseData = $this->serializer->serialize($picture, 'json', [
                'circular_reference_handler' => fn ($object) => $object->getId(),
            ]);

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        // Si l'image n'est pas trouvée, on renvoie une erreur 404
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }


    // une route pour modifier une image
    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $picture = $this->repository->find($id);

        if ($picture) {
            $this->serializer->deserialize(
                $request->getContent(),
                Picture::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $picture]
            );

            $picture->setUpdatedAt(new \DateTimeImmutable());

            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // une route pour supprimer une image
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $picture = $this->repository->find($id);

        if ($picture) {
            $this->manager->remove($picture);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

}
