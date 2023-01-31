<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Form\ContenuPanierType;
use App\Repository\ContenuPanierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/contenu/panier')]
class ContenuPanierController extends AbstractController
{
    #[Route('/', name: 'app_contenu_panier_index', methods: ['GET'])]
    public function index(ContenuPanierRepository $contenuPanierRepository): Response
    {
        return $this->render('contenu_panier/index.html.twig', [
            'contenu_paniers' => $contenuPanierRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contenu_panier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TranslatorInterface $t, ContenuPanierRepository $contenuPanierRepository): Response
    {
        $contenuPanier = new ContenuPanier();
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contenuPanier->setAjout(new \DateTime());

            $contenuPanierRepository->save($contenuPanier, true);

            return $this->redirectToRoute('app_contenu_panier_index', [], Response::HTTP_SEE_OTHER);

            $this->addFlash('success', $t->trans('contenu.ajoute'));
        }

        return $this->render('contenu_panier/new.html.twig', [
            'contenu_panier' => $contenuPanier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_contenu_panier_show', methods: ['GET'])]
    public function show(ContenuPanier $contenuPanier): Response
    {
        return $this->render('contenu_panier/show.html.twig', [
            'contenu_panier' => $contenuPanier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contenu_panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TranslatorInterface $t, ContenuPanier $contenuPanier, ContenuPanierRepository $contenuPanierRepository): Response
    {
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contenuPanierRepository->save($contenuPanier, true);

            return $this->redirectToRoute('app_contenu_panier_index', [], Response::HTTP_SEE_OTHER);

            $this->addFlash('warning', $t->trans('contenu.modifier'));
        }

        return $this->render('contenu_panier/edit.html.twig', [
            'contenu_panier' => $contenuPanier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_contenu_panier_delete', methods: ['POST'])]
    public function delete(Request $request, TranslatorInterface $t, ContenuPanier $contenuPanier, ContenuPanierRepository $contenuPanierRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $contenuPanier->getId(), $request->request->get('_token'))) {
            $contenuPanierRepository->remove($contenuPanier, true);

            $this->addFlash('danger', $t->trans('contenu.suprimer'));
        }

        return $this->redirectToRoute('app_contenu_panier_index', [], Response::HTTP_SEE_OTHER);
    }
}
