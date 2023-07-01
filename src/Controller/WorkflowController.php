<?php

namespace App\Controller;


use App\Service\FormError;
use App\Service\ActionRender;
use App\Entity\GestionWorkflow;
use App\Form\GestionWorkflowType;
use App\Repository\TypeRepository;
use App\Repository\WorkflowRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Omines\Adapter\ArrayAdapter;
use App\Repository\GestionWorkflowRepository;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin")
 * il s'agit du workflow des module
 */
class WorkflowController extends AbstractController
{
    /**
     * @Route("/workflow", name="workflow")
     * @param WorkflowRepository $workflowRepository
     * @return Response
     */
    public function index(Request $request,
                          DataTableFactory $dataTableFactory,
                          GestionWorkflowRepository $gestionWorkflowRepository): Response
    {

        $table = $dataTableFactory->create();

        $user = $this->getUser();

        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;

        $totalData = $gestionWorkflowRepository->countAll();
        $totalFilteredData = $gestionWorkflowRepository->countAll($searchValue);
        $data = $gestionWorkflowRepository->getAll($limit, $offset,  $searchValue);

        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ]) ->setName('dt_');
        ;
        $table->add('titre', TextColumn::class, ['label' => 'Titre']);
        //$table->add('type', TextColumn::class, ['label' => 'Type', 'className' => 'w-100px']);
        $table->add('total', TextColumn::class, ['label' => 'Nbre Jrs', 'className' => 'w-80px'])
            //->add('active', TextColumn::class, ['label' => 'Actif', 'className' => 'w-50px'])
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
                                'url' => $this->generateUrl('workflow_edit', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-edit'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['edit'];
                                })
                            ],
                            'details' => [
                                'url' => $this->generateUrl('workflow_show', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-eye'
                                , 'attrs' => ['class' => 'btn-primary']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['details'];
                                })
                            ],
                            'delete' => [
                                'url' => $this->generateUrl('workflow_delete', ['id' => $value])
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

        return $this->render('_admin/workflow/index.html.twig', ['datatable' => $table, 'titre' => 'Liste des process']);
    }


    /**
     * @Route("/workflow/new", name="workflow_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TypeRepository $repository
     * @return Response
     */
    public function new(Request $request, FormError $formError, EntityManagerInterface $em, TypeRepository $repository): Response
    {
        $workflow = new GestionWorkflow();
        $form = $this->createForm(GestionWorkflowType::class, $workflow, [
            'method' => 'POST',
            'action' => $this->generateUrl('workflow_new')
        ]);

        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) 
        {
            $statut = 1;
            $response = [];
            $redirect = $this->generateUrl('workflow');
            $datas = $form->get('workflow')->getData();

            $typeActe = $repository->find($request->get('type'));
            $total =0;

            if ($form->isValid()) {

                foreach ($datas as $data) {
                    $data->setTypeActe($typeActe);
                    $total = $total + $data->getNombreJours();
                }
                $workflow->setActive(1);
                $workflow->setTotal($total);
                $workflow->setType($typeActe);
                $em->persist($workflow);
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

        return $this->render('_admin/workflow/new.html.twig', [
            'workflow' => $workflow,
            'form' => $form->createView(),
            'titre' => 'Worflow',
            'type' => $repository->findAll(),
        ]);
    }
    /**
     * @Route("/workflow/{id}/confirmation", name="workflow_confirmation", methods={"GET"})
     * @param $id
     * @param GestionWorkflow $parent
     * @return Response
     */
    public function confirmation($id,GestionWorkflow $parent): Response
    {
        return $this->render('_admin/modal/confirmation.html.twig',[
            'id'=>$id,
            'action'=>'workflow',
        ]);
    }


    /**
     * @Route("/workflow/{id}/edit", name="workflow_edit", methods={"GET","POST"})
     * @param Request $request
     * @param GestionWorkflow $workflow
     * @param EntityManagerInterface $em
     * @param TypeRepository $repository
     * @return Response
     */
    public function edit(Request $request, FormError $formError, GestionWorkflow $workflow, EntityManagerInterface $em, TypeRepository $repository): Response
    {

        $form = $this->createForm(GestionWorkflowType::class, $workflow, [
            'method' => 'POST',
            'action' => $this->generateUrl('workflow_edit', [
                'id' => $workflow->getId(),
            ])
        ]);
        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            $statut = 1;
            $response = [];
            $redirect = $this->generateUrl('workflow');
            $datas = $form->get('workflow')->getData();

            $typeActe = $repository->find($request->get('type'));
            $total =0;

            if ($form->isValid()) {

                foreach ($datas as $data) {
                    $data->setTypeActe($typeActe);
                    $total = $total + $data->getNombreJours();
                }
                $workflow->setActive(1);
                $workflow->setTotal($total);
                $workflow->setType($typeActe);
                $em->persist($workflow);
                $em->flush();

                $message = 'Opération effectuée avec succès';
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
                return $this->json(compact('statut', 'message', 'redirect', 'data'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/workflow/edit.html.twig', [
            'workflow' => $workflow,
            'form' => $form->createView(),
            'type' => $repository->findAll(),
            'titre' => 'Worflow',
        ]);
    }

    /**
     * @Route("/workflow/{id}/show", name="workflow_show", methods={"GET"})
     * @param GestionWorkflow $workflow
     * @return Response
     */
    public function show(GestionWorkflow $workflow, TypeRepository $repository): Response
    {
        $form = $this->createForm(GestionWorkflowType::class, $workflow, [
            'method' => 'POST',
            'action' => $this->generateUrl('workflow_show', [
                'id' => $workflow->getId(),
            ])
        ]);

        return $this->render('_admin/workflow/voir.html.twig', [
            'workflow' => $workflow,
            'titre' => 'Worflow',
            'type' => $repository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/workflow/{id}/active", name="workflow_active", methods={"GET"})
     * @param $id
     * @param GestionWorkflow $workflow
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active($id, GestionWorkflow $workflow, EntityManagerInterface $entityManager): Response
    {


        if ($workflow->getActive() == 1) {

            $workflow->setActive(0);

        } else {

            $workflow->setActive(1);

        }

        $entityManager->persist($workflow);
        $entityManager->flush();
        return $this->json([
            'code' => 200,
            'message' => 'ça marche bien',
            'active' => $workflow->getActive(),
        ], 200);


    }


    /**
     * @Route("/workflow/delete/{id}", name="workflow_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param GestionWorkflow $workflow
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em, GestionWorkflow $workflow): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'workflow_delete'
                    , [
                        'id' => $workflow->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->remove($workflow);
            $em->flush();

            $redirect = $this->generateUrl('workflow');

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
        return $this->render('_admin/workflow/delete.html.twig', [
            'workflow' => $workflow,
            'form' => $form->createView(),
        ]);
    }

}
