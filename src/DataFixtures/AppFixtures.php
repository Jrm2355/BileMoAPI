<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Client;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Déclaration des données

        $client = [
            [
                "name"=>"BileMo",
                "roles"=>['ROLE_ADMIN', 'ROLE_USER'],
                "password"=>"bilemopass",
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
            [
                "name"=>"SFR",
                "roles"=>['ROLE_ADMIN', 'ROLE_USER'],
                "password"=>"bilemopass",
                "users"=>[
                    [
                        "username"=>"ArthurMartin",
                        "email"=>"arthur.m@gmail.com",
                        "lastname"=>"Martin",
                        "firstname"=>"Arthur",
                    ],
                    [
                        "username"=>"Clara",
                        "email"=>"clara@gmail.com",
                        "lastname"=>"Chicita",
                        "firstname"=>"Clara",
                    ],
                ],
            ],
        ];
        $product = [
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
        ];

        // Parcours et enregistrement des données
        foreach ($client as $c) {
            $client = new Client();
            $client->setName($c["name"]);
            $client->setRoles($c["roles"]);
            $client->setPassword(
                $this->passwordHasher->hashPassword(
                    $client,
                    $c["password"]));
            $manager->persist($client);

            foreach ($c["users"] as $u) {
                $user = new User();
                $user->setUsername($u["username"]);
                $user->setEmail($u["email"]);
                $user->setLastname($u["lastname"]);
                $user->setFirstname($u["firstname"]);
                $user->setClient($client);
                $manager->persist($user);
            }
        }
        foreach ($product as $p) {
            $product = new Product();
            $product->setName($p["name"]);
            $product->setDescription($p["description"]);
            $product->setPrice($p["price"]);
            $manager->persist($product);
        }
        $manager->flush();
    }
}
