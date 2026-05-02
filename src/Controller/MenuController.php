<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\DishRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends AbstractController
{
    #[Route('/menu', name: 'client_menu')]
    public function index(Request $request, DishRepository $dishRepository, CategoryRepository $categoryRepository): Response
    {
        $categoryId = $request->query->get('cat');
        $categories = $categoryRepository->findAll();

        if ($categoryId) {
            $dishes = $dishRepository->findBy(['category' => $categoryId]);
        } else {
            $dishes = $dishRepository->findAll();
        }

        return $this->render('client/menu.html.twig', [
            'dishes' => $dishes,
            'categories' => $categories,
            'selected' => $categoryId
        ]);
    }
}
