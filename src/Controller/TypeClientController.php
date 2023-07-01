<?php

namespace App\Controller;

use App\Service\Services;
use App\Entity\TypeClient;
use App\Service\FormError;
use App\Entity\TypeSociete;
use App\Form\TypeClientType;
use App\Service\ActionRender;
use App\Repository\TypeRepository;
use App\Service\PaginationService;
use App\Repository\DossierRepository;
use App\Repository\WorkflowRepository;
use App\Repository\TypeClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Omines\Adapter\ArrayAdapter;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin")
 * il s'agit du typeClient des module
 */
class TypeClientController extends AbstractController
{
    /**
     * @Route("/typeClient/{id}/confirmation", name="typeClient_confirmation", methods={"GET"})
     * @param $id
     * @param TypeClient $parent
     * @return Response
     */
    public function confirmation($id,TypeClient $parent): Response
    {
        return $this->render('_admin/modal/confirmation.html.twig',[
            'id'=>$id,
            'action'=>'typeClient',
        ]);
    }

    /**
     * @Route("/typeClient", name="typeClient")
     * @param TypeRepository $repository
     * @return Response
     */
    public function index(Request $request,
                          DataTableFactory $dataTableFactory,
                          TypeClientRepository $clientRepository): Response
    {

        $table = $dataTableFactory->create();

        $user = $this->getUser();

        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;



        $totalData = $clientRepository->countAll();
        $totalFilteredData = $clientRepository->countAll($searchValue);
        $data = $clientRepository->getAll($limit, $offset,  $searchValue);

//dd($data);


        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ]) ->setName('dt_');
        ;


        $table->add('titre', TextColumn::class, ['label' => 'Titre', 'className' => 'w-100px']);




        $renders = [
            'edit' =>  new ActionRender(function () {
                return true;
            }),
            /*    'suivi' =>  new ActionRender(function () use ($etat) {
                    return in_array($etat, ['cree']);
                }),*/
            'delete' => new ActionRender(function (){
                return true;
            }),
            'details' => new ActionRender(function () {
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
                                'url' => $this->generateUrl('typeClient_edit', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-edit'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['edit'];
                                })
                            ],
                            'details' => [
                                'url' => $this->generateUrl('typeClient_show', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-eye'
                                , 'attrs' => ['class' => 'btn-primary']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['details'];
                                })
                            ],
                            'delete' => [
                                'url' => $this->generateUrl('typeClient_delete', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-trash-2'
                                , 'attrs' => ['class' => 'btn-danger', 'title' => 'Suppression']
                                , 'target' => '#smallmodal'
                                ,  'render' => new ActionRender(function () use ($renders) {
                                    return $renders['delete'];
                                })
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

        return $this->render('_admin/typeClient/index.html.twig', ['datatable' => $table, 'titre' => 'Liste des types client']);
    }


    /**
     * @Route("/typeClient/new", name="typeClient_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function new(Request $request, FormError $formError, EntityManagerInterface  $em): Response
    {
        $typeClient = new TypeClient();
        $form = $this->createForm(TypeClientType::class,$typeClient, [
            'method' => 'POST',
            'action' => $this->generateUrl('typeClient_new')
        ]);

        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if($form->isSubmitted())
        {
            $statut = 1;
            $response = [];
            $redirect = $this->generateUrl('typeClient');

            if ($form->isValid()) {
                $typeClient->setActive(1);
                $em->persist($typeClient);
                $em->flush();

                $data = true;
                $message       = 'Opération effectuée avec succès';
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                  $this->addFlash('warning', $message);
                }
            }


            /*  }*/
            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
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

        return $this->render('_admin/typeClient/new.html.twig', [
            'typeClient' => $typeClient,
            'form' => $form->createView(),
            'titre' => 'Type Client',
        ]);
    }

    /**
     * @Route("/typeClient/{id}/edit", name="typeClient_edit", methods={"GET","POST"})
     * @param Request $request
     * @param TypeClient $typeClient
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Request $request, FormError $formError, TypeClient $typeClient, EntityManagerInterface  $em): Response
    {

        $form = $this->createForm(TypeClientType::class,$typeClient, [
            'method' => 'POST',
            'action' => $this->generateUrl('typeClient_edit',[
                'id'=>$typeClient->getId(),
            ])
        ]);
        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if($form->isSubmitted())
        {
            $statut = 1;
            $response = [];
            $redirect = $this->generateUrl('typeClient');

            if($form->isValid()){
                $em->persist($typeClient);
                $em->flush();

                $message       = 'Opération effectuée avec succès';
                $data = true;
                $this->addFlash('success', $message);

            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                  $this->addFlash('warning', $message);
                }
            }

            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/typeClient/edit.html.twig', [
            'typeClient' => $typeClient,
            'form' => $form->createView(),
            'titre' => 'Type Client',
        ]);
    }

    /**
     * @Route("/typeClient/{id}/show", name="typeClient_show", methods={"GET"})
     * @param TypeClient $typeClient
     * @return Response
     */
    public function show(TypeClient $typeClient): Response
    {
        $form = $this->createForm(TypeClientType::class,$typeClient, [
            'method' => 'POST',
            'action' => $this->generateUrl('typeClient_show',[
                'id'=>$typeClient->getId(),
            ])
        ]);

        return $this->render('_admin/typeClient/voir.html.twig', [
            'typeClient' => $typeClient,
            'titre' => 'Type Client',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/typeClient/{id}/active", name="typeClient_active", methods={"GET"})
     * @param $id
     * @param TypeClient $typeClient
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active($id,TypeClient $typeClient, EntityManagerInterface $entityManager): Response
    {

        if ($typeClient->getActive() == 1){

            $typeClient->setActive(0);

        }else{

            $typeClient->setActive(1);

        }
        $entityManager->persist($typeClient);
        $entityManager->flush();
        return $this->json([
            'code'=>200,
            'message'=>'ça marche bien',
            'active'=>$typeClient->getActive(),
        ],200);


    }


    /**
     * @Route("/typeClient/{id}/delete", name="typeClient_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TypeClient $typeClient
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em,TypeClient $typeClient): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'typeClient_delete'
                    ,   [
                        'id' => $typeClient->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->remove($typeClient);
            $em->flush();

            $redirect = $this->generateUrl('typeClient');

            $message = 'Opération effectuée avec succès';

            $response = [
                'statut'   => 1,
                'message'  => $message,
                'redirect' => $redirect,
                'data' => true
            ];

            $this->addFlash('success', $message);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirect($redirect);
            } else {
                return $this->json($response);
            }



        }
        return $this->render('_admin/typeClient/delete.html.twig', [
            'typeClient' => $typeClient,
            'form' => $form->createView(),
        ]);
    }

}
