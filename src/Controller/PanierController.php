<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Form\PanierType;
use App\Entity\ContenuPanier;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('/', name: 'app_panier_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository): Response
    {
        return $this->render('panier/index.html.twig', [
            'paniers' => $panierRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_panier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TranslatorInterface $t, PanierRepository $panierRepository): Response
    {
        $panier = new Panier();
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $panier->setAchat(new \DateTime());
            $panier->setUser($this->getUser());

            $panierRepository->save($panier, true);

            return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);

            $this->addFlash('success', $t->trans('article.ajoute'));
        }

        return $this->render('panier/new.html.twig', [
            'panier' => $panier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_panier_show', methods: ['GET'])]
    public function show(Panier $panier): Response
    {
        return $this->render('panier/show.html.twig', [
            'panier' => $panier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TranslatorInterface $t, Panier $panier, PanierRepository $panierRepository): Response
    {
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $panierRepository->save($panier, true);

            return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);

            $this->addFlash('warning', $t->trans('article.modifier'));
        }

        return $this->render('panier/edit.html.twig', [
            'panier' => $panier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_panier_delete', methods: ['POST'])]
    public function delete(Request $request, TranslatorInterface $t, Panier $panier, PanierRepository $panierRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $panier->getId(), $request->request->get('_token'))) {
            $panierRepository->remove($panier, true);
        }

        return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);

        $this->addFlash('danger', $t->trans('article.suprimer'));
    }

    #[Route('/etat/{id}', name: 'panier_etat')]
    public function etat(Panier $panier = null, EntityManagerInterface $em)
    {
        if ($panier != null) {
            if ($panier->isEtat()) {
                $panier->setEtat(false);
            } else {
                $panier->setEtat(true);
            }
            $em->persist($panier);
            $em->flush();
        }
        
        return $this->redirectToRoute('app_article_show', ['id' => $panier->getContenuPaniers()->getId() ]);
    }
}
