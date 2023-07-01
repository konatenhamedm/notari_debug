<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Client;
use App\Entity\Note;
use App\Form\CalendarType;
use App\Form\NoteType;
use App\Repository\CalendarRepository;
use App\Repository\CourierArriveRepository;
use App\Repository\DossierRepository;
use App\Repository\NoteRepository;
use App\Service\ActionRender;
use App\Service\FormError;
use App\Service\MailerService;
use App\Service\Omines\Adapter\ArrayAdapter;
use App\Service\PaginationService;
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
class CalendarController extends AbstractController
{

    /**
     * @Route("/calendar", name="calendar")
     * @return Response
     */
    public function index(Request $request): Response
    {
        $etats = [
            'cours' => 'Évènements à venir',
            'passe' => 'Évènements passés',
        ];
        return $this->render('_admin/calendar/index.html.twig', ['etats' => $etats, 'titre' => 'Liste des évènements']);
    }


    /**
     * @Route("/calendar/{etat}/liste", name="calendar_liste")
     * @param Request $request
     * @param string $etat
     * @param DataTableFactory $dataTableFactory
     * @param CourierArriveRepository $courierArriveRepository
     * @return Response
     */
    public function liste(Request $request,
                          string $etat,
                          DataTableFactory $dataTableFactory,
                          CalendarRepository $calendarRepository
    ): Response
    {

        $table = $dataTableFactory->create();

        $user = $this->getUser();

        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;

//dd($calendarRepository->countAlle());
        if ($etat === "cours") {
            $totalData = $calendarRepository->countAll();
            $totalFilteredData = $calendarRepository->countAll($searchValue);
            $data = $calendarRepository->getAll($limit, $offset, $searchValue);
        } else {
            $totalData = $calendarRepository->countAllPasse();
            $totalFilteredData = $calendarRepository->countAllPasse($searchValue);
            $data = $calendarRepository->getAllPasse($limit, $offset, $searchValue);
        }

        // dd($data);


        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ])
            ->setName('dt_');


        $table->add('title', TextColumn::class, ['label' => 'Titre', 'className' => 'w-100px'])
            ->add('start', DateTimeColumn::class, ['label' => 'Date de debut', 'format' => 'd-m-Y'])
            ->add('end', DateTimeColumn::class, ['label' => 'Date de fin', 'format' => 'd-m-Y']);


        $renders = [
            'edit' => new ActionRender(function () use ($etat) {
                return true;
            }),
            'note' => new ActionRender(function () use ($etat) {
                return true;
            }),
            /*'suivi' =>  new ActionRender(function () use ($etat) {
                return $etat == 'termine';
            }),*/
            'delete' => new ActionRender(function () use ($etat) {
                return true;
            }),
            /* 'archive' => new ActionRender(function () use ($etat) {
                 return true;
             }),*/
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
                , 'globalSearchable' => false
                , 'className' => 'grid_row_actions'
                , 'render' => function ($value, $context) use ($renders, $etat) {

                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#extralargemodal1',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('calendar_edit', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-edit'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => $renders['edit']

                            ],
                            /* 'suivi' => [
                                 'url' => $this->generateUrl('calendar_recep', ['id' => $value])
                                 , 'ajax' => true
                                 , 'icon' => '%icon% fe fe-mail'
                                 , 'attrs' => ['class' => 'btn-dark', 'title' => 'Accuse de reception']
                                 , 'render' =>$renders['suivi']

                             ],*/
                            'details' => [
                                'url' => $this->generateUrl('calendar_show', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-eye'
                                , 'attrs' => ['class' => 'btn-primary']
                                , 'render' => $renders['details']

                            ],
                            'delete' => [
                                'url' => $this->generateUrl('calendar_delete', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-trash-2'
                                , 'target' => '#smallmodal'
                                , 'attrs' => ['class' => 'btn-danger', 'title' => 'Suppression']

                                , 'render' => $renders['delete']

                            ],
                             'note' => [
                                 'url' => $this->generateUrl('calendar_note', ['id' => $value])
                                 , 'ajax' => true
                                 , 'icon' => '%icon% fa fa-file'
                                 , 'attrs' => ['class' => 'btn-info', 'title' => 'Note']

                                 ,  'render' => $renders['note']

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

        return $this->render('_admin/calendar/liste.html.twig', ['datatable' => $table, 'etat' => $etat]);
    }

    /**
     * @Route("/calendar/{id}/show", name="calendar_show", methods={"GET"})
     */
    public function show(Calendar $calendar): Response
    {
        $form = $this->createForm(CalendarType::class, $calendar, [
            'method' => 'POST',
            'action' => $this->generateUrl('calendar_show', [
                'id' => $calendar->getId(),
            ])
        ]);

        return $this->render('_admin/calendar/voir.html.twig', [
            'calendar' => $calendar,
            'titre' => 'Evenement',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/calendar/new", name="calendar_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param FormError $formError
     * @param UploaderHelper $uploaderHelper
     * @param MailerService $mailerService
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $em, FormError $formError, UploaderHelper $uploaderHelper): Response
    {
        $calendar = new Calendar();
        $calendar->setEnd(new \DateTime());
        $calendar->setNbreJours(0);
        $form = $this->createForm(CalendarType::class, $calendar, [
            'method' => 'POST',
            'action' => $this->generateUrl('calendar_new')
        ]);
        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();
       // $dossier = $form->get('dossier')->getData();
       // dd($dossier);
        if ($form->isSubmitted()) {
            $redirect = $this->generateUrl('calendar');

            // $email = "konatenfvaly@gmail.com";
            // $objet ="je viens de loin";
            // $identite = "KONATE NOTARIE";
            if ($form->isValid()) {

                // $mailerService->send(
                //    'INFORMATION CONCERNANT LE RENDEZ-VOUS',
                //    'konatefvaly@gmail.com',
                //    "konatenhamed@gmail.com",
                //    "_admin/contact/template.html.twig",
                //    [
                //        'message' =>  $objet,
                //        'entreprise' =>  "Notari",
                //        'identite' =>  $identite,
                //        //'telephone' =>  '0704314164'
                //    ]
                // );
               /* if($dossier != null){
                    $calendar->setDossier($dossier);
                }*/
                $calendar->setActive(1)
                    ->setAllDay(false)
                    ->setBackgroundColor("#31F74F")
                    ->setBorderColor("#BBF0DA")
                    ->setTextColor("#FAF421");
                $em->persist($calendar);
                $em->flush();

                $mailerService->send($calendar->getTitle(),'etuderck@notari.tech',$calendar->getClient()->getEmail()/*'mikhail3e@yahoo.fr'*/,'_admin/calendar/mail.html.twig',['calendar' => $calendar]);

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

        return $this->render('_admin/calendar/new.html.twig', [
            'titre' => 'Evenement',
            'calendar' => $calendar,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/calendar/{id}/edit", name="calendar_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Calendar $calendar
     * @param EntityManagerInterface $em
     * @param UploaderHelper $uploaderHelper
     * @return Response
     */
    public function edit(Request $request, Calendar $calendar, FormError $formError, EntityManagerInterface $em, UploaderHelper $uploaderHelper): Response
    {

        $form = $this->createForm(CalendarType::class, $calendar, [
            'method' => 'POST',
            'action' => $this->generateUrl('calendar_edit', [
                'id' => $calendar->getId(),
            ])
        ]);
        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('calendar');
            $statut = 1;
            if ($form->isValid()) {

                $em->persist($calendar);
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

        return $this->render('_admin/calendar/edit.html.twig', [
            'titre' => 'Evenement',
            'calendar' => $calendar,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/calendar/{id}/note", name="calendar_note", methods={"GET","POST"})
     * @param Request $request
     * @param Calendar $calendar
     * @param EntityManagerInterface $em
     * @param UploaderHelper $uploaderHelper
     * @return Response
     */
    public function note(Request $request, Calendar $calendar,NoteRepository $noteRepository, FormError $formError, EntityManagerInterface $em, UploaderHelper $uploaderHelper): Response
    {
        //$dt = new \DateTime();
       // dd($dt->format('H:i:s'));

         $note = $noteRepository->findOneBy(array('calendar'=>$calendar)) ?: new Note();
         if (!$note->getId()){
             $note->setCalendar($calendar);
             $note->setDate(new \DateTime());
         }
        $form = $this->createForm(NoteType::class, $note, [
            'method' => 'POST',
            'action' => $this->generateUrl('calendar_note', [
                'id' => $calendar->getId(),
            ])
        ]);
        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('calendar');
            $statut = 1;
            if ($form->isValid()) {

                $note->setCalendar($calendar);
                $em->persist($note);
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

        return $this->render('_admin/calendar/note.html.twig', [
            'note' => $note,
            'titre'=>'de notes',
            'calendar' => $calendar,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/calendar/delete/{id}", name="calendar_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param Calendar $calendar
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em, Calendar $calendar): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'calendar_delete'
                    , [
                        'id' => $calendar->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->remove($calendar);
            $em->flush();

            $redirect = $this->generateUrl('calendar');

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
        return $this->render('_admin/calendar/delete.html.twig', [
            'calendar' => $calendar,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/calendar/{id}/active", name="calendar_active", methods={"GET"})
     * @param $id
     * @param Calendar $parent
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active(Calendar $parent, EntityManagerInterface $entityManager): Response
    {
        // $entityManager = $this->getDoctrine()->getManager();


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
     * @Route("/acte", name="acte", methods={"GET","POST"})
     * @param DossierRepository $repository
     * @param Request $request
     * @return Response
     */
    public function dossier(DossierRepository $repository,Request $request): Response
    {
        $response = new Response();
        $tabEnsembles = array();
        if ($request->isXmlHttpRequest()) {
            $id = "";
            $id = $request->get("id");
            if($id){
                $data  = $repository->getListeDossier($id);
               // dd($data);



                $i = 0;

                foreach($data as $e) { // transformer la réponse de la requete en tableau qui remplira le select pour ensembles
                    //dd($e['id']);
                    $tabEnsembles[$i]['numeroOuverture'] = $e['numeroOuverture'];
                    $tabEnsembles[$i]['id'] = $e['id'];
                    $tabEnsembles[$i]['objet'] =$e['objet'];
                    $i++;
                }
                $data = json_encode($tabEnsembles); // formater le résultat de la requête en json
                //dd($data);
                $response->headers->set('Content-type', 'application/json');
                $response->setContent($data);
            }


        }
        //dd($response);
        return $response;

    }
}
