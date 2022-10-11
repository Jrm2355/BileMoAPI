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
use OpenApi\Annotations as OA;

class UserController extends AbstractController
{
    /**
     * Liste des utilisateurs liés à un client
     *
     * @Route("/api/users", methods={"GET"})
     * @OA\Tag(name="Utilisateur")
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
     * Détail d'un utilisateur liés à un client
     *
     * @Route("/api/users/{id}", methods={"GET"})
     * @OA\Tag(name="Utilisateur")
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
     * Suppression d'un utilisateurs par son client
     *
     * @Route("/api/users/{id}", methods={"DELETE"})
     * @OA\Tag(name="Utilisateur")
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
     * Création d'un utilisateurs liés à un client
     *
     * @Route("/api/users", methods={"POST"})
     * @OA\Tag(name="Utilisateur")
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
