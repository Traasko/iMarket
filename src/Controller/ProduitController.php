<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/acceuil')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, TranslatorInterface $t): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('photo')->getData();

            if ($photo) {
                $newFilename = uniqid() . '.' . $photo->guessExtension();

                try {
                    $photo->move(
                        $this->getParameter('upload_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('app_produit');
                }

                $produit->setPhoto($newFilename);
            }
            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', $t->trans('produit.ajoutee'));
        }

        $produit = $em->getRepository(Produit::class)->findAll();

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $em, TranslatorInterface $t): Response
    {
        if ($produit == null) {
            $this->addFlash('danger', $t->trans('produit.introuvable'));
            return $this->redirectToRoute('app_produit_edit');
        }

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $photo = $form->get('photo')->getData();

            if ($photo) {
                $newFilename = uniqid() . '.' . $photo->guessExtension();

                try {
                    $photo->move(
                        $this->getParameter('upload_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('app_produit_edit');
                }

                // Suppression de l'ancienne image
                unlink($this->getParameter('upload_dir') . '/' . $produit->getPhoto());

                $produit->setPhoto($newFilename);
            }

            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', $t->trans('produit.mise_a_jour'));
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(EntityManagerInterface $em, Produit $produit, TranslatorInterface $t): Response
    {
        if ($produit == null) {
            $this->addFlash('danger', $t->trans('produit.introuvable'));
        } else {
            unlink($this->getParameter('upload_dir') . '/' . $produit->getPhoto());
            $em->remove($produit);
            $em->flush();
            $this->addFlash('warning', $t->trans('produit.suprime'));
        }
        return $this->redirectToRoute('app_produit_index');
    }
}
