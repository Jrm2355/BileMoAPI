<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'listProduct', methods:['GET'])]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $idCache = "AllProducts";

        $jsonProductList = $cachePool->get($idCache, function (ItemInterface $item) use ($productRepository, $serializer) {
            $item->tag("allProductsCache");
            $productsList = $productRepository->findAll();
            return $serializer->serialize($productsList, 'json', ['getProducts']);
        });

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'detailProduct', methods:['GET'])]
    public function getDetailProduct(int $id, ProductRepository $productRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $idCache = "product".$id;

        $jsonProduct = $cachePool->get($idCache, function (ItemInterface $item) use ($productRepository, $id, $serializer) {
            $item->tag("productCache".$id);
            $product = $productRepository->find($id);
            return $serializer->serialize($product, 'json', ['getProducts']);
        });
        if ($jsonProduct) {
            return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
