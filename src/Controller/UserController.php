<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface; 

use App\Entity\User; 


class UserController extends AbstractController
{
    #[Route('/api/users', name: 'get_users', methods: ['GET'])]

    public function getAllUsers(EntityManagerInterface $entityManager): JsonResponse
    {
        // Fetch all users from the database
        $users = $entityManager->getRepository(User::class)->findAll();

        if (!$users) {
            return new JsonResponse(['message' => 'No users found'], 404);
        }

        // Convert user objects to an array
        $userData = [];
        foreach ($users as $user) {
            $userData[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'address' => $user->getAddress(),
                'roles' => $user->getRoles()
            ];
        }

        return new JsonResponse($userData);
    }
}