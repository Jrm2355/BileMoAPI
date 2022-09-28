<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'listProduct', methods:['GET'])]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $idCache = "getAllProducts";

        $productsList = $cachePool->get($idCache, function(ItemInterface $item) use ($productRepository) {
            $item->tag("productsCache");
            return $productRepository->findAll();
        });

        $jsonProductList = $serializer->serialize($productsList, 'json', ['groups' => 'getProducts']);

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    // $idCache = "getAllProducts";
    // $jsonProductList = $cachePool->get($idCache, function(ItemInterface $item) use ($productRepository, $serializer) {
    //     $item->tag("productsCache");
    //     $productsList = $productRepository->findAll();
    //     return $serializer->serialize($productsList, 'json', ['groups' => 'getProducts']);
    // });    
    // return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);

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
