<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;

#[Route('api/restaurant', name: 'app_api_restaurant_')]
class RestaurantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private RestaurantRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    ) {
        // Constructeur
    }

    // une route pour lister tous les restaurants
    #[Route('', name: 'index', methods: ['GET'])]
    
    /**
    * @OA\Get(
    *     path="/api/restaurant",
    *     summary="Lister tous les restaurants",
    *     @OA\Response(
    *         response=200,
    *         description="Liste des restaurants",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(
    *                 type="object",
    *                 @OA\Property(property="id", type="integer", example=1),
    *                 @OA\Property(property="name", type="string", example="Nom du restaurant"),
    *                 @OA\Property(property="description", type="string", example="Restaurant gastronomique"),
    *                 @OA\Property(property="maxGuest", type="integer", example=50),
    *                 @OA\Property(property="createdAt", type="string", format="date-time", example="2023-10-01T12:00:00Z")
    *             )
    *         )
    *     )
    * )
    */

    public function index(): Response
    {
        $restaurants = $this->repository->findAll();
        return $this->json($restaurants);
    }

    // une route pour créer un restaurant
    #[Route(name: 'new', methods: ['POST'])]
    
    /** 
    * @OA\Post(
    *     path="/api/restaurant",
    *     summary="Créer un nouveau restaurant",
    *     @OA\RequestBody(
    *         required=true,
    *         description="Données du restaurant à créer",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="name", type="string", example="Nom du restaurant"),
    *             @OA\Property(property="description", type="string", example="Description précise"),
    *             @OA\Property(property="maxGuest", type="integer", example="50")
    *         )
    *     ),
    *     @OA\Response(
    *         response=201,
    *         description="Restaurant créé avec succès",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="id", type="integer", example="1"),
    *             @OA\Property(property="name", type="string", example="Nom du restaurant"),
    *             @OA\Property(property="description", type="string", example="Nom du restaurant"),
    *             @OA\Property(property="maxGuest", type="integer", example="50"),
    *             @OA\Property(property="createdAt", type="string", format="date-time", example="2023-10-01T12:00:00Z")
    *         )
    *     )
    * )
    */
    public function new(Request $request): JsonResponse
    {
        $restaurant = $this->serializer->deserialize($request->getContent(),Restaurant::class,'json');
        $restaurant->setCreatedAt(new DateTimeImmutable());

        /* Créer une nouvelle instance de Restaurant pour tester
        $restaurant = new Restaurant();
        $restaurant->setName('Quai Antique');
        $restaurant->setDescription('Restaurant de fruits de mer');
        $restaurant->setCreatedAt(new \DateTimeImmutable());
        $restaurant->setAmOpeningTime([]); // Ajoute si nécessaire selon ton entité
        $restaurant->setPmOpeningTime([]);
        $restaurant->setMaxGuest(50); 
        */

        //à stocker en BDD
        $this->manager->persist($restaurant);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($restaurant,'json');
        
        // on peut aussi ajouter une URL avec location mais pas obligatoire
        //$location = $this->urlGenerator->generate('app_api_restaurant_show', ['id' => $restaurant->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true); // <- true active le contenu JSON brut, s'il y a $location, on peut l'ajouter ici
        //return new JsonResponse($responseData, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    // une route pour afficher un restaurant
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    
    /** 
    * @OA\Get(
    *     path="/api/restaurant/{id}",
    *     summary="Afficher un restaurant par son ID",
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         description="ID du restaurant à afficher",
    *         @OA\Schema(type="integer", example=1)
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Restaurant trouvé avec succès",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="id", type="integer", example="1"),
    *             @OA\Property(property="name", type="string", example="Nom du restaurant"),
    *             @OA\Property(property="description", type="string", example="Nom du restaurant"),
    *             @OA\Property(property="maxGuest", type="integer", example="50"),
    *             @OA\Property(property="createdAt", type="string", format="date-time", example="2023-10-01T12:00:00Z")
    *         )
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Restaurant non trouvé "
    *     )
    * )
    */
    public function show(int $id): JsonResponse
    {
        //$restaurant = chercher RESTAURANT ID =1
        $restaurant = $this->repository->find($id); //si chercher par nom/email on utilise findOneBy()

        //if ($restaurant) {
            //$responseData = $this->serializer->serialize($restaurant, 'json');

            //return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        //} 
        if ($restaurant) {
            $responseData = $this->serializer->serialize($restaurant, 'json', [
            'circular_reference_handler' => function ($object) {
            return $object->getId();
        }
    ]);

    return new JsonResponse($responseData, Response::HTTP_OK, [], true);
}
  
        // Si le restaurant n'est pas trouvé, on renvoie une erreur 
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // une route pour modifier un restaurant
    #[Route('/{id}', name: 'edit', methods: ['PUT'])]

    /**
    * @OA\Put(
    *     path="/api/restaurant/{id}",
    *     summary="Modifier un restaurant existant",
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         description="ID du restaurant à modifier",
    *         @OA\Schema(type="integer", example=1)
    *     ),
    *     @OA\RequestBody(
    *         required=true,
    *         description="Données mises à jour du restaurant",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="name", type="string", example="Nom mis à jour"),
    *             @OA\Property(property="description", type="string", example="Nouvelle description"),
    *             @OA\Property(property="maxGuest", type="integer", example=80)
    *         )
    *     ),
    *     @OA\Response(
    *         response=204,
    *         description="Restaurant mis à jour avec succès"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Restaurant non trouvé"
    *     )
    * )
    */

    public function edit(int $id, Request $request): JsonResponse
    {
        $restaurant = $this->repository->find($id); //si chercher par nom/email on utilise findOneBy()

        if ($restaurant) {
            $this->serializer->deserialize($request->getContent(),Restaurant::class,'json',[AbstractNormalizer::OBJECT_TO_POPULATE => $restaurant]);

            $restaurant->setUpdatedAt(new DateTimeImmutable());
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }  

    // une route pour supprimer un restaurant
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    
    /**
    * @OA\Delete(
    *     path="/api/restaurant/{id}",
    *     summary="Supprimer un restaurant par son ID",
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         description="ID du restaurant à supprimer",
    *         @OA\Schema(type="integer", example=1)
    *     ),
    *     @OA\Response(
    *         response=204,
    *         description="Restaurant supprimé avec succès"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Restaurant non trouvé"
    *     )
    * )
    */

    public function delete(int $id): JsonResponse
    {
        //$restaurant = chercher et supprimer RESTAURANT ID =1
        $restaurant = $this->repository->find($id); //si chercher par nom/email on utilise findOneBy()

        if ($restaurant) {
            $this->manager->remove($restaurant);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        // Si le restaurant n'est pas trouvé, on renvoie une erreur
        return new JsonResponse(null, Response::HTTP_NOT_FOUND); 
    }

    /* on peut créer une méthode pour rechercher un restaurant par nom ou par nombre de places si besoin
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $criteria = [];

        if ($request->query->get('name')) {
            $criteria['name'] = $request->query->get('name');
        }

        if ($request->query->get('maxGuest')) {
            $criteria['maxGuest'] = $request->query->get('maxGuest');
        }

        // Tu peux ajouter autant de champs que tu veux contrôler ici

        $restaurants = $this->repository->findBy($criteria);

        if (empty($restaurants)) {
            return new JsonResponse(['message' => 'No matching restaurant found.'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($restaurants, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
*/
}