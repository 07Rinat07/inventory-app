<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\DiscussionPostCreateDTO;
use App\Entity\DiscussionPost;
use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DiscussionPostController extends AbstractController
{
    #[Route(
        path: '/inventories/{id}/discussion',
        name: 'discussion_post_create',
        methods: ['POST']
    )]
    #[IsGranted('DISCUSSION_WRITE', subject: 'inventory')]
    public function create(
        Inventory $inventory,
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $em
    ): Response {
        $dto = new DiscussionPostCreateDTO();
        $dto->content = (string) $request->request->get('content', '');

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json([
                'errors' => (string) $errors,
            ], Response::HTTP_BAD_REQUEST);
        }

        $post = new DiscussionPost(
            inventory: $inventory,
            author: $this->getUser(),
            content: $dto->content
        );

        $em->persist($post);
        $em->flush();

        return $this->json([
            'id' => $post->getId(),
            'createdAt' => $post->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], Response::HTTP_CREATED);
    }
}
