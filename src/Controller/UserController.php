<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/api/usersClient/{client}', name: 'listUser', methods:['GET'])]
    public function getUserList(string $client, UserRepository $userRepository, ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        $clientId = $clientRepository->findBy(['name' => $client]) ;
        $usersList = $userRepository->findByClient($clientId);

        $jsonUserList = $serializer->serialize($usersList, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'detailUser', methods:['GET'])]
    public function getDetailUser(int $id, UserRepository $userRepository, SerializerInterface $serializer ): JsonResponse
    {
        $user = $userRepository->find($id);
        if ($user) {
            $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/users/{id}', name: 'deleteUser', methods:['DELETE'])]
    public function deleteUser(int $id, UserRepository $userRepository ): JsonResponse
    {
        $user = $userRepository->find($id);
        $userRepository->remove($user, true);
        // $em->remove($user);
        // $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/users', name: 'createUser', methods:['POST'])]
    public function createUser(Request $resquest, UserRepository $userRepository, ClientRepository $clientRepository, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator ): JsonResponse
    {
        //$user = $serializer->deserialize($resquest->getContent(), User::class, 'json');

        //récupération des données sous forme de tableau pour avoir l'id du client
        $content = $resquest->toArray();

        $user = new User();
        $user->setUsername($content['username']);
        $user->setemail($content['email']);
        $user->setPassword($content['password']);
        $user->setFirstname($content['firstname']);
        $user->setLastname($content['lastname']);
        $user->setClient($clientRepository->find($content['idClient']));

        $userRepository->add($user, true);

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location]);
    }
}
