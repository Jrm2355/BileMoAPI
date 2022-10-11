<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class UserController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des utilisateurs pour un client.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des utilisateurs d'un client",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="User")
     *
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/users', name: 'listUser', methods:['GET'])]
    public function getUserList(UserRepository $userRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $idCache= "usersList". $page . "-" . $limit;
        $context = SerializationContext::create()->setGroups(['getUsers']);

        $jsonUserList = $cachePool->get($idCache, function(ItemInterface $item) use ($userRepository, $serializer, $page, $limit, $context) {
            $item->tag("allUsersCache");
            $usersList = $userRepository->findByClient($this->getUser(), $page, $limit);
            return $serializer->serialize($usersList, 'json', $context);
        });

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer le détail d'un utilisateurs pour un client.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne le détail d'un utilisateurs",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\Response(
     *     response=404,
     *     description="id incorrect",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\Tag(name="User")
     *
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/users/{id}', name: 'detailUser', methods:['GET'])]
    public function getDetailUser(int $id, UserRepository $userRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getUsers']);
        $idCache = "user".$id;

        $jsonUser = $cachePool->get($idCache, function (ItemInterface $item) use ($id, $serializer, $context, $userRepository){
            $item->tag("userCache".$id);
            if ($user = $userRepository->find($id)) {
                return $serializer->serialize($user, 'json', $context);
            } else {
                return null;
            }
        }); 
        if ($jsonUser){
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }  
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    /**
     * Cette méthode permet de supprimer l'utilisateurs d'un client.
     *
     * @OA\Response(
     *     response=204,
     *     description="Supprime un utilisateur d'un client",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\Tag(name="User")
     *
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/users/{id}', name: 'deleteUser', methods:['DELETE'])]
    public function deleteUser(int $id, UserRepository $userRepository,TagAwareCacheInterface $cachePool): JsonResponse
    {
        
        $user = $userRepository->find($id);
        $userClient = $user->getClient();
        if ($user && $userClient == $this->getUser()) {
            $cachePool->invalidateTags(["allUsersCache"]);
            $userRepository->remove($user, true);
        }
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet d'ajouter un utilisateurs à un client.
     *
     * @OA\Response(
     *     response=201,
     *     description="Ajout d'un utilisateur à un client",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\Tag(name="User")
     *
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/users', name: 'createUser', methods:['POST'])]
    public function createUser(Request $resquest, UserRepository $userRepository, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, TagAwareCacheInterface $cachePool ): JsonResponse
    {
        //récupération des données sous forme de tableau pour avoir l'id du client
        $content = $resquest->toArray();

        $user = new User();
        $user->setUsername($content['username']);
        $user->setemail($content['email']);
        $user->setFirstname($content['firstname']);
        $user->setLastname($content['lastname']);
        $user->setClient($this->getUser()); 

        $userRepository->add($user, true);
        $cachePool->invalidateTags(["allUsersCache"]);
        $context = SerializationContext::create()->setGroups(['getUsers']);

        $jsonUser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location]);
    }
}
