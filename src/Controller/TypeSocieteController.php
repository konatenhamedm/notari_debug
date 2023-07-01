<?php

namespace App\Controller;

use App\Service\FormError;
use App\Entity\TypeSociete;
use App\Form\TypeSocieteType;
use App\Service\ActionRender;
use App\Entity\GestionWorkflow;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TypeSocieteRepository;
use App\Service\Omines\Adapter\ArrayAdapter;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/admin")
 */
class TypeSocieteController extends AbstractController
{

    /**
     * @Route("/typeSociete/{id}/confirmation", name="typeSociete_confirmation", methods={"GET"})
     * @param $id
     * @param TypeSociete $parent
     * @return Response
     */
    public function confirmation($id,TypeSociete $parent): Response
    {
        return $this->render('_admin/modal/confirmation.html.twig',[
            'id'=>$id,
            'action'=>'typeSociete',
        ]);
    }


    /**
     * @Route("/typeSociete", name="typeSociete")
     * @param TypeSocieteRepository $typeSocieterepository
     * @return Response
     */
    public function index(Request $request,
                          DataTableFactory $dataTableFactory,
                          TypeSocieteRepository $typeSocieterepository): Response
    {

        $table = $dataTableFactory->create();

        $user = $this->getUser();

        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;

        $totalData = $typeSocieterepository->countAll();
        $totalFilteredData = $typeSocieterepository->countAll($searchValue);
        $data = $typeSocieterepository->getAll($limit, $offset,  $searchValue);

        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ]) ->setName('dt_');
        ;


        $table
            ->add('sigle', TextColumn::class, ['label' => 'Sigle', 'className' => 'w-30px'])
            ->add('libelle', TextColumn::class, ['label' => 'Libelle', 'className' => 'w-100px']);

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
                                'url' => $this->generateUrl('typeSociete_edit', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-edit'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['edit'];
                                })
                            ],
                            'details' => [
                                'url' => $this->generateUrl('typeSociete_show', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-eye'
                                , 'attrs' => ['class' => 'btn-primary']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['details'];
                                })
                            ],
                            'delete' => [
                                'url' => $this->generateUrl('typeSociete_delete', ['id' => $value])
                                , 'ajax' => true
                                , 'target' => '#smallmodal'
                                , 'icon' => '%icon% fe fe-trash-2'
                                , 'attrs' => ['class' => 'btn-danger', 'title' => 'Suppression']

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

        return $this->render('_admin/typeSociete/index.html.twig', ['datatable' => $table, 'titre' => 'Liste des types de société']);    
    }


    /**
     * @Route("/type_societe/liste", name="type_societe_liste")
     * @param Request $request
     * @param DataTableFactory $dataTableFactory
     * @param TypeSocieteRepository $typeSocieteRepository
     * @return Response
     */
    public function liste(Request $request,
                          DataTableFactory $dataTableFactory,
                          TypeSocieteRepository $typeSocieteRepository
    ): Response
    {

        $table = $dataTableFactory->create();

        $user = $this->getUser();

        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;



        $totalData = $typeSocieteRepository->countAll();
        $totalFilteredData = $typeSocieteRepository->countAll($searchValue);
        $data = $typeSocieteRepository->getAll($limit, $offset,  $searchValue);

//dd($data);


        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ]) ->setName('dt_');
        ;


        $table->add('libelle', TextColumn::class, ['label' => 'Libelle', 'className' => 'w-100px'])
            ->add('sigle', TextColumn::class, ['label' => 'Sigle', 'className' => 'w-100px'])
        ;




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
                                'url' => $this->generateUrl('typeSociete_edit', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-edit'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['edit'];
                                })
                            ],
                            /*'suivi' => [
                                'url' => $this->generateUrl('dossier_suivi', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-book'
                                , 'attrs' => ['class' => 'btn-dark', 'title' => 'Suivi du dossier']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['suivi'];
                                })
                            ],*/
                            'details' => [
                                'url' => $this->generateUrl('typeSociete_show', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-eye'
                                , 'attrs' => ['class' => 'btn-primary']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['details'];
                                })
                            ],
                            'delete' => [
                                'url' => $this->generateUrl('typeSociete_delete', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-trash-2'
                                , 'target' => '#smallmodal'
                                , 'attrs' => ['class' => 'btn-danger', 'title' => 'Suppression']

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

        return $this->render('_admin/typeSociete/liste.html.twig', ['datatable' => $table]);
    }

    /**
     * @Route("/archive/{id}/TYPE SOCIETE", name="typeSociete_archive", methods={"GET"})
     * @param $id
     * @param TypeSocieteRepository $repository
     * @return Response
     */
    public  function  archive($id, TypeSocieteRepository $repository){


        return $this->render('_admin/typeSociete/archive.html.twig', [
            'titre'=>'Type societe',
        ]);
    }

    /**
     * @Route("/typeSociete/{id}/show", name="typeSociete_show", methods={"GET"})
     * @param TypeSociete $typeSociete
     * @param $id
     * @param TypeSocieteRepository $repository
     * @return Response
     */
    public function show(TypeSociete $typeSociete,$id,TypeSocieteRepository $repository): Response
    {
        //$type = $typeSociete->getType();
//dd($repository->getFichier($id));
        $form = $this->createForm(TypeSocieteType::class, $typeSociete, [

            'method' => 'POST',
            'action' => $this->generateUrl('typeSociete_show', [
                'id' => $typeSociete->getId(),
            ])
        ]);

        return $this->render('_admin/typeSociete/voir.html.twig', [
            'titre'=>'TYPE SOCIETE',
            'typeSociete' => $typeSociete,
            'form' => $form->createView(),
            'data'=>$repository->getFichier($id),
        ]);
    }

    /**
     * @Route("/typeSociete/new", name="typeSociete_new", methods={"GET","POST"})
     * @param Request $request
     * @param FormError $formError
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function new(Request $request, FormError $formError, EntityManagerInterface  $em): Response
    {
        $typeSociete = new TypeSociete();
        $form = $this->createForm(TypeSocieteType::class,$typeSociete, [
            'method' => 'POST',
            'action' => $this->generateUrl('typeSociete_new')
        ]);

        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if($form->isSubmitted())
        {
            $statut = 1;
            $response = [];
            $redirect = $this->generateUrl('typeSociete');

            if ($form->isValid()) {
                $typeSociete->setActive(1);
                $em->persist($typeSociete);
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
        }

        return $this->render('_admin/typeSociete/new.html.twig', [
            'typeSociete' => $typeSociete,
            'form' => $form->createView(),
            'titre' => 'Type société',
        ]);
    }

    /**
     * @Route("/typeSociete/{id}/edit", name="typeSociete_edit", methods={"GET","POST"})
     * @param Request $request
     * @param TypeSociete $typeSociete
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Request $request, FormError $formError, TypeSociete $typeSociete, EntityManagerInterface  $em): Response
    {

        $form = $this->createForm(TypeSocieteType::class,$typeSociete, [
            'method' => 'POST',
            'action' => $this->generateUrl('typeSociete_edit',[
                'id'=>$typeSociete->getId(),
            ])
        ]);
        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if($form->isSubmitted())
        {
            $statut = 1;
            $response = [];
            $redirect = $this->generateUrl('typeSociete');

            if($form->isValid()){
                $em->persist($typeSociete);
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

        return $this->render('_admin/typeSociete/edit.html.twig', [
            'typeSociete' => $typeSociete,
            'form' => $form->createView(),
            'titre' => 'Type société',
        ]);
    }

    /**
     * @Route("/accuse/{id}", name="typeSociete_accuse_edit", methods={"GET","POST"})
     * @param Request $request
     * @param TypeSociete $typeSociete
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function accuse(Request $request, TypeSociete $typeSociete, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TypeSocieteType::class, $typeSociete, [
            'method' => 'POST',
            'action' => $this->generateUrl('typeSociete_accuse_edit', [
                'id' => $typeSociete->getId(),
            ])
        ]);


        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest();
      //  $type = $form->getData()->getType();
        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('typeSociete');

            if ($form->isValid()) {

                $em->persist($typeSociete);
                $em->flush();

                $message = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);

            }

            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/typeSociete/accuse.html.twig', [
            'titre'=>"ACCUSE DE RECEPTION",
            'typeSociete' => $typeSociete,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/typeSociete/delete/{id}", name="typeSociete_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param typeSociete $typeSociete
     * @return Response
     */
    public function delete($id,Request $request, EntityManagerInterface $em, typeSociete $typeSociete): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'typeSociete_delete'
                    , [
                        'id' => $typeSociete->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $categorie = $em->getRepository(TypeSociete::class)->find($typeSociete->getId());
            $em->remove($categorie);
           // $em->remove($typeSociete);
            $em->flush();

            $redirect = $this->generateUrl('typeSociete');

            $message = 'Opération effectuée avec succès';

            $response = [
                'statut' => 1,
                'message' => $message,
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
        return $this->render('_admin/typeSociete/delete.html.twig', [
            'typeSociete' => $typeSociete,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/typeSociete/{id}/active", name="typeSociete_active", methods={"GET"})
     * @param $id
     * @param TypeSociete $parent
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active($id, TypeSociete $parent, EntityManagerInterface $entityManager): Response
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

}
  