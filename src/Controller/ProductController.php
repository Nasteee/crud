<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class ProductController extends AbstractController
{
    /**
     * @Route("/product",
     *     name="product_index",
     *     methods={"GET"})
     */
    public function index(ProductRepository $productRepository, ?UserInterface $user): Response
    {
        if($user){
            $username = $user->getUserIdentifier();
            $roles = $user->getRoles();
        }else{
            $username = "Non Connecté";
            $roles = ['ROLE_NA'];
        }

        $productStock = $productRepository->findStock();
        return $this->render('product/index.html.twig', [
//            'products' => $productRepository->findAll(),
            'products' => $productStock,
            'user' => $username,
            'roles' => $roles,
        ]);
    }

    /**
     * @Route("/admin/product/new",
     *     name="product_new",
     *     methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('brochure')->getData();
            $entityManager = $this->getDoctrine()->getManager();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {

                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setBrochureFilename($newFilename);
                $entityManager->persist($product);
                $entityManager->flush();

                return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
            }


        }
        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }


    /**
     * @Route("/product/{id}",
     *     name="product_show",
     *     methods={"GET"})
     */
    public function show(Product $product, ?UserInterface $user): Response
    {
        if($user){
            $username = $user->getUserIdentifier();
            $roles = $user->getRoles();
        }else{
            $username = "Non Connecté";
            $roles = ['ROLE_NA'];
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'roles' => $roles,
        ]);
    }

    /**
     * @Route("/admin/product/{id}/edit",
     *     name="product_edit",
     *     methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('brochure')->getData();
            $entityManager = $this->getDoctrine()->getManager();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {

                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setBrochureFilename($newFilename);
                $entityManager->persist($product);
                $entityManager->flush();

                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
            }
        }
            return $this->renderForm('product/edit.html.twig', [
                'product' => $product,
                'form' => $form,
            ]);


    }

    /**
     * @Route("/admin/product/{id}",
     *     name="product_delete",
     *     methods={"POST"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
    }
}
