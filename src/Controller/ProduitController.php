<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use Knp\Component\Pager\PaginatorInterface;

#[Route('/')]
class ProduitController extends AbstractController
{
    #[Route('/ ', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {

        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/front', name: 'display_front', methods: ['GET'])]
    public function indexUser(Request $request,ProduitRepository $produitRepository, PaginatorInterface $paginator): Response
    {
        $produits = $produitRepository->findAll();
        $produits = $paginator->paginate(
            $produits,
            $request->query->getInt('page', 1),
            4
        );
        return $this->render('produit/indexFront.html.twig', [
            'produits' => $produits,
        ]);
    }



    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(FlashyNotifier $notifier,Request $request, ProduitRepository $produitRepository, MailerInterface $mailer): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produitRepository->save($produit, true);
            $mail = (new Email())
                ->from('wissem.benhouria@esprit.tn')
                ->to('swtbahmed@gmail.com')
                ->subject('Mon beau sujet')
                ->html('<p>Ceci est mon message en HTML</p>')
            ;

            $mailer->send($mail);

            $notifier->info('Commande ajouter');
            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }


    #[Route('/{id}/showproduit', name: 'app_produit_showfront', methods: ['GET'])]
    public function showFront(Produit $produit): Response
    {
        return $this->render('produit/frontproddetails.html.twig', [
            'produit' => $produit,
        ]);
    }
    




    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produitRepository->save($produit, true);

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/deleteprods/{id}', name: 'supprimerP')]

    public function suppPr(ManagerRegistry $doctrine,$id,ProduitRepository $repository)
    {
        //récupérer le classroom à supprimer
        $p= $repository->find($id);
        //récupérer l'entity manager
        $em= $doctrine->getManager();
        $em->remove($p);
        $em->flush();
        return $this->redirectToRoute("app_produit_index");
    }

    #[Route('/search', name: 'recherche_produit', methods: ['GET'])]
    public function search_byname(ProduitRepository $rep, Request $request)
    {
        $info=$request->get('search');
        $prod= $rep->findBy(['prix'=>($info)]);
        return $this->render('produit/index.html.twig',['produits'=>$prod]);
    }
    //PDF
    #[Route('/pdf', name: 'pdf', methods: ['GET'])]
    public function pdf(ProduitRepository $produitRepository)
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('produit/pdf.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();
        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("ListeDesVoyages.pdf", [
            "produits" => true
        ]);
    }
    #[Route('/mail', name: 'mail')]
    public function mail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('wissem.benhouria@esprit.tn')
            ->to('swtbahmed@gmail.com')
            ->subject('Test email')
            ->text('This is a test email sent using the Symfony Mailer Bundle with Gmail.');

        $mailer->send($email);

        return new Response('Email sent successfully.');
    }
    #[Route('/stat',name:'stats')]
    public function statscomd(CommandeRepository $commandeRepository,ProduitRepository $produitRepository)
    {
        $produits= $produitRepository->findAll();
        $prodnom= [];
        $procolor= [];
        $prodcount= [];

        foreach ($produits as $produit)
        {
            $prodnom[] = $produit->getNomproduit();
            $procolor[] = $produit->getColor();
            $prodcount[]= count($produit->getCommandes());

        }
        $commandes=$commandeRepository->countbydate();
        $dates=[];
        $commCount=[];
        foreach ($commandes as $commande)
        {
            $dates[] = $commande['datecommande'];
            $commCount[]= $commande['count'];

        }
        return $this->render('commande/stat.html.twig',[
            'prodnom'=>json_encode($prodnom),
            'prodcolor'=>json_encode($procolor),
            'prodcount'=>json_encode($prodcount),
            'dates'=>json_encode($dates),
            'comcount'=>json_encode($commCount),
        ]);

    }

    #[Route("/email", name: "app_mail")]

    public function sendEmail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('wissem.benhouria@esprit.tn')
            ->to('wissem.benhouria@esprit.tn ')
            ->subject('Hello from Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<div class="success-cont">
        <i class="fas fa-check"></i>
        <h3>Appointment booked Successfully!</h3>
        <p>Appointment booked with <strong>Dr. Darren Elder</strong><br> on <strong>12 Nov
                2019 5:00PM to 6:00PM</strong></p>

    </div>');

        $mailer->send($email);

        return new Response("Success");
    }


/*
    #[Route('/search', name: 'recherche_produit', methods: ['GET'])]
    public function search_bynameFront(ProduitRepository $rep, Request $request, PaginatorInterface $paginator)
    {
        $info=$request->get('search');
        $prod= $rep->findBy(['prix'=>($info)]);
        return $this->render('produit/indexFront.html.twig',['produits'=>$prod]);


        $info=$request->get('search');
        $prod= $rep->findBy(['nomproduit'=>($info)]);
        $produits = $produitRepository->findAll();
        $produits = $paginator->paginate(
            $produits,
            $request->query->getInt('page', 1),
            2
        );
        return $this->render('produit/indexFront.html.twig', [
            'produits' => $produits,
        ]);



    }*/



/*
    #[Route('/search', name: 'recherche_produit_prix', methods: ['GET'])]
    public function search_byPrixFront(ProduitRepository $rep, Request $request, PaginatorInterface $paginator)
    {
        $info = $request->get('search');
        $queryBuilder = $rep->createQueryBuilder('p')
            ->where('p.prix = :prix')
            ->setParameter('prix', $info);
        $pagination = $paginator->paginate($queryBuilder, $request->query->getInt('page', 1), 10);
        return $this->render('produit/indexFront.html.twig', ['produits' => $pagination]);
    }*/
    #[Route('/search', name: 'recherche_produit_nom', methods: ['GET'])]
    public function search_bynameFront(ProduitRepository $rep, Request $request, PaginatorInterface $paginator)
    {
        $info = $request->get('search');
        $queryBuilder = $rep->createQueryBuilder('p')
            ->where('p.nomproduit LIKE :nomproduit')
            ->setParameter('nomproduit', '%' . $info . '%');
        $pagination = $paginator->paginate($queryBuilder, $request->query->getInt('page', 1), 10);
        return $this->render('produit/indexFront.html.twig', ['produits' => $pagination]);
    }
    #[Route('/searchProd', name: 'recherche_produit', methods: ['GET'])]
    public function searchFilterByNameAndPrixe(ProduitRepository $rep, Request $request, PaginatorInterface $paginator)
    {
        $queryBuilder = $rep->createQueryBuilder('p');
        $name = $request->query->get('name');
        if ($name) {
            $queryBuilder->andWhere('p.nomproduit LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }
        $price = $request->query->get('price');
        if ($price) {
            $queryBuilder->orWhere('p.prix = :price')
                ->setParameter('price', $price);
        }
        $pagination = $paginator->paginate($queryBuilder, $request->query->getInt('page', 1), 10);
        return $this->render('produit/indexFront.html.twig', ['produits' => $pagination]);

    }


}
