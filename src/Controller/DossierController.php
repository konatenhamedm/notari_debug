<?php

namespace App\Controller;

use App\Classe\UploadFile;
use App\Entity\Archive;
use App\Entity\Client;
use App\Entity\DocumentClient;
use App\Entity\Dossier;
use App\Entity\DossierWorkflow;
use App\Entity\Enregistrement;
use App\Entity\Identification;
use App\Entity\InfoClassification;
use App\Entity\Obtention;
use App\Entity\Piece;
use App\Entity\PieceVendeur;
use App\Entity\Redaction;
use App\Entity\Remise;
use App\Entity\RemiseActe;
use App\Entity\SuiviDossierWorkflow;
use App\Form\DossierType;
use App\Form\UploadFileType;
use App\Repository\ClientRepository;
use App\Repository\DocumentSigneRepository;
use App\Repository\DocumentTypeActeRepository;
use App\Repository\DossierRepository;
use App\Repository\CourierArriveRepository;
use App\Repository\DossierWorkflowRepository;
use App\Repository\FichierRepository;
use App\Repository\IdentificationRepository;
use App\Repository\PieceRepository;
use App\Repository\TypeRepository;
use App\Repository\WorkflowRepository;
use App\Service\ActionRender;
use App\Service\FormError;
use App\Service\Omines\Adapter\ArrayAdapter;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\NumberColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * @Route("/admin")
 */
class DossierController extends AbstractController
{
    use FileTrait;

    private $dossierWorkflow;

    const TAB_ID = 'smartwizard-3';

    private const FILE_PATH = 'acte_vente';

    private $em;

    public function __construct(WorkflowInterface $dossierWorkflow, EntityManagerInterface $em)
    {
        $this->dossierWorkflow = $dossierWorkflow;
        $this->em = $em;
    }

    /**
     * @Route("/dossier/acteVente", name="dossierActeVente")
     * @param DossierRepository $repository
     * @return Response
     */
    public function index(Request $request): Response
    {
        $etats = [
            'cree' => 'Dossiers crées',
            'en_cours' => 'En cours de traitement',
            'termine' => 'Finalisés',
            'archive' => 'Archivés'
        ];
        return $this->render('_admin/dossier/index.html.twig', ['etats' => $etats, 'titre' => 'Liste des actes de vente']);
    }


    /**
     * @Route("/dossier/{etat}/liste", name="acte_vente_liste")
     * @param DossierRepository $repository
     * @return Response
     */
    public function liste(Request $request,
                          string $etat,
                          DataTableFactory $dataTableFactory,
                          DossierRepository $dossierRepository,
                          WorkflowRepository $workflowRepository
    ): Response
    {

        $table = $dataTableFactory->create();

        $user = $this->getUser();

        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;



        $totalData = $dossierRepository->countAll($etat);
        $totalFilteredData = $dossierRepository->countAll($etat, $searchValue);
        $data = $dossierRepository->getAll($etat, $limit, $offset,  $searchValue);
       //dd($data);
       


        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ])
            ->setName('dt_'.$etat);


        $table->add('numero_ouverture', TextColumn::class, ['label' => 'Numéro', 'className' => 'w-100px'])
            ->add('date_creation', DateTimeColumn::class, ['label' => 'Date de création', 'format' => 'd-m-Y'])
            ->add('objet', TextColumn::class, ['label' => 'Objet', 'className' => 'w-100px text-right'])
            ->add('type_acte_id', NumberColumn::class, ['visible' => false])
            ->add('etape', TextColumn::class, ['label' => 'Etape', 'render' => function ($value, $context) use ($workflowRepository) {
                $current = $workflowRepository->findOneBy(['typeActe' => $context['type_acte_id'], 'route' => $context['etape']]);

                if ($current) {
                    return $current->getLibelleEtape();
                }
                return 'Non Entamé';

            }]);


        $renders = [
            'edit' =>  new ActionRender(function () use ($etat) {
                return true;
            }),
            'suivi' =>  new ActionRender(function () use ($etat) {
                return true;
            }),
            'delete' => new ActionRender(function () use ($etat) {
                return $etat == 'cree';
            }),
            'archive' => new ActionRender(function () use ($etat) {
                return $etat == 'termine';
            }),
            'details' => new ActionRender(function () use ($etat) {
                return true;
            }),
        ];


        $hasActions = false;

        foreach ($renders as $_ => $cb) {
            if ($cb->execute()) {
                $hasActions = true;
                break;
            }
        }


        if ($hasActions) {
            $table->add('id', TextColumn::class, [
                'label' => 'Actions'
                , 'field' => 'id'
                , 'orderable' => false
                ,'globalSearchable' => false
                ,'className' => 'grid_row_actions'
                , 'render' => function ($value, $context) use ($renders, $etat) {

                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#extralargemodal1',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('dossierActeVente_edit', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-edit'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => $renders['edit']

                            ],
                            'suivi' => [
                                'url' => $this->generateUrl('dossier_suivi', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-book'
                                , 'attrs' => ['class' => 'btn-dark', 'title' => 'Suivi du dossier']
                                , 'render' =>$renders['suivi']

                            ],
                            /*'details' => [
                                'url' => $this->generateUrl('dossierActeVente_show', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-eye'
                                , 'attrs' => ['class' => 'btn-primary']
                                , 'render' => $renders['details']

                            ],*/
                            'delete' => [
                                'url' => $this->generateUrl('dossierActeVente_delete', ['id' => $value])
                                , 'ajax' => true
                                , 'target' => '#smallmodal'
                                , 'icon' => '%icon% fe fe-trash-2'
                                , 'attrs' => ['class' => 'btn-danger', 'title' => 'Suppression']

                                ,  'render' => $renders['delete']

                            ],
                            'archive' => [
                                'url' => $this->generateUrl('dossierActeVente_archive', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-folder'
                                , 'attrs' => ['class' => 'btn-info', 'title' => 'Archivage']

                                ,  'render' => $renders['archive']

                            ],
                        ]
                    ];
                    return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                }
            ]);
        }


        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('_admin/dossier/liste.html.twig', ['datatable' => $table, 'etat' => $etat]);
    }



     /**
     * @Route("/dossier/print", name="acte_vente_print")
     * @param DossierRepository $repository
     * @return Response
     */
    public function print(Request $request,
                          DataTableFactory $dataTableFactory,
                          DossierRepository $dossierRepository,
                          WorkflowRepository $workflowRepository
    ): Response
    {
        $dossiers = $dossierRepository->getAllEtat('acte_vente');
        return $this->renderPdf('_admin/dossier/print.html.twig', [
            'dossiers' => $dossiers,
        ], [
            'orientation' => 'P'
            , 'fontDir' => [
                $this->getParameter('font_dir').'/arial', 
                $this->getParameter('font_dir').'/trebuchet', 
            ]
        ]);
    }

    /**
     * @Route("/dossier/{id}/show", name="dossierActeVente_show", methods={"GET"})
     * @param dossier $dossier
     * @param $id
     * @param DossierRepository $repository
     * @return Response
     */
    public function show(dossier $dossier,$id,DossierRepository $repository,DossierWorkflowRepository $dossierWorkflowRepository): Response
    {
        $form = $this->createForm(DossierType::class, $dossier, [

            'method' => 'POST',
            'action' => $this->generateUrl('dossierActeVente_show', [
                'id' => $dossier->getId(),
            ])
        ]);

        return $this->render('_admin/dossier/voir.html.twig', [
            'titre'=>'Acte de vente',
            'workflow'=>$dossierWorkflowRepository->getListe($dossier->getId()),
            /* 'data'=>$repository->getFichier($id),*/
            'dossier' => $dossier,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/dossier/{id}/details", name="dossierActeVente_details", methods={"GET","POST"})
     * @param DossierWorkflowRepository $dossierWorkflowRepository
     * @param PieceRepository $pieceRepository
     * @param DocumentSigneRepository $documentSigneRepository
     * @param IdentificationRepository $identificationRepository
     * @param Request $request
     * @param Dossier $dossier
     * @param EntityManagerInterface $em
     * @param $id
     * @param DossierRepository $repository
     * @return Response
     */
    public function details(DossierRepository $repository,DossierWorkflowRepository $dossierWorkflowRepository,PieceRepository $pieceRepository,DocumentSigneRepository $documentSigneRepository, IdentificationRepository $identificationRepository,Request $request,Dossier $dossier, EntityManagerInterface $em,$id): Response
    {

        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'action' => $this->generateUrl('dossierActeVente_details', [
                'id' => $dossier->getId(),
            ])
        ]);
        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest();

        // $type = $form->getData()->getType();
        if ($form->isSubmitted()) {
            //dd($isAjax);
            $redirect = $this->generateUrl('dossierActeVente');
            $brochureFile = $form->get('documentSignes')->getData();
            $brochureFile2 = $form->get('pieces')->getData();
            $brochureFile3 = $form->get('enregistrements')->getData();
            $piecesVendeur = $form->get('pieceVendeurs')->getData();
            $redaction = $form->get('redactions')->getData();
            $remise = $form->get('remises')->getData();
            $obtention = $form->get('obtentions')->getData();
            $remiseActe = $form->get('remiseActes')->getData();
            $statut = 0;
            if ($form->isValid()) {

                $this->saveFile($brochureFile);
                $this->saveFile($piecesVendeur);
                $this->saveFile($brochureFile3);
                $this->saveFile($brochureFile2);
                $this->saveFile($redaction);
                $this->saveFile($remise);
                $this->saveFile($obtention);
                $this->saveFile($remiseActe);


                $em->persist($dossier);
                $em->flush();

                $message = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);

            }

            // if ($isAjax) {
            //return $this->json(compact('statut', 'message', 'redirect'));
            //  } else {
            if ($statut == 1) {
                return $this->redirect($redirect);
            }
            // }
        }

        return $this->render('_admin/dossier/details.html.twig', [
            'titre'=>'Acte de vente',
            'workflow'=>$dossierWorkflowRepository->getListe($dossier->getId()),
            'dossier' => $dossier,
            'form' => $form->createView(),
            'identification_nombre' =>$identificationRepository->getLength($dossier->getId()),
            'piece_nombre' =>$pieceRepository->getLength($dossier->getId()),
            'document_nombre' =>$documentSigneRepository->getLength($dossier->getId()),
        ]);
    }


    /**
     * @Route("/dossier/{id}/archive", name="dossierActeVente_archive", methods={"GET","POST"})
     */
    public function archive(Request $request,  Dossier $dossier, FormError $formError, DocumentTypeActeRepository $documentTypeActeRepository,WorkflowRepository $workflowRepository, EntityManagerInterface $em,DossierRepository $repository,TypeRepository $typeRepository): Response
    {
    }

    /**
     * @Route("/dossier/new", name="dossierActeVente_new", methods={"GET","POST"})
     * @param Request $request
     * @param DocumentTypeActeRepository $documentTypeActeRepository
     * @param WorkflowRepository $workflowRepository
     * @param EntityManagerInterface $em
     * @param DossierRepository $repository
     * @param TypeRepository $typeRepository
     * @return Response
     */
    public function new(Request $request, FormError $formError, DocumentTypeActeRepository $documentTypeActeRepository,WorkflowRepository $workflowRepository, EntityManagerInterface $em,DossierRepository $repository,TypeRepository $typeRepository): Response
    {
        $dossier = new Dossier();
        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'action' => $this->generateUrl('dossierActeVente_new')
        ]);

        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            $statut = 1;
            $acteVente = $typeRepository->findOneBy(['code'=>'acte_vente']);
            $workflows = $workflowRepository->getFichier($acteVente->getId());
            $listeDocument = $documentTypeActeRepository->getListeDocument();

            $redirect = $this->generateUrl('dossierActeVente');
            $date = (new \DateTime('now'))->format('Y-m-d');



            if ($form->isValid()) {
                $currentDate = new \DateTimeImmutable();
                
                foreach ($workflows as $workflow){
                  
                    $dossierWorkflow = new DossierWorkflow();
                    $nbre = $workflow->getNombreJours();
                   
                    $dossierWorkflow->setDossier($dossier)
                        ->setWorkflow($workflow)
                        ->setDateDebut($currentDate);

                    $lastDate = $currentDate->modify("+{$nbre} day");
                    $dossierWorkflow->setDateFin($lastDate);

                    $dossier->addDossierWorkflow($dossierWorkflow);

                    $currentDate = $lastDate;

                }

               
                $this->dossierWorkflow->getMarking($dossier);

                $dossier->setTypeActe($acteVente);
                $dossier->setEtape('');
                $em->persist($dossier);
                $em->flush();
                $data = true;
                $message = 'Opération effectuée avec succès';

                $this->addFlash('success', $message);

            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/dossier/new.html.twig', [
            'titre'=>'Acte de vente',
            'dossier' => $dossier,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/dossier/{id}/edit", name="dossierActeVente_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Dossier $dossier
     * @param EntityManagerInterface $em
     * @param $id
     * @param DossierRepository $repository
     * @return Response
     */
    public function edit(Request $request,Dossier $dossier, FormError $formError, EntityManagerInterface $em,$id,DossierRepository $repository, WorkflowRepository $workflowRepository): Response
    {

        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'action' => $this->generateUrl('dossierActeVente_edit', [
                'id' => $dossier->getId(),
            ])
        ]);
        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();
        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('dossierActeVente');


            if ($form->isValid()) {

                $currentDate = new \DateTimeImmutable();
                $currentDate->setTime(0,0);
                $acteVente = $dossier->getTypeActe();
                $workflows = $workflowRepository->getFichier($acteVente->getId());
                $dossierWorkflowRepository = $em->getRepository(DossierWorkflow::class);
                foreach ($workflows as $workflow) {
                    $nbre = $workflow->getNombreJours();
                    if (!$dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $workflow])) {
                        $dossierWorkflow = new DossierWorkflow();
                        $dossierWorkflow->setDossier($dossier);

                        $dossierWorkflow->setDateDebut($currentDate);
                        $dateFin = $currentDate->modify("+{$nbre} day");
                    } else {
                        $dt = clone $dossierWorkflow->getDateDebut();
                        $dateFin = $dt->modify("+{$nbre} day");
                    }




                    $dossierWorkflow->setWorkflow($workflow)

                        ->setDateFin($dateFin);

                    $dossierWorkflow->setWorkflow($workflow)
                        ->setDateDebut($currentDate)
                        ->setDateFin($dateFin);

                    $dossier->addDossierWorkflow($dossierWorkflow);

                }
                $em->persist($dossier);
                $em->flush();
                $data = true;
                $message = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);

            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }

            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/dossier/edit.html.twig', [
            'titre'=>'Acte de vente',
            'dossier' => $dossier,
            'form' => $form->createView(),
        ]);
    }



    


    /**
     * @Route("/dossier/{id}/delete", name="dossierActeVente_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param dossier $dossier
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em,dossier $dossier): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'dossierActeVente_delete'
                    , [
                        'id' => $dossier->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em->remove($dossier);
            $em->flush();

            $redirect = $this->generateUrl('dossierActeVente');

            $message = 'Opération effectuée avec succès';

            $response = [
                'statut'   => 1,
                'message'  => $message,
                'data' => true,
                'redirect' => $redirect,
            ];

            $this->addFlash('success', $message);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirect($redirect);
            } else {
                return $this->json($response);
            }


        }
        return $this->render('_admin/dossier/delete.html.twig', [
            'dossier' => $dossier,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/dossier/valider", name="valider", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param DossierRepository $repository
     * @return Response
     */
    public function valider(Request $request,EntityManagerInterface $entityManager,DossierRepository $repository): Response
    {
        $response = new Response();
        $etape ="";
        //  dd(($request->get('vendeur')));
        if ($request->isXmlHttpRequest()) { // pour vérifier la présence d'une requete Ajax
            // dd($request->get('id'),$request->get('etape'));
            $id = "";
            $id = $request->get('id');
            $etape = $request->get('etape');
            // dd();
            if ($id) {
                //dd($id);
                //dd("==================",$id);
                // $ensembles = $repository->listeDepartement($id);
                $dossier = $repository->find($id);
                // dd($dossier);
                if($etape == 1){
                    $dossier->setEtape("Recueil des pièces");
                }elseif ($etape == 2){
                    $dossier->setEtape("Redaction");
                }elseif ($etape == 3){
                    $dossier->setEtape("Signature");
                }elseif ($etape == 4){
                    $dossier->setEtape("Enregistrement");
                }
                elseif ($etape == 5){
                    $dossier->setEtape("Acte");
                }
                elseif ($etape == 6){
                    $dossier->setEtape("Obtention");
                }
                elseif ($etape == 7){
                    $dossier->setEtape("Remise");
                }
                elseif ($etape == 8){
                    $dossier->setEtape("Classification");
                }
                elseif ($etape == 9){
                    $dossier->setEtape("Archive");
                    $dossier->setEtat(1);
                }


                $entityManager->persist($dossier);
                $entityManager->flush();
                $data = $this->json([
                    'status'=>$etape,
                ]);

                //$data = json_encode($arrayCollection); // formater le résultat de la requête en json
                //dd($data);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);

            }

        }

        return $this->json([
            'code' => 200,
            'message' => 'ça marche bien',
            'status' => $etape,
        ], 200);
    }

    /**
     * @Route("/dossier/{id}/confirmation", name="dossierActeVente_confirmation", methods={"GET"})
     * @param $id
     * @param Dossier $parent
     * @return Response
     */
    public function confirmation($id,Dossier $parent): Response
    {
        return $this->render('_admin/modal/confirmation.html.twig',[
            'id'=>$id,
            'action'=>'dossierActeVente',
        ]);
    }


    /**
     * @Route("/dossier/{id}/active", name="dossierActeVente_active", methods={"GET"})
     * @param $id
     * @param Dossier $parent
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active($id,Dossier $parent, EntityManagerInterface $entityManager): Response
    {

        if ($parent->getActive() == 1) {

            $parent->setActive(0);

        } else {

            $parent->setActive(1);

        }

        $entityManager->persist($parent);
        $entityManager->flush();
        $redirect = $this->generateUrl('dossierActeVente');
        return $this->redirect($redirect);

    }

    /**
     * @Route("/dossier/{id}/suivi", name="dossier_suivi", methods={"GET", "POST"})
     * @param Request $request
     * @param Dossier $dossier
     * @param WorkflowRepository $workflowRepository
     * @return Response
     */
    public function suivi(Request $request, Dossier $dossier, WorkflowRepository $workflowRepository)
    {
        $typeActe = $dossier->getTypeActe();
        $etapes = $workflowRepository->findBy(['active' => 1, 'typeActe' => $typeActe], ['numeroEtape' => 'ASC']);
//dd($etapes);
        return $this->render('_admin/dossier/suivi.html.twig', [
            'dossier' => $dossier,
            'base_url' => $this->generateUrl('dossierActeVente'),
            'type_acte' => $typeActe,
            'etapes' => $etapes
        ]);
    }

    /**
     * @Route("/dossier/{id}/receuil-piece", name="acte_vente_piece", methods={"GET", "POST", "PUT"})
     *
     */
    public function piece(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository,
        FichierRepository $fichierRepository
    )
    {
        $typeActe = $dossier->getTypeActe();
        //$documents =  $documentTypeActeRepository->getDocumentsEtape($typeActe, 'piece');
        $identification = $dossier->getIdentifications()->first();

        $acheteur = $identification->getAcheteur();
        $vendeur = $identification->getVendeur();

        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);
        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        $oldPieces = $dossier->getPieces();


        $docAcheteurs = $acheteur->getDocuments();
        $docVendeurs = $vendeur->getDocuments();


        foreach ($docAcheteurs as $document) {
           
            $hasDoc = $oldPieces->filter(function (Piece $piece) use ($document) {
                return $piece->getOrigine() == Piece::ORIGINE_ACHETEUR && $piece->getLibDocument() == $document->getLibelle() && $piece->getClient();
            })->first();

           

            if (!$hasDoc) {
                $fichier = $fichierRepository->find($document->getFichier()->getId());
                $piece = new Piece();
                $piece->setDocument($document->getDocument());
                $piece->setLibDocument($document->getLibelle());
                $piece->setFichier($fichier);
                $piece->setOrigine(Piece::ORIGINE_ACHETEUR);
                $dossier->addPiece($piece);
                $piece->setClient(true);
            }
        }


        foreach ($docVendeurs as $document) {
            $hasDoc = $oldPieces->filter(function (Piece $piece) use ($document) {
                return $piece->getOrigine() == Piece::ORIGINE_VENDEUR  && 
                    $piece->getLibDocument() == $document->getLibelle() && 
                    $piece->getClient();
            })->first();

            if (!$hasDoc) {
                $fichier = $fichierRepository->find($document->getFichier()->getId());
                $piece = new Piece();
                $piece->setFichier($fichier);
                $piece->setDocument($document->getDocument());
                $piece->setLibDocument($document->getLibelle());
                $piece->setOrigine(Piece::ORIGINE_VENDEUR);
                $piece->setClient(true);
                $dossier->addPiece($piece);
            }
        }





        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());

        $filePath = 'acte_vente';
        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'etape' => strtolower(__FUNCTION__),
            'current_etape' => $dossier->getEtape(),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir($filePath, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl($currentRoute, $urlParams)
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext = $form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {
                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }

                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'].'_'.$next['route'], $urlParams),
                        'tab' =>'#'.$next['route'],
                        'current' => '#'.$routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];
                    if (!$suivi->getEtat()){
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }

                    $suivi->setEtat(true);
                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'acheteur' => $acheteur,
            'vendeur' => $vendeur,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView(),
            'current' => $current
        ]);

    }



    /**
     * @Route("/dossier/{id}/identification", name="acte_vente_identification", methods={"GET", "POST", "PUT"})
     *
     */
    public function identification(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository)
    {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        if (!$dossier->getIdentifications()->count()) {
            $identification = new Identification();
            $dossier->addIdentification($identification);
        }

        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'current_etape' => $dossier->getEtape(),
            'etape' => strtolower(__FUNCTION__),
            'validation_groups' => ['Default', $routeWithoutPrefix],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext =$form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {
                if ($this->dossierWorkflow->can($dossier, 'post_creation')) {
                    $this->dossierWorkflow->apply($dossier, 'post_creation');
                }

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(array('dossier' => $dossier, 'workflow' => $current));
//dd($dossierWorkflow);
                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'].'_'.$next['route'], $urlParams),
                        'tab' =>'#'.$next['route'],
                        'current' => '#'.$routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];

                    if (!$suivi->getEtat()){
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }
                    $suivi->setEtat(true);

                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView(),
            'current' => $current
        ]);
    }

    /**
     * @Route("/dossier/{id}/redaction", name="acte_vente_redaction", methods={"GET", "POST"})
     *
     */
    public function redaction(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    )
    {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        if (!$dossier->getRedactions()->count()) {
            $redaction = new Redaction();
            $redaction->setNumVersion(1);
            $dossier->addRedaction($redaction);
        }

        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'etape' => strtolower(__FUNCTION__),
            'current_etape' => $dossier->getEtape(),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext =$form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'].'_'.$next['route'], $urlParams),
                        'tab' =>'#'.$next['route'],
                        'current' => '#'.$routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];


                    if (!$suivi->getEtat()){
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }
                    $suivi->setEtat(true);

                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView(),
            'current' => $current
        ]);
    }

    /**
     * @Route("/dossier/{id}/classification", name="acte_vente_classification", methods={"GET", "POST"})
     *
     */
    public function classification(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    )
    {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        if (!$dossier->getInfoClassification()) {
            $classification = new InfoClassification();
            $dossier->setInfoClassification($classification);
        }

        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'etape' => strtolower(__FUNCTION__),
            'current_etape' => current(array_keys($dossier->getEtat())),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();

        $data = null;

        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isDone = $form->get('cloture')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                $redirect = $this->generateUrl($currentRoute, $urlParams);
                $modal = $isDone;

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }

                if ($isDone) {
                    if ($this->dossierWorkflow->can($dossier, 'cloture')) {
                        $this->dossierWorkflow->apply($dossier, 'cloture');
                    }
                    if (!$suivi->getEtat()){
                        $suivi->setDateFin(new \DateTime());
                    }
                    $suivi->setEtat(true);
                    $data = true;
                }
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();


                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView(),
            'current' => $current
        ]);
    }

    /**
     * @Route("/dossier/{id}/signature-acte", name="acte_vente_signature", methods={"GET", "POST"})
     * @param Request $request
     * @param Dossier $dossier
     * @param EntityManagerInterface $em
     * @param FormError $formError
     * @param WorkflowRepository $workflowRepository
     * @param DossierWorkflowRepository $dossierWorkflowRepository
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function signature(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    )
    {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);



        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'current_etape' => $dossier->getEtape(),
            'etape' => strtolower(__FUNCTION__),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext =$form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'].'_'.$next['route'], $urlParams),
                        'tab' =>'#'.$next['route'],
                        'current' => '#'.$routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];

                    if (!$suivi->getEtat()){
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }
                    $suivi->setEtat(true);

                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView(),
            'current' => $current
        ]);
    }

    /**
     * @Route("/dossier/{id}/enregistrement-acte", name="acte_vente_enregistrement", methods={"GET", "POST"})
     *
     */
    public function enregistrement(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    )
    {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        $oldEnregistrements = $dossier->getEnregistrements();

        foreach (Enregistrement::SENS as $idSens => $value) {
            $hasValue = $oldEnregistrements->filter(function (Enregistrement $enregistrement) use ($idSens) {
                return $enregistrement->getSens() == $idSens;
            })->current();

            if (!$hasValue) {
                $enregistrement = new Enregistrement();
                $enregistrement->setSens(intval($idSens));
                $dossier->addEnregistrement($enregistrement);
            }
        }


        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'current_etape' => $dossier->getEtape(),
            'etape' => strtolower(__FUNCTION__),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext =$form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'].'_'.$next['route'], $urlParams),
                        'tab' =>'#'.$next['route'],
                        'current' => '#'.$routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];


                    if (!$suivi->getEtat()){
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }
                    $suivi->setEtat(true);

                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView(),
            'current' => $current
        ]);
    }

    //======================
    /**
     * @Route("/dossier/{id}/verification-acte", name="acte_vente_verification", methods={"GET", "POST"})
     *
     */
    public function verification(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    )
    {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        //$oldEnregistrements = $dossier->getVerifications();



        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'current_etape' => $dossier->getEtape(),
            'etape' => strtolower(__FUNCTION__),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext =$form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'].'_'.$next['route'], $urlParams),
                        'tab' =>'#'.$next['route'],
                        'current' => '#'.$routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];


                    if (!$suivi->getEtat()){
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }
                    $suivi->setEtat(true);

                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView(),
            'current' => $current
        ]);
    }

    /**
     * @Route("/dossier/{id}/titre-propriete", name="acte_vente_remise", methods={"GET", "POST"})
     *
     */
    public function remise(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    )
    {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        if (!$dossier->getRemises()->count()) {
            $remise = new Remise();
            $dossier->addRemise($remise);
        }

        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'current_etape' => $dossier->getEtape(),
            'etape' => strtolower(__FUNCTION__),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext =$form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'].'_'.$next['route'], $urlParams),
                        'tab' =>'#'.$next['route'],
                        'current' => '#'.$routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];

                    if (!$suivi->getEtat()){
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }
                    $suivi->setEtat(true);

                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView(),
            'current' => $current
        ]);
    }


    /**
     * @Route("/dossier/{id}/obtention", name="acte_vente_obtention", methods={"GET", "POST"})
     *
     */
    public function obtention(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    )
    {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        if (!$dossier->getObtentions()->count()) {
            $obtention = new Obtention();
            $dossier->addObtention($obtention);
        }



        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'etape' => strtolower(__FUNCTION__),
            'current_etape' => $dossier->getEtape(),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext =$form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'].'_'.$next['route'], $urlParams),
                        'tab' =>'#'.$next['route'],
                        'current' => '#'.$routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];

                    if (!$suivi->getEtat()){
                        $dossier->setEtape($next['route']);
                        $suivi->setDateFin(new \DateTime());
                    }
                    $suivi->setEtat(true);

                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView(),
            'current' => $current
        ]);
    }



    /**
     * @Route("/dossier/{id}/remise-acte", name="acte_vente_remise_acte", methods={"GET", "POST"})
     */
    public function remiseActe(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    )
    {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        if (!$dossier->getRemiseActes()->count()) {
            $remise = new RemiseActe();
            $dossier->addRemiseActe($remise);
        }


        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'etape' => strtolower(snake_case(__FUNCTION__)),
            'current_etape' => $dossier->getEtape(),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext =$form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'].'_'.$next['route'], $urlParams),
                        'tab' =>'#'.$next['route'],
                        'current' => '#'.$routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];

                    if (!$suivi->getEtat()){
                        $dossier->setEtape($next['route']);
                        $suivi->setDateFin(new \DateTime());
                    }
                    $suivi->setEtat(true);

                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView(),
            'current' => $current
        ]);
    }

    /**
     *
     * @Route("/dossier/liste", name="dossier_liste", methods={"GET", "POST"})
     * @param Request $request
     * @param DossierRepository $repository
     * @return Response
     */
    public function listeDossier(Request $request,DossierRepository $repository)
    {
        $query = $request->query->get('q');
        //dd("fff");
        $affaire = $repository->search($query);
        $total = count($affaire);

        $items = ['total'=>$total,'items'=>$affaire];

        $data = $this->get('serializer')->serialize($items,'json');

        $response = new Response();

        $response->headers->set('Content-type','application/json');

        return $response;
    }

    /**
     * @Route("/dossier/addFile", name="dossier_addFile_new", methods={"GET","POST"})
     * @param Request $request
     * @param ClientRepository $clientRepository
     * @param DocumentTypeActeRepository $documentTypeActeRepository
     * @param WorkflowRepository $workflowRepository
     * @param TypeRepository $typeRepository
     * @param DossierRepository $dossierRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function addFile(Request $request,ClientRepository $clientRepository,DocumentTypeActeRepository $documentTypeActeRepository,WorkflowRepository $workflowRepository,TypeRepository $typeRepository,DossierRepository $dossierRepository, EntityManagerInterface $entityManager)
    {
        $dossier = new UploadFile();
        $form = $this->createForm(UploadFileType::class,$dossier, [
            'method' => 'POST',
            'action' => $this->generateUrl('dossier_addFile_new')
        ]);
        $form->handleRequest($request);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl('dossierActeVente');

            //

            if ($form->isValid()) {

                $file = $form->get("upload_file")->getData(); // get the file from the sent request


                $fileFolder = $this->getParameter('kernel.project_dir') . '/public/uploads/';  //choose the folder in which the uploaded file will be stored

                //dd($fileFolder);
                $filePathName = md5(uniqid()) . $file->getClientOriginalName();

                try {
                    $file->move($fileFolder, $filePathName);
                } catch (FileException $e) {
                    dd($e);
                }

                $spreadsheet = IOFactory::load($fileFolder . $filePathName); // Here we are able to read from the excel file

                $row = $spreadsheet->getActiveSheet()->removeRow(1); // I added this to be able to remove the first file line
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true); // here, the read data is turned into an array

               $i=0;
                foreach ($sheetData as $Row)
                {
                  $i =$i+1;

                    $numero = 'AV'.date('y').'-'.$i;
                    $nc = $Row['A'];     // store the first_name on each iteration
                    $no = $Row['B'];     // store the first_name on each iteration
                    $affaire = $Row['C'];     // store the first_name on each iteration
                    $comparant1 = $Row['D'];     // store the first_name on each iteration
                    $comparant2 = $Row['E'];     // store the first_name on each iteration
                    $repertoire = $Row['F'];     // store the first_name on each iteration
                    $observations = $Row['G'];     // store the first_name on each iteration
                    $renvoi = $Row['H'];     // store the first_name on each iteration
                    $piece = $Row['I'];     // store the first_name on each iteration
                    //$desig = $Row['B'];   // store the last_name on each iteration




                  // $type_existant = $typeRepository->findOneBy(array('titre'=>$affaire));


                    // make sure that the user does not already exists in your db
//                   if (!$client_existant)
//                    {

                        $dossier = new Dossier();
                        $dossier->setNumeroC($nc);
                        $dossier->setActive(1);
                        $dossier->setEtape("classification");
                        $dossier->setEtat(["termine"=>1]);
                        $dossier->setDateCreation(new \DateTime());
                        $dossier->setNumeroOuverture($numero);
                        $dossier->setNumeroO($no);
                        $dossier->setTypeActe($typeRepository->findOneBy(array('titre'=>$affaire)));
                        $dossier->setComparant1($comparant1);
                        $dossier->setComparant2($comparant2);
                        $dossier->setRepertoire($repertoire);
                        $dossier->setObjet($observations);
                        $dossier->setDescription($observations);
                        $dossier->setRenvoi($renvoi);
                        $dossier->setPiece($piece);
                    $currentDate = new \DateTime();
                        if($affaire == "Vente"){

                            $acteVente = $dossier->getTypeActe();
                            $workflows = $workflowRepository->getFichier(1);
                            $dossierWorkflowRepository = $this->em->getRepository(DossierWorkflow::class);
                            foreach ($workflows as $workflow) {
                                $nbre = $workflow->getNombreJours();
                                if (!$dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $workflow])) {
                                    $dossierWorkflow = new DossierWorkflow();
                                    $dossierWorkflow->setDossier($dossier);

                                    $dossierWorkflow->setDateDebut($currentDate);
                                    $dateFin = $currentDate->modify("+{$nbre} day");
                                } else {
                                    $dt = clone $dossierWorkflow->getDateDebut();
                                    $dateFin = $dt->modify("+{$nbre} day");
                                }

                                $dossierWorkflow->setWorkflow($workflow)

                                    ->setDateFin($dateFin);

                                $dossierWorkflow->setWorkflow($workflow)
                                    ->setDateDebut($currentDate)
                                    ->setDateFin($dateFin);

                                $dossier->addDossierWorkflow($dossierWorkflow);

                            }

                            $archive = new Archive();
                            $archive->setNumeroClassification('Class'.$i)
                                    ->setAcheteur($clientRepository->findOneBy(array('raisonSocial'=>'SGCI')))
                                    ->setDateClassification(new \DateTime())
                                    ->setDateCreation(new \DateTime())
                                    ->setDateOuverture(new \DateTime())
                                    ->setDescription($observations)
                                    ->setNumeroOuverture('Arch'.$i)
                                    ->setObjet($observations)
                                    ->setTypeActe($typeRepository->findOneBy(array('titre'=>$affaire)))
                                    ->setVendeur($clientRepository->findOneBy(array('raisonSocial'=>'SGCI')));
                            $entityManager->persist($archive);
                            $entityManager->flush();
                            $classification = new InfoClassification();
                            $classification->setNumero("Classe".$i);
                            $classification->setDate(new \DateTime());
                            $dossier->setInfoClassification($classification);
                            $this->dossierWorkflow->getMarking($dossier);
                        }




                    $entityManager->persist($dossier);
                        $entityManager->flush();
                        // here Doctrine checks all the fields of all fetched data and make a transaction to the database.
//                    }else{
//
//                        $client_existant->setObjet($nom);
//                        $entityManager->persist($client_existant);
//                        $entityManager->flush();
//                    }
                }

                $data = true;
                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }

        }
        return $this->renderForm('parametre/uploadFile/upload_file_new.html.twig', [
            'form' => $form,
        ]);
    }
}