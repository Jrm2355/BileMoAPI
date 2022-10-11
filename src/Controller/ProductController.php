<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class ProductController extends AbstractController
{
    /**
     * Liste des produits
     *
     * @Route("/api/products", methods={"GET"})
     * @OA\Tag(name="Produit")
     */
    #[Route('/api/products', name: 'listProduct', methods:['GET'])]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool, Request $request): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getProducts']);
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $idCache = "AllProducts". $page . "-" . $limit;

        $jsonProductList = $cachePool->get($idCache, function (ItemInterface $item) use ($productRepository, $serializer, $context, $page, $limit) {
            $item->tag("allProductsCache");
            $productsList = $productRepository->findAllProductsWithPagination($page, $limit);
            return $serializer->serialize($productsList, 'json', $context);
        });

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    /**
     * Détail d'un produit
     *
     * @Route("/api/products/{id}", methods={"GET"})
     * @OA\Tag(name="Produit")
     */
    #[Route('/api/products/{id}', name: 'detailProduct', methods:['GET'])]
    public function getDetailProduct(int $id, ProductRepository $productRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $idCache = "product".$id;
        $context = SerializationContext::create()->setGroups(['getProducts']);
        
        $jsonProduct = $cachePool->get($idCache, function (ItemInterface $item) use ($productRepository, $id, $serializer, $context) {
            $item->tag("productCache".$id);
            if ($product = $productRepository->find($id)){
                return $serializer->serialize($product, 'json', $context);
            } else {
                return null;
            }            
        });
        if ($jsonProduct) {
            return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        
    }
}
