<?php

namespace App\Controller\Dossier;

use App\Entity\Archive;
use App\Form\ArchiveType;
use App\Repository\ArchiveRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FormError;
use APY\DataGridBundle\Grid\Source\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\DataTableFactory;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\BoolColumn;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\ActionRender;
use App\Annotation\Module;
use App\Controller\FileTrait;
use App\Repository\TypeRepository;

/**
 * @Route("/admin/dossier/archive", options={"expose"=true}))
 */
class ArchiveController extends AbstractController
{
    use FileTrait;
    /**
     * @Route("/", name="app_dossier_archive_index", methods={"GET", "POST"}, options={"expose"=true})
     */
    public function index(Request $request, DataTableFactory $dataTableFactory): Response
    {
        $typeActe = $request->query->get('type_acte');
        $table = $dataTableFactory->create()
        ->add('numeroOuverture', TextColumn::class, ['label' => 'Numéro Ouverture'])
        ->add('dateOuverture', DateTimeColumn::class, ['label' => 'Date d\'ouverture', 'format' => 'd-m-Y'])
        ->add('objet', TextColumn::class, ['label' => 'Objet'])
        ->add('numeroClassification', TextColumn::class, ['label' => 'Numéro Classification'])
        ->add('dateClassification', DateTimeColumn::class, ['label' => 'Date de classification', 'format' => 'd-m-Y'])
        ->createAdapter(ORMAdapter::class, [
            'entity' => Archive::class,
        ])
        ->setName('dt_app_dossier_archive_'.$typeActe);
        
        $renders = [
            'edit' =>  new ActionRender(function () {
                return true;
            }),
            'delete' => new ActionRender(function () {
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
                , 'orderable' => false
                ,'globalSearchable' => false
                ,'className' => 'grid_row_actions'
                , 'render' => function ($value, Archive $context) use ($renders) {
                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#extralargemodal1',
                            
                        'actions' => [
                            'edit' => [
                            'url' => $this->generateUrl('app_dossier_archive_edit', ['id' => $value])
                            , 'ajax' => true
                            , 'icon' => '%icon% fe fe-edit'
                            , 'attrs' => ['class' => 'btn-primary']
                            , 'render' => $renders['edit']
                        ],
                        'delete' => [
                            'target' => '#smallmodal',
                            'url' => $this->generateUrl('app_dossier_archive_delete', ['id' => $value])
                            , 'ajax' => true
                            , 'icon' => '%icon% fe fe-trash'
                            , 'attrs' => ['class' => 'btn-danger']
                            ,  'render' => $renders['delete']
                        ]
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

        return $this->render('dossier/archive/index.html.twig', ['datatable' => $table, 'type_acte' => $typeActe]);
    }

    /**
     * @Route("/new", name="app_dossier_archive_new", methods={"GET","POST"})
     */
    public function new(Request $request, EntityManagerInterface $em, FormError $formError, TypeRepository $typeRepository): Response
    {
        $codeTypeActe = $request->query->get('type_acte', 'acte_vente');
        $acteVente = $typeRepository->findOneBy(['code'=>$codeTypeActe]);
        $archive = new Archive();
        $archive->setTypeActe($acteVente);
        $filePath = 'archives';
        $form = $this->createForm(ArchiveType::class, $archive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir($filePath, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl('app_dossier_archive_new', ['type_acte' => $codeTypeActe])
        ]);
        $form->handleRequest($request);

        $data = null;
        $statutCode = 200;

        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl('app_dossier_archive_index', ['type_acte' => $codeTypeActe]);
            
            

            if ($form->isValid()) {
                
                $em->persist($archive);
                $em->flush();
                $data = true;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);

                
            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = 500;
                if (!$isAjax) {
                  $this->addFlash('warning', $message);
                }
                
            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('dossier/archive/new.html.twig', [
            'archive' => $archive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="app_dossier_archive_show", methods={"GET"})
     */
    public function show(Archive $archive): Response
    {
        return $this->render('dossier/archive/show.html.twig', [
            'archive' => $archive,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_dossier_archive_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Archive $archive, FormError $formError, EntityManagerInterface $em): Response
    {
        $typeActe = $archive->getTypeActe();
        $filePath = 'archives';
        $form = $this->createForm(ArchiveType::class, $archive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir($filePath, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl('app_dossier_archive_edit', ['id' =>  $archive->getId()])
        ]);
        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest();

        $data = null;
        $statutCode = 200;
        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl('app_dossier_archive_index', ['type_acte' => $typeActe->getCode()]);
            

            if ($form->isValid()) {
                $em->flush();
                $data = true;
                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);

                
            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = 500;
                if (!$isAjax) {
                  $this->addFlash('warning', $message);
                }
                
            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('dossier/archive/edit.html.twig', [
            'archive' => $archive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_dossier_archive_delete", methods={"DELETE", "GET"})
     */
    public function delete(Request $request, EntityManagerInterface $em, Archive $archive): Response
    {
    

        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                'app_dossier_archive_delete'
                ,   [
                        'id' => $archive->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);
        $typeActe = $archive->getTypeActe();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = true;
            $em->remove($archive);
            $em->flush();
            $codeTypeActe = $typeActe->getCode();

            $redirect = $this->generateUrl('app_dossier_archive_index', ['type_acte' => $typeActe->getCode()]);
          

            $message = 'Opération effectuée avec succès';

            $response = [
                'statut'   => 1,
                'message'  => $message,
                'redirect' => $redirect,
                'data' => $data
            ];

            $this->addFlash('success', $message);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirect($redirect);
            } else {
                return $this->json($response);
            }


           
        }

        return $this->render('dossier/archive/delete.html.twig', [
            'archive' => $archive,
            'form' => $form->createView(),
        ]);
    }
}
