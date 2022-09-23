<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\Product;

class AppFixtures extends Fixture
{
    // public function __construct(UserPasswordHasherInterface $passwordHasher)
    // {
    //     $this->passwordHasher = $passwordHasher;
    // }

    public function load(ObjectManager $manager): void
    {
        // Déclaration des données

        $client = [
            [
                "name"=>"BileMo",
                "products"=>[
                    [
                        "name"=>"Iphone X",
                        "description"=>"Ceci est un super Iphone X !!",
                        "price"=>1000,
                    ],
                    [
                        "name"=>"Samsung 10",
                        "description"=>"Ceci est un super Samsung 10 !!",
                        "price"=>999,
                    ],
                    [
                        "name"=>"HUAWEI",
                        "description"=>"Ceci est un super Huawei !!",
                        "price"=>899,
                    ],
                ],
                "users"=>[
                    [
                        "username"=>"Jyon",
                        "email"=>"jeremyon.jy@gmail.com",
                        "lastname"=>"Yon",
                        "firstname"=>"Jeremy",
                    ],
                    [
                        "username"=>"Jules",
                        "email"=>"jules@gmail.com",
                        "lastname"=>"Cliento",
                        "firstname"=>"Jules",
                    ],
                    [
                        "username"=>"Marie",
                        "email"=>"marie@gmail.com",
                        "lastname"=>"Clienta",
                        "firstname"=>"Marie",
                    ],
                ],
            ],
        ];

        // Parcours et enregistrement des données
        foreach ($client as $c) {
            $client = new Client();
            $client->setName($c["name"]);
            $manager->persist($client);

            foreach ($c["products"] as $p) {
                $product = new Product();
                $product->setName($p["name"]);
                $product->setDescription($p["description"]);
                $product->setPrice($p["price"]);
                $product->setClient($client);
                $manager->persist($product);
            }

            foreach ($c["users"] as $u) {
                $user = new User();
                $user->setUsername($u["username"]);
                // $user->setPassword(
                //     $this->passwordHasher->hasPassword(
                //         $user,
                //         $u["password"]));
                $user->setEmail($u["email"]);
                $user->setLastname($u["lastname"]);
                $user->setFirstname($u["firstname"]);
                $user->setClient($client);
                $manager->persist($user);
            }
        }
        $manager->flush();
    }
}
