<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository,ProduitRepository $produitRepository): Response
    {



        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(FlashyNotifier $notifier, Request $request,int $id, CommandeRepository $commandeRepository, ProduitRepository $produitRepository,MailerInterface $mailer): Response
    {
        $produit = new Produit();
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class,  $commande);
        $form->handleRequest($request);

         if ($form->isSubmitted() && $form->isValid()) {
           $commandeRepository->save($commande, true);
             $this->addFlash('success', 'Commander Enregistre! Merci ');
           return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);

           }



        $em = $this->getDoctrine()->getManager();
        $comm = new Commande();
        $produit = $produitRepository->find($id);
        $comm->addLignecommande($produit);
        $comm->setDatecommande(new \DateTime('now'));
        $prix=$produit->getPrix();
        $quantite =$request->request->get('quantite');
        $intValue = intval($quantite);
        $comm->setPrix($prix * $intValue);
        $em->persist($comm);
        $em->flush();
        return $this->renderForm('commande/new.html.twig', [
           'commande' => $commande,
          'form' => $form,
            'produit' => $produit,
        ]);



    }


    #[Route('/success', name: 'success', methods: ['GET'])]
    public function successpage():Response{

        return $this->render('commande/success.html.twig');

    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, CommandeRepository $commandeRepository): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commandeRepository->save($commande, true);

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }
    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, CommandeRepository $commandeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $commandeRepository->remove($commande, true);
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }
}
