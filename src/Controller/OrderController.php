<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\DishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends AbstractController
{
    #[Route('/order', name: 'order_form')]
    public function order(
        Request $request,
        SessionInterface $session,
        DishRepository $dishRepo,
        EntityManagerInterface $em
    ): Response {
        $cart = $session->get('cart', []);
        if (!$cart) {
            return $this->redirectToRoute('client_menu');
        }

        if ($request->isMethod('POST')) {
            $order = new Order();
            $order->setCustomerName($request->request->get('name'));
            $order->setCustomerPhone($request->request->get('phone'));
            $order->setCustomerAddress($request->request->get('address'));
            $order->setStatus('pending');
            $order->setCreatedAt(new \DateTimeImmutable());

            $total = 0;

            foreach ($cart as $id => $qty) {
                $dish = $dishRepo->find($id);
                if ($dish) {
                    $item = new OrderItem();
                    $item->setDish($dish);
                    $item->setQuantity($qty);
                    $item->setPrice($dish->getPrice());
                    $item->setOrder($order);

                    $total += $dish->getPrice() * $qty;
                    $em->persist($item);
                }
            }

            $order->setTotalPrice($total);
            $em->persist($order);
            $em->flush();

            $session->remove('cart');

            return $this->redirectToRoute('client_menu');
        }

        return $this->render('client/order_form.html.twig');
    }
}
