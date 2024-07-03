<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
#use Symfony\Component\BrowserKit\Request;
use App\Form\Type\ProductType;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index( ManagerRegistry $doctrine ): Response
    {
        $product = $doctrine->getRepository( Product::class )->findAll();
        if ( !$product ) {
            return $this->render('product/noindex.html.twig', [
                'product' => 'product',
            ]);
        } else {
            return $this->render('product/index.html.twig', ['product' => $product]);
        }
    }


    #[Route('/product/new', name: 'new_product')]
    public function newProduct( ManagerRegistry $doctrine, Request $request ): Response
    {
        $entityManager = $doctrine->getManager();
        $product = new Product();
        $product->setName('');
        $product->setPrice( 0 );
        # FORM -------------------------------------------------------------------------
        $form = $this->createForm( ProductType::class, $product );
        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid() ) {
            // get form data
            $product = $form->getData();
            // data to doctrine entitymanager
            $entityManager->persist( $product );
            // save data
            $entityManager->flush();
            return $this->redirectToRoute('new_product');
        }
        # -------------------------------------------------------------------------------
        return $this->render( 'product/new.html.twig', ['form'=>$form->createView()] );      
    }


    #[Route('/product/del/{id}', name: 'del_product')]
    public function delProduct( ManagerRegistry $doctrine, Request $request, int $id ): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository( Product::class )->find($id);

        if ( !$product) {
            return $this->render('product/noindex.html.twig', [
                'product' => 'product',
            ]);
            exit();
        }
        $text = $product->getName();
        // remove
        $entityManager->remove($product);
        // save / delete
        $entityManager->flush();

        return new Response( 'Product deleted: ' . $text . '<br><a href="/product">Index</a>' );
    }


    #[Route('/product/edit/{id}', name: 'edit_product')]
    public function updateProduct( ManagerRegistry $doctrine, Request $request, int $id ): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository( Product::class )->find($id);

        if ( !$product ) {
            return $this->render('product/noindex.html.twig', [
                'product' => 'product',
            ]);
            exit();
        }
        # FORM -------------------------------------------------------------------------
        $form = $this->createForm( ProductType::class, $product );
        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid() ) {
            // data to doctrine entitymanager
            $entityManager->persist( $product );
            // save data
            $entityManager->flush();
            return $this->redirectToRoute('app_product');
        }
        # -------------------------------------------------------------------------------
        return $this->render( 'product/new.html.twig', ['form'=>$form->createView()] );
    }
}
