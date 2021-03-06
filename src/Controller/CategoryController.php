<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;


class CategoryController extends AbstractController
{
    /**
     * @Route("/category",
     *     name="category_index",
     *     methods={"GET"})
     */
    public function index(CategoryRepository $categoryRepository, ?UserInterface $user): Response
    {
        if($user){
            $username = $user->getUserIdentifier();
            $roles = $user->getRoles();
        }else{
            $username = "Non Connecté";
            $roles = ['ROLE_NA'];
        }
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'user' => $username,
            'roles' => $roles,
        ]);
    }

    /**
     * @Route("/admin/new",
     *     name="category_new",
     *     methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    /**
     * @Route("category/{id}",
     *     name="category_show",
     *     methods={"GET"})
     */
    public function show(Category $category, ?UserInterface $user): Response
    {
        if($user){
            $username = $user->getUserIdentifier();
            $roles = $user->getRoles();
        }else{
            $username = "Non Connecté";
            $roles = ['ROLE_NA'];
        }
        return $this->render('category/show.html.twig', [
            'category' => $category,
            'roles' => $roles,
        ]);
    }

    /**
     * @Route("/admin/{id}/edit",
     *     name="category_edit",
     *     methods={"GET","POST"})
     */
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/{id}",
     *     name="category_delete",
     *     methods={"POST"})
     */
    public function delete(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
    }
}
