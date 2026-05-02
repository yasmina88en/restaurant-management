<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\DishRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(
        CategoryRepository $categoryRepository,
        DishRepository $dishRepository,
        OrderRepository $orderRepository,
    ): Response {
        // Statistiques de base
        $totalCategories = $categoryRepository->count([]);
        $totalDishes = $dishRepository->count([]);
        $totalOrders = $orderRepository->count([]);

        // Calcul du revenu total
        $orders = $orderRepository->findAll();
        $totalRevenue = 0;
        foreach ($orders as $order) {
            $totalRevenue += $order->getTotalPrice();
        }

        // Calcul du revenu moyen par commande
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Nombre de plats par catégorie
        $categories = $categoryRepository->findAll();
        $dishesPerCategory = [];
        foreach ($categories as $category) {
            $dishesPerCategory[] = [
                'name' => $category->getName(),
                'count' => count($category->getDishes())
            ];
        }

        // Top 5 des plats les plus chers
        $topDishes = $dishRepository->findBy([], ['price' => 'DESC'], 5);

        // Dernières commandes
        $recentOrders = $orderRepository->findBy([], ['createdAt' => 'DESC'], 5);

        // Prix moyen des plats
        $dishes = $dishRepository->findAll();
        $totalPrice = 0;
        foreach ($dishes as $dish) {
            $totalPrice += $dish->getPrice();
        }
        $avgDishPrice = $totalDishes > 0 ? $totalPrice / $totalDishes : 0;

        return $this->render('dashboard/dashboard.html.twig', [
            'totalCategories' => $totalCategories,
            'totalDishes' => $totalDishes,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'avgOrderValue' => $avgOrderValue,
            'avgDishPrice' => $avgDishPrice,
            'dishesPerCategory' => $dishesPerCategory,
            'topDishes' => $topDishes,
            'recentOrders' => $recentOrders,
        ]);
    }
}
