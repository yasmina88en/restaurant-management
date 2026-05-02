<?php

namespace App\Controller;

use App\Entity\Dish;
use App\Form\DishType;
use App\Repository\DishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/dish')]
class DishController extends AbstractController
{
    #[Route('/', name: 'dish_index')]
    public function index(DishRepository $dishRepository): Response
    {
        $dishes = $dishRepository->findAll();
        return $this->render('dish/index.html.twig', [
            'dishes' => $dishes,
        ]);
    }

    #[Route('/new', name: 'dish_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $dish = new Dish();
        $form = $this->createForm(DishType::class, $dish);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                $dish->setImage($newFilename);
            }

            $em->persist($dish);
            $em->flush();

            $this->addFlash('success', 'Dish added successfully!');
            return $this->redirectToRoute('dish_index');
        }

        return $this->render('dish/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'dish_show', requirements: ['id' => '\d+'])]
    public function show(Dish $dish): Response
    {
        return $this->render('dish/show.html.twig', [
            'dish' => $dish,
        ]);
    }

    #[Route('/{id}/edit', name: 'dish_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Dish $dish, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DishType::class, $dish, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                $dish->setImage($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Dish updated successfully!');
            return $this->redirectToRoute('dish_index');
        }

        return $this->render('dish/edit.html.twig', [
            'form' => $form->createView(),
            'dish' => $dish,
        ]);
    }

    #[Route('/{id}/delete', name: 'dish_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Dish $dish, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $dish->getId(), $request->request->get('_token'))) {
            $em->remove($dish);
            $em->flush();
            $this->addFlash('success', 'Dish deleted successfully!');
        }

        return $this->redirectToRoute('dish_index');
    }
}
