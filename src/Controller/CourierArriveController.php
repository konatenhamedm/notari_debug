<?php

namespace App\Controller;

use App\Entity\CourierArrive;
use App\Entity\Fichier;
use App\Form\CourierArriveType;
use App\Repository\CourierArriveRepository;
use App\Repository\DocumentCourrierRepository;
use App\Service\ActionRender;
use App\Service\FormError;
use App\Service\Omines\Adapter\ArrayAdapter;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Mpdf\MpdfException;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin")
 */
class CourierArriveController extends AbstractController
{
    use FileTrait;

    private const UPLOAD_PATH = 'courrier';

    /**
     * @Route("/courrier-arrive", name="courierArrive")
     * @param Request $request
     * @param DataTableFactory $dataTableFactory
     * @param CourierArriveRepository $courierArriveRepository
     * @return Response
     */
    public function index(Request $request,
                          DataTableFactory $dataTableFactory,
                          CourierArriveRepository $courierArriveRepository): Response
    {

        $table = $dataTableFactory->create();

        $user = $this->getUser();

        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;



        $totalData = $courierArriveRepository->countAll();
        $totalFilteredData = $courierArriveRepository->countAll($searchValue);
        $data = $courierArriveRepository->getAll($limit, $offset,  $searchValue);

//dd($data);


        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ]) ->setName('dt_');
        ;


        $table->add('numero', TextColumn::class, ['label' => 'Numéro', 'className' => 'w-100px'])
            ->add('date_reception', DateTimeColumn::class, ['label' => 'Date de réception', 'format' => 'd-m-Y'])
            ->add('objet', TextColumn::class, ['label' => 'Objet', 'className' => 'w-100px'])
            ->add('expediteur', TextColumn::class, ['label' => 'Expediteur', 'className' => 'w-100px'])
        ;


        $renders = [
            'edit' =>  new ActionRender(function () {
                return true;
            }),
            'delete' => new ActionRender(function (){
                return true;
            }),
            'details' => new ActionRender(function () {
                return true;
            }),
            'archive' => new ActionRender(function () {
                return true;
            }),
            'imprimer' => new ActionRender(function () {
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
                , 'render' => function ($value, $context) use ($renders) {

                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#extralargemodal1',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('courierArrive_edit', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-edit'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['edit'];
                                })
                            ],
                            /*'details' => [
                                'url' => $this->generateUrl('courierArrive_show', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-eye'
                                , 'attrs' => ['class' => 'btn-primary']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['details'];
                                })
                            ],*/
                            'archive' => [
                                'url' => $this->generateUrl('courierArrive_archive', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-folder'
                                , 'attrs' => ['class' => 'btn-info', 'title' => 'Archivage']

                                ,  'render' => $renders['archive']

                            ],
                            'delete' => [
                                'url' => $this->generateUrl('courierArrive_delete', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-trash-2'
                                , 'target' => '#smallmodal'
                                , 'attrs' => ['class' => 'btn-danger', 'title' => 'Suppression']

                                ,  'render' => new ActionRender(function () use ($renders) {
                                    return $renders['delete'];
                                })
                            ],
                            /*'imprimer' => [
                                'url' => $this->generateUrl('fiche_liste', ['id' => $value])
                                , 'ajax' => false
                                , 'target' => '_blank'
                                , 'icon' => '%icon% fe fe-download'
                                , 'attrs' => ['class' => 'btn-info', 'title' => 'Imprimer document','target' => '_blank']
                                , 'render' =>$renders['imprimer']

                            ],*/

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

        return $this->render('_admin/arrive/index.html.twig', ['datatable' => $table, 'titre' => 'Liste des courriers arrivés']);
    }



    /**
     * @Route("/archive/{id}/arrive", name="courierArrive_archive", methods={"GET"})
     * @param $id
     * @param DocumentCourrierRepository $repository
     * @return Response
     */
    public  function  archive($id, DocumentCourrierRepository $repository){


        return $this->render('_admin/arrive/archive.html.twig', [
            'titre'=>'Arrive',
            'data'=>$repository->getFichier($id),

        ]);
    }

    /**
     * @Route("/courier/{id}/show", name="courierArrive_show", methods={"GET"})
     * @param CourierArrive $courierArrive
     * @return Response
     */
    public function show(CourierArrive $courierArrive,$id,CourierArriveRepository $repository): Response
    {
        //$type = $courierArrive->getType();

        $form = $this->createForm(CourierArriveType::class, $courierArrive, [

            'method' => 'POST',
            'action' => $this->generateUrl('courierArrive_show', [
                'id' => $courierArrive->getId(),
            ])
        ]);

        return $this->render('_admin/arrive/voir.html.twig', [
            'titre'=>'ARRIVE',
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courier/new", name="courierArrive_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UploaderHelper $uploaderHelper
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $em,FormError $formError, UploaderHelper $uploaderHelper,CourierArriveRepository $repository): Response
    {


        $courierArrive = new CourierArrive();
        $courierArrive->setNumero($repository->getNumeroIncrementation('ARRIVE'));
        $form = $this->createForm(CourierArriveType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('courierArrive_new')
        ]);


        $form->handleRequest($request);
       $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            $statut = 1;
            $redirect = $this->generateUrl('courierArrive');

            if ($form->isValid()) {

                $courierArrive->setEtat("cree");
                $courierArrive->setType('ARRIVE');
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

        return $this->render('_admin/arrive/new.html.twig', [
            'titre'=>'ARRIVE',
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courier/{id}/edit", name="courierArrive_edit", methods={"GET","POST"})
     * @param Request $request
     * @param CourierArrive $courierArrive
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Request $request, CourierArrive $courierArrive,FormError $formError, EntityManagerInterface $em,$id,CourierArriveRepository $repository): Response
    {


        $form = $this->createForm(CourierArriveType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('courierArrive_edit', [
                'id' => $courierArrive->getId(),
            ])
        ]);
        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();
       // $type = $form->getData()->getType();
        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('courierArrive');


            if ($form->isValid()) {

                $em->persist($courierArrive);
                $em->flush();

                $data = true;
                $message = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
              /*  foreach ($courierArrive->getDocuments() as $document) {
                    $files[$document->getDocHash()] = $this->generateUrl('fichier_index', ['id' => $document->getFichier()->getId()]);
                }*/
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

        return $this->render('_admin/arrive/edit.html.twig', [
            'titre'=>'ARRIVE',
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courrier/accuse/{id}", name="courierArrive_accuse_edit", methods={"GET","POST"})
     * @param Request $request
     * @param CourierArrive $courierArrive
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function accuse(Request $request, CourierArrive $courierArrive, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CourierArriveType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('courierArrive_edit', [
                'id' => $courierArrive->getId(),
            ])
        ]);
        $form->handleRequest($request);
        $data = null;

        $isAjax = $request->isXmlHttpRequest();
      //  $type = $form->getData()->getType();

        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('courierArrive');
            $finalise = $form->get('finalise')->getData();

            if ($form->isValid()) {

               if ($finalise == true){
                   $courierArrive->setEtat('termine');
               }
                $em->persist($courierArrive);
                $em->flush();

                $message = 'Opération effectuée avec succès';
                $data = true;
                $statut = 1;
                $this->addFlash('success', $message);

            }

            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect','data'));
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
     * @Route("/courierArrive/delete/{id}", name="courierArrive_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param courierArrive $courierArrive
     * @return Response
     */
    public function delete($id,Request $request, EntityManagerInterface $em, CourierArrive $courierArrive): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'courierArrive_delete'
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
        return $this->render('_admin/arrive/delete.html.twig', [
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/courierArrive/{id}/active", name="courierArrive_active", methods={"GET"})
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
     * @Route("/existe", name="exsite", methods={"GET","POST"})
     * @param CourierArriveRepository $repository
     * @param Request $request
     * @return Response
     */
    public function existe(CourierArriveRepository $repository,Request $request): Response
    {
        $response = new Response();
        $format="";
        $nombre = $repository->getNombre();
        $date = date('y');
        $format = $date.'-'.$nombre.' '.'A';

        if ($request->isXmlHttpRequest()) {

            $arrayCollection[] = array(
                'nom' =>  $format,

                // ... Same for each property you want
            );
            $data = json_encode($arrayCollection); // formater le résultat de la requête en json
            //dd($data);
            $response->headers->set('Content-type', 'application/json');
            $response->setContent($data);
        }
        return $this->json([
            'code' => 200,
            'message' => 'ça marche bien',
            'nom' => $format,
        ], 200);

    }

    /**
     * @Route("/modal/imprime/{type}", name="imprime", methods={"GET","POST"})
     */
    public function  imprime($type): Response
    {
        return $this->renderForm("_admin/arrive/modal_view.html.twig",[
           'type'=>$type
        ]);
    }

    /**
     * @Route("/fiche/{type}", name="fiche_liste", methods={"GET"})
     * @param Request $request
     * @param CourierArriveRepository $repository
     * @throws MpdfException
     */
    public function imprimer($type,Request $request, CourierArriveRepository $repository)
    {
       // dd($type);

        $html = $this->renderView('_admin/arrive/imprimer.html.twig', [
            'liste' => $repository->findBy(array('type'=>$type)),
            'type'=>$type
        ]);


        //}
        $mpdf = new \Mpdf\Mpdf([

            'mode' => 'utf-8', 'format' => 'A4'
        ]);
        $mpdf->PageNumSubstitutions[] = [
            'from' => 1,
            'reset' => 0,
            'type' => 'I',
            'suppress' => 'on'
        ];

        $mpdf->WriteHTML($html);
        $mpdf->SetFontSize(6);
        $mpdf->Output();


    }
}
  