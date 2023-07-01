<?php

namespace App\Controller;

use App\Entity\CourierArrive;
use App\Form\CourierArriveType;
use App\Form\CourierDepartAccuseType;
use App\Form\CourierDepartArchiveType;
use App\Form\CourierDepartType;
use App\Form\CourierInterneType;
use App\Repository\CourierArriveRepository;
use App\Repository\DocumentCourrierRepository;
use App\Repository\DocumentReceptionRepository;
use App\Service\ActionRender;
use App\Service\FormError;
use App\Service\Omines\Adapter\ArrayAdapter;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin")
 */
class CourierInterneController extends AbstractController
{
    use FileTrait;
    private const UPLOAD_PATH = 'courrier';

    /**
     * @Route("/courier-interne", name="courierInterne")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $etats = [
            'cree' => 'En cours de traitement',
            'termine' => 'Finalisés',
        ];
        return $this->render('_admin/interne/index.html.twig', ['etats' => $etats, 'titre' => 'Liste des courriers internes']);
    }

    /**
     * @Route("/interne/{etat}/liste", name="interne_liste")
     * @param Request $request
     * @param string $finalise
     * @param DataTableFactory $dataTableFactory
     * @param CourierArriveRepository $courierArriveRepository
     * @return Response
     */
    public function liste(Request $request,
                          string $etat,
                          DataTableFactory $dataTableFactory,
                          CourierArriveRepository $courierArriveRepository
    ): Response
    {

        $table = $dataTableFactory->create();

        $user = $this->getUser();

        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;



        $totalData = $courierArriveRepository->countAllInterne($etat);
        $totalFilteredData = $courierArriveRepository->countAllInterne($etat, $searchValue);
        $data = $courierArriveRepository->getAllInterne($etat, $limit, $offset,  $searchValue);
        // dd($data);



        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ])
            ->setName('dt_'.$etat);


        $table->add('numero', TextColumn::class, ['label' => 'Numéro', 'className' => 'w-100px'])
            ->add('date_envoi', DateTimeColumn::class, ['label' => 'Date d\'envoi', 'format' => 'd-m-Y'])
            ->add('objet', TextColumn::class, ['label' => 'Objet', 'className' => 'w-100px'])
            ->add('fullname', TextColumn::class, ['label' => 'Destinataire', 'className' => 'w-100px'])
        ;



        $renders = [
            'edit' =>  new ActionRender(function () use ($etat) {
                return $etat == 'cree';
            }),
            'all_archive' =>  new ActionRender(function () use ($etat) {
                return $etat == 'termine';
            }),
            'delete' => new ActionRender(function () use ($etat) {
                return true;
            }),
            'accuse' => new ActionRender(function () use ($etat) {
                return $etat == 'cree';
            }),
            'word' => new ActionRender(function () use ($etat) {
                return $etat == 'cree';
            }),
            'archive' => new ActionRender(function () use ($etat) {
                return $etat == 'cree';
            }),
            'details' => new ActionRender(function () use ($etat) {
                return $etat == 'termine';
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
                                'url' => $this->generateUrl('courierInterne_edit', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-edit'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => $renders['edit']

                            ],

                            'details' => [
                                'url' => $this->generateUrl('courierInterne_show', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-eye'
                                , 'attrs' => ['class' => 'btn-primary']
                                , 'render' => $renders['details']

                            ],
                            'delete' => [
                                'url' => $this->generateUrl('courierArrive_delete', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-trash-2'
                                , 'target' => '#smallmodal'
                                , 'attrs' => ['class' => 'btn-danger', 'title' => 'Suppression']

                                ,  'render' => $renders['delete']

                            ],
                            'all_archive' => [
                                'url' => $this->generateUrl('courierDepart_all_archive', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-folder'
                                , 'attrs' => ['class' => 'btn-dark', 'title' => 'Liste des documents']
                                , 'render' =>$renders['all_archive']

                            ],
                            'archive' => [
                                'url' => $this->generateUrl('courierInterne_archive', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-folder'
                                , 'attrs' => ['class' => 'btn-info', 'title' => 'Archivage']

                                ,  'render' => $renders['archive']

                            ],
                            'accuse' => [
                                'url' => $this->generateUrl('courierInterne_accuse_edit', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fa fa-mail-reply'
                                , 'attrs' => ['class' => 'btn-info', 'title' => 'Accusé de reception']

                                ,  'render' => $renders['accuse']

                            ],
                            'word' => [
                                'url' => $this->generateUrl('word', ['id' => $value])
                                , 'ajax' => false
                                , 'icon' => '%icon% fa fa-file'
                                , 'attrs' => ['class' => 'btn-info', 'title' => 'Génerer le document word']

                                ,  'render' => $renders['word']

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

        return $this->render('_admin/interne/liste.html.twig', ['datatable' => $table, 'etat' => $etat]);
    }


    /**
     * @Route("/accuse/{id}/interne/edit", name="courierInterne_accuse_edit", methods={"GET","POST"})
     * @param Request $request
     * @param CourierArrive $courierArrive
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function accuser($id,Request $request,FormError $formError, CourierArrive $courierArrive, EntityManagerInterface $em,CourierArriveRepository $repository): Response
    {

        $form = $this->createForm(CourierDepartAccuseType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('courierDepart_accuse_edit', [
                'id' => $courierArrive->getId(),
            ])
        ]);

        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('courierDepart');
//dd($form->get('finalise')->getData());
            if ($form->isValid()) {
                if ($form->get('finalise')->getData() == true){
                    $courierArrive->setEtat('termine');
                }
                $em->persist($courierArrive);
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

        return $this->render('_admin/arrive/accuse.html.twig', [
            'titre'=>'INTERNE',
            'data'=>$repository->getFichier($id),
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/archive/{id}/interne", name="courierInterne_archive", methods={"GET","POST"})
     * @param $id
     * @param Request $request
     * @param FormError $formError
     * @param CourierArrive $courierArrive
     * @param DocumentCourrierRepository $repository
     * @param DocumentReceptionRepository $documentReceptionRepository
     * @return Response
     */
    public  function  archive($id,Request $request,FormError $formError, CourierArrive $courierArrive,CourierArriveRepository $courierArriveRepository,  DocumentCourrierRepository $repository,DocumentReceptionRepository $documentReceptionRepository)
    {
//dd($repository->getFichier($id),$id);

        $form = $this->createForm(CourierDepartArchiveType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('courierDepart_archive', [
                'id' => $courierArrive->getId(),
            ])
        ]);

        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            //  dd($form);
            $redirect = $this->generateUrl('courierDepart');

            if ($form->isValid()) {
                $courierArriveRepository->add($courierArrive,true);
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

        return $this->render('_admin/depart/archive.html.twig', [
            'titre'=>'INTERNE',
            'data'=>$repository->getFichier($id),
            'dataAccuseReception'=>$documentReceptionRepository->getFichier($id),
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/courrier-interne/{id}/show", name="courierInterne_show", methods={"GET"})
     * @param CourierArrive $courierArrive
     * @return Response
     */
    public function show(CourierArrive $courierArrive,$id,CourierArriveRepository $repository): Response
    {
        $type = $courierArrive->getType();

        $form = $this->createForm(CourierArriveType::class, $courierArrive, [

            'method' => 'POST',
            'action' => $this->generateUrl('courierInterne_show', [
                'id' => $courierArrive->getId(),
            ])
        ]);

        return $this->render('_admin/interne/voir.html.twig', [
            'titre'=>'INTERNE',
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courier-interne/{id}/accuse", name="courierInterne_recep", methods={"GET","POST"})
     * @param Request $request
     * @param CourierArrive $courierArrive
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function accuse(Request $request, CourierArrive $courierArrive,FormError $formError, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CourierArriveType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('courierInterne_recep', [
                    'id' => $courierArrive->getId(),
                ]
            )
        ]);

        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();
        //   $type = $form->getData()->getType();
        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('courierInterne');

            if ($form->isValid()) {

                $em->persist($courierArrive);
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

        return $this->render('_admin/arrive/accuse.html.twig', [
            'titre'=>"ACCUSE DE RECEPTION",
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/courrier-interne/new", name="courierInterne_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UploaderHelper $uploaderHelper
     * @param CourierArriveRepository $repository
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $em,FormError $formError, UploaderHelper $uploaderHelper,CourierArriveRepository $repository): Response
    {


        $courierArrive = new CourierArrive();

        $courierArrive->setExpediteur("KASSY ETUDER");
        $courierArrive->setNumero($repository->getNumeroIncrementation('INTERNE'));
        $form = $this->createForm(CourierInterneType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('courierInterne_new')
        ]);


        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();
      // $type = $form->getData()->getType();
        if ($form->isSubmitted()) {
            $statut = 1;
            $redirect = $this->generateUrl('courierInterne');

        //    dd($brochureFile);
            if ($form->isValid()) {

                $courierArrive->setEtat("cree");
                $courierArrive->setType('INTERNE');
                $courierArrive->setCategorie('COURRIER');
                $courierArrive->setActive(1);
                $em->persist($courierArrive);
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

        return $this->render('_admin/interne/new.html.twig', [
            'titre'=>'INTERNE',
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courrier-interne/{id}/edit", name="courierInterne_edit", methods={"GET","POST"})
     * @param Request $request
     * @param CourierArrive $courierArrive
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Request $request,FormError $formError, CourierArrive $courierArrive, EntityManagerInterface $em,$id,CourierArriveRepository $repository): Response
    {

        $form = $this->createForm(CourierDepartType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('courierInterne_edit', [
                'id' => $courierArrive->getId(),
            ])
        ]);
        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();
        //$type = $form->getData()->getType();
        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('courierInterne');


            if ($form->isValid()) {

                $em->persist($courierArrive);
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

        return $this->render('_admin/interne/edit.html.twig', [
            'titre'=>'INTERNE',
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courrier-interne/delete/{id}", name="courierInterne_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param courierArrive $courierArrive
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em, CourierArrive $courierArrive): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'courierInterne_delete'
                    , [
                        'id' => $courierArrive->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->remove($courierArrive);
            $em->flush();

            $redirect = $this->generateUrl('courierInterne');

            $message = 'Opération effectuée avec succès';

            $response = [
                'statut' => 1,
                'message' => $message,
                'redirect' => $redirect,
            ];

            $this->addFlash('success', $message);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirect($redirect);
            } else {
                return $this->json($response);
            }


        }
        return $this->render('_admin/interne/delete.html.twig', [
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courrier-interne/{id}/active", name="courierInterne_active", methods={"GET"})
     * @param $id
     * @param CourierArrive $parent
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active($id, CourierArrive $parent, EntityManagerInterface $entityManager): Response
    {

        if ($parent->getActive() == 1) {

            $parent->setActive(0);

        } else {

            $parent->setActive(1);

        }

        $entityManager->persist($parent);
        $entityManager->flush();
        return $this->json([
            'code' => 200,
            'message' => 'ça marche bien',
            'active' => $parent->getActive(),
        ], 200);

    }
    /**
     * @Route("/existe_interne", name="exsite_interne", methods={"GET","POST"})
     * @param CourierArriveRepository $repository
     * @param Request $request
     * @return Response
     */
    public function existeInterne(CourierArriveRepository $repository,Request $request): Response
    {
        $response = new Response();
        $format="";
        if ($request->isXmlHttpRequest()) {
            $nombre = $repository->getNombre();

            $date = date('y');
            $format = $date.'-'.$nombre.' '.'I';


            $arrayCollection[] = array(
                'nom' =>  $format,

                // ... Same for each property you want
            );
            $data = json_encode($arrayCollection); // formater le résultat de la requête en json
            //dd($data);
            $response->headers->set('Content-TypeActe', 'application/json');
            $response->setContent($data);
        }
        return $this->json([
            'code' => 200,
            'message' => 'ça marche bien',
            'nom' => $format,
        ], 200);

    }

}
  