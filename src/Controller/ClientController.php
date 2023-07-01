<?php

namespace App\Controller;

use App\Classe\UploadFile;
use App\Entity\Client;
use App\Entity\CourierArrive;
use App\Entity\TypeClient;
use App\Form\ClientType;
use App\Form\UploadFileType;
use App\Repository\ClientRepository;
use App\Repository\TypeClientRepository;
use App\Service\ActionRender;
use App\Service\FormError;
use App\Service\Omines\Adapter\ArrayAdapter;
use App\Service\PaginationService;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin")
 */
class ClientController extends AbstractController
{
    use FileTrait;

    private const UPLOAD_PATH = 'client';

     /**
     * @Route("/client", name="client")
     * @param ClientRepository $repository
     * @return Response
     */
    public function index(Request $request, TypeClientRepository $typeClientRepository): Response
    {
       
        $types = [];
        foreach ($typeClientRepository->findAll() as $typeClient) {
            $id = $typeClient->getId();
            $types[] = [
                'url' => $this->generateUrl('client_ls', ['id' => $id]),
                'label' => $typeClient->getTitre(),
                'id' => 'type_'.$id
            ];
        }
        
        
        return $this->render('_admin/client/index.html.twig', ['types' => $types, 'titre' => 'Liste des clients']);
    }


    /**
     * @Route("/client/{id}/liste", name="client_ls")
     * @param DossierRepository $repository
     * @return Response
     */
    public function liste(Request $request, 
        TypeClient $typeClient, 
        DataTableFactory $dataTableFactory, 
        ClientRepository $clientRepository
    ): Response
    {

        $table = $dataTableFactory->create();
        $idTypeClient = $typeClient->getId();
        $user = $this->getUser();
        
        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;



        $totalData = $clientRepository->countAll($idTypeClient);
        $totalFilteredData = $clientRepository->countAll($idTypeClient, $searchValue);
        $data = $clientRepository->getAll($idTypeClient, $limit, $offset,  $searchValue);
        //dd($data);
       


        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ])
        ->setName('dt_'.$typeClient->getId());

        /*'tableau' => [

            'Nom' => 'Nom',
            'Prenoms' => 'Prenoms',
            'email' => 'email',
            'profession' => 'profession',
            'Téléphone' => 'Téléphone',
        ],
        'tableau1' => [

            'Raison_social' => 'Raison_social',
            'Registre_commerce' => 'Registre_commerce',
            'Boite_postal' => 'Boite_postal',
            'Site_web' => 'Site_web',
            'Email' => 'Email',
        ],*/

        $code = $typeClient->getCode();

        if ($code == 'E') {
            $table->add('raison_social', TextColumn::class, ['label' => 'Raison sociale', 'className' => 'w-100px'])
            ->add('registre_commercial', TextColumn::class, ['label' => 'Registre de commerce'])
            ->add('boite_postal', TextColumn::class, ['label' => 'Boite Postale', 'className' => 'w-100px text-right'])
            ->add('site_web', TextColumn::class, ['label' => 'Site WEB'])
            ->add('email', TextColumn::class, ['label' => 'Adresse E-mail'])
            
        ;
        } else {
            $table->add('nom', TextColumn::class, ['label' => 'Nom', 'className' => 'w-100px'])
            ->add('prenom', TextColumn::class, ['label' => 'Prénoms'])
            ->add('email', TextColumn::class, ['label' => 'Adresse E-mail'])
            ->add('profession', TextColumn::class, ['label' => 'Profession', 'className' => 'w-100px text-right'])
            ->add('tel_portable', TextColumn::class, ['label' => 'Téléphone']);
        }
        


        $renders = [
            'edit' =>  new ActionRender(function ()  {
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
                                'url' => $this->generateUrl('client_edit', ['id' => $value])
                                , 'ajax' => false
                                , 'icon' => '%icon% fe fe-edit'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => $renders['edit']
                                
                            ],
                            'delete' => [
                                'url' => $this->generateUrl('client_delete', ['id' => $value])
                                , 'ajax' => true
                                , 'target' => '#smallmodal'
                                , 'icon' => '%icon% fe fe-trash'
                                , 'attrs' => ['class' => 'btn-danger']
                                , 'render' => $renders['edit']
                                
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

        return $this->render('_admin/client/liste.html.twig', ['datatable' => $table, 'id_type_client' => $idTypeClient]);
    }


    
    public function _index(ClientRepository $repository): Response
    {

        $pagination = $repository->findBy(['type_client'=>1,'active'=>1]);
        $pagination1 = $repository->findBy(['type_client'=>2,'active'=>1]);

        return $this->render('_admin/client/index.html.twig', [
            'physique' => $pagination,
            'moral' => $pagination1,
            'tableau' => [

                'Nom' => 'Nom',
                'Prenoms' => 'Prenoms',
                'email' => 'email',
                'profession' => 'profession',
                'Téléphone' => 'Téléphone',
            ],
            'tableau1' => [

                'Raison_social' => 'Raison_social',
                'Registre_commerce' => 'Registre_commerce',
                'Boite_postal' => 'Boite_postal',
                'Site_web' => 'Site_web',
                'Email' => 'Email',
            ],
            'critereTitre'=>'',
            'modal' => '',
            'position' => 4,
            'active'=> 3,
            'titre' => 'Liste des clients',

        ]);
    }

    /**
     * @Route("/client/{id}/show", name="client_show", methods={"GET"})
     * @param Client $client
     * @return Response
     */
    public function show(client $client): Response
    {
        $form = $this->createForm(ClientType::class, $client, [
            'method' => 'POST',
            'action' => $this->generateUrl('client_show', [
                'id' => $client->getId(),
            ])
        ]);

        return $this->render('_admin/client/voir.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/modal/show", name="modal_show", methods={"GET","POST"})
     */
    public function modal(): Response
    {


        return $this->render('_admin/client/modal.html.twig');
    }

    /**
     * @Route("/client/new", name="client_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UploaderHelper $uploaderHelper
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $em, UploaderHelper $uploaderHelper, FormError $formError): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'photo' => [
                'doc_options' => [
                    'uploadDir' => $this->getUploadDir('client', true),
                    'attrs' => ['class' => 'filestyle'],
                ],
            ],
           
            'action' => $this->generateUrl('client_new')
        ]);

        
        $statut=0;
       
        $form->handleRequest($request);
        $fullRedirect = false;
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('client');

            if ($form->isValid()) {

               
              
                $client->setActive(1);
                $em->persist($client);
                $em->flush();
                $statut = 1;
                $fullRedirect = true;
              
                $message = 'Opération effectuée avec succès';

                $this->addFlash('success', $message);

            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }

            // dd($isAjax);
            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data', 'fullRedirect'));
            } else {
                if ($statut == 1) {
                    return $this->redirectToRoute('client');
                }

            }
        }

        return $this->render('_admin/client/new.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/client/{id}/edit", name="client_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Client $client
     * @param EntityManagerInterface $em
     * @param UploaderHelper $uploaderHelper
     * @return Response
     */
    public function edit(Request $request,  FormError $formError, Client $client, EntityManagerInterface $em, UploaderHelper $uploaderHelper): Response
    {

        $form = $this->createForm(ClientType::class, $client, [
            'method' => 'POST',
            'action' => $this->generateUrl('client_edit', [
                'id' => $client->getId(),
            ]),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'photo' => [
                'doc_options' => [
                    'uploadDir' => $this->getUploadDir('client', true),
                    'attrs' => ['class' => 'filestyle'],
                ],
            ],
        ]);
        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest();

        $files = [];

        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('client');


            if ($form->isValid()) {

                
                $em->persist($client);
                $em->flush();

                $message = 'Client modifié avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
                foreach ($client->getDocuments() as $document) {
                    $files[$document->getDocHash()] = $this->generateUrl('fichier_index', ['id' => $document->getFichier()->getId()]);
                }

            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }

            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'files'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/client/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/client/{id}/delete", name="client_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param Client $client
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em, Client $client): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'client_delete'
                    , [
                        'id' => $client->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            $em->remove($client);
            $em->flush();

            $redirect = $this->generateUrl('client');

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
        return $this->render('_admin/client/delete.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/liste_tarife", name="liste_tarife_index", methods={"GET","POST"})
     * @param Request $request
     * @param DepartementRepository $repository
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function remplirSelect2Action(Request $request, DepartementRepository $repository, EntityManagerInterface $em): Response
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) { // pour vérifier la présence d'une requete Ajax

            $id = "";
            $id = $request->get('id');

            if ($id) {

                $ensembles = $repository->listeDepartement($id);

                $arrayCollection = array();

                foreach ($ensembles as $item) {
                    $arrayCollection[] = array(
                        'id' => $item->getId(),
                        'libelle' => $item->getLibDepartement(),
                        // ... Same for each property you want
                    );
                }
                $data = json_encode($arrayCollection); // formater le résultat de la requête en json
                //dd($data);
                $response->headers->set('Content-TypeActe', 'application/json');
                $response->setContent($data);

            }

        }

        return $response;
    }

    /**
     * @Route("/client/{id}/active", name="client_active", methods={"GET"})
     * @param $id
     * @param Client $parent
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active($id, Client $parent, EntityManagerInterface $entityManager): Response
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
     * @Route("/client/addFile", name="client_addFile_new", methods={"GET","POST"})
     */
    public function addFile(Request $request,FormError $formError,TypeClientRepository $typeClientRepository,ClientRepository $clientRepository, EntityManagerInterface $entityManager)
    {
        $dossier = new UploadFile();
        $form = $this->createForm(UploadFileType::class,$dossier, [
            'method' => 'POST',
            'action' => $this->generateUrl('client_addFile_new')
        ]);
        $form->handleRequest($request);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl('client');

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


                foreach ($sheetData as $Row)
                {

                    $nom = $Row['A'];     // store the first_name on each iteration
                    //$desig = $Row['B'];   // store the last_name on each iteration


                    $client_existant = $clientRepository->findOneBy(array('nom' => $nom));


                    // make sure that the user does not already exists in your db
                    if (!$client_existant)
                    {

                        $colisage = new Client();
                        $colisage->setNom($nom);
                        $colisage->setTypeClient($typeClientRepository->findOneBy(array("titre"=>"Particuliers")));
                        $entityManager->persist($colisage);
                        $entityManager->flush();
                        // here Doctrine checks all the fields of all fetched data and make a transaction to the database.
                    }else{

                        $client_existant->setNom($nom);
                        $client_existant->setTypeClient($typeClientRepository->findOneBy(array("titre"=>"Particuliers")));
                        $entityManager->persist($client_existant);
                        $entityManager->flush();
                    }
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