<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'listProduct', methods:['GET'])]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $productsList = $productRepository->findAll();

        $jsonProductList = $serializer->serialize($productsList, 'json', ['groups' => 'getProducts']);

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'detailProduct', methods:['GET'])]
    public function getDetailProduct(int $id, ProductRepository $productRepository, SerializerInterface $serializer ): JsonResponse
    {
        $product = $productRepository->find($id);
        if ($product) {
            $jsonProduct = $serializer->serialize($product, 'json', ['groups' => 'getProducts']);
            return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
