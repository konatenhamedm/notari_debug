<?php

namespace App\Controller;

use App\Entity\CourierArrive;
use App\Form\CourierDepartAccuseType;
use App\Form\CourierDepartArchiveType;
use App\Form\CourierDepartType;
use App\Repository\ClientRepository;
use App\Repository\CourierArriveRepository;
use App\Repository\DocumentCourrierRepository;
use App\Repository\DocumentReceptionRepository;
use App\Service\ActionRender;
use App\Service\FormError;
use App\Service\Omines\Adapter\ArrayAdapter;
use App\Service\UploaderHelper;
use App\Service\Util;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\NumberColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Cell;
use PhpOffice\PhpWord\Style\TablePosition;
use PhpOffice\PhpWord\Writer\HTML;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;


/**
 * @Route("/admin")
 */
class CourierDepartController extends AbstractController
{
    use FileTrait;
    private const UPLOAD_PATH = 'courrier';
    /**
     * @var mixed
     */
    private $util;

    private $client;
    private $em;

    /**
     * @param Util $util
     */
    public function __construct(Util $util,ClientRepository $client,EntityManagerInterface $em)
    {
        $this->util = $util;
        $this->em = $em;
        $this->client = $client;
    }

    /**
     * @Route("/courier-depart", name="courierDepart")
     * @return Response
     */
    public function index(Request $request): Response
    {
        $etats = [
            'cree' => 'En cours de traitement',
            'termine' => 'Déchargés',
        ];
        return $this->render('_admin/depart/index.html.twig', ['etats' => $etats, 'titre' => 'Liste des courriers depart']);
    }


    /**
     * @Route("/depart/{etat}/liste", name="depart_liste")
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



        $totalData = $courierArriveRepository->countAllDepart($etat);
        $totalFilteredData = $courierArriveRepository->countAllDepart($etat, $searchValue);
        $data = $courierArriveRepository->getAllDepart($etat, $limit, $offset,  $searchValue);
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
            ->add('destinataire', TextColumn::class, ['label' => 'Destinataire', 'className' => 'w-100px'])
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
                                'url' => $this->generateUrl('courierDepart_edit', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-edit'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => $renders['edit']

                            ],

                            'details' => [
                                'url' => $this->generateUrl('courierDepart_show', ['id' => $value])
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
                                'url' => $this->generateUrl('courierDepart_archive', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-folder'
                                , 'attrs' => ['class' => 'btn-info', 'title' => 'Archivage']

                                ,  'render' => $renders['archive']

                            ],
                            'accuse' => [
                                'url' => $this->generateUrl('courierDepart_accuse_edit', ['id' => $value])
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

        return $this->render('_admin/depart/liste.html.twig', ['datatable' => $table, 'etat' => $etat]);
    }


    /**
     * @Route("/archive/{id}/depart", name="courierDepart_archive", methods={"GET","POST"})
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
        //$form->
        $isAjax = $request->isXmlHttpRequest();
        //$courierArrive->setDossier("ffffr");
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
            'titre'=>'DEPART',
            'data'=>$repository->getFichier($id),
            'dataAccuseReception'=>$documentReceptionRepository->getFichier($id),
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);

    }


    /**
     * @Route("/all_archive/{id}/depart", name="courierDepart_all_archive", methods={"GET","POST"})
     * @param $id
     * @param DocumentCourrierRepository $repository
     * @param DocumentReceptionRepository $documentReceptionRepository
     * @return Response
     */
    public  function  allArchive($id,Request $request,FormError $formError, CourierArrive $courierArrive,  DocumentCourrierRepository $repository,DocumentReceptionRepository $documentReceptionRepository)
    {
//dd($repository->getFichier($id),$id);

        $form = $this->createForm(CourierDepartArchiveType::class, $courierArrive, [
            'method' => 'POST',
            'action' => $this->generateUrl('courierDepart_archive', [
                'id' => $courierArrive->getId(),
            ])
        ]);

        return $this->render('_admin/depart/all_archive.html.twig', [
            'titre'=>'Liste des documents',
            'data'=>$repository->getFichier($id),
            'dataAccuseReception'=>$documentReceptionRepository->getFichier($id),
            'courierArrive' => $courierArrive,
        ]);

    }


    private function generateFile(CourierArrive $courrier, $distinct = true, $download = false)
    {

        if (!$distinct) {
            $language = new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::FR_FR);
            $phpWord  = new \PhpOffice\PhpWord\PhpWord();
            $phpWord->getSettings()->setThemeFontLang($language);
        }


            if ($distinct) {
                $language = new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::FR_FR);
                $phpWord  = new \PhpOffice\PhpWord\PhpWord();
                $phpWord->getSettings()->setThemeFontLang($language);
            }

            $phpWord->setDefaultFontSize(10);
            $phpWord->setDefaultFontName('Arial');

            $section = $phpWord->addSection(['headerHeight' => 0, 'footerHeight' => 0]);

            //$section = $phpWord->addSection();

            //$center      = ['align' => Cell::VALIGN_CENTER, 'spaceAfter' => 0];
           // $this->util->wordHeaderCOURRIER($phpWord, $section, 'portrait', null);


            $renderHtml = function ($html, $section) {
                \PhpOffice\PhpWord\Shared\Html::addHtml($section, str_replace("<br>", "<br />", $html));
            };


            $section->addTextBreak(0.5);

            $date      = date('d').' '.date('M').' '.date('Y');
            // = 'Chef de Projet';

            $textRun = $section->addTextRun(['spaceAfter' => 0]);
            $textRun->addText('                         
                                                                            Abidjan ,le : ');
            $textRun->addText($date, ['bold' => true,'align' => 'right']);

            $section->addTextBreak(0.5);
            $textRun = $section->addTextRun();

            $section->addTextBreak(0.8);
          /*  $section->addText('DONNE ORDRE A ', [
                'size'      => 14,
                'underline' => 'single',
                'bold'      => true,
                'allCaps'   => true,
                //'align' => 'center',
            ], ['align' => Cell::VALIGN_CENTER]);*/
            $bold = ['bold' => true];
            $section->addTextBreak(0.5);
            $textRun = $section->addTextRun(['spaceAfter' => 0]);
            $textRun->addText('N/Réf52/OF ');
            $textRun->addText($courrier->getNumero().' / '.date('Y'), ['bold' => true]);
            $textRun = $section->addTextRun(['spaceAfter' => 0]);
            $textRun->addText('RCK/TEO/ ');
            $textRun->addText($courrier->getNumero(), ['bold' => true]);
            $textRun = $section->addTextRun(['spaceAfter' => 0]);
            $textRun->addText('V/Réf ');
            $textRun->addText(' ', ['bold' => true]);
           /* $section->addText('DE SE RENDRE EN courrier ', [
                'size'      => 14,
                'underline' => 'single',
                'bold'      => true,
                'allCaps'   => true,
                //'align' => 'center',
            ], ['align' => Cell::VALIGN_CENTER]);*/
            $section->addTextBreak(0.8);

            $textRun = $section->addTextRun(['spaceAfter' => 0]);
            $textRun->addText('                                                         
                                    Monsieur/Madame l\'administrateur',['italic' => true]);

/*
            $textRun->addText(implode('; ', $localites), $bold);
*/          $data =$this->client->getClient($courrier->getDossier());
             $client="";
            if($data){
                if ($data[0]['raisonSocial'] == "") {
                    $client = $data[0]['nom']. ' ' . $data[0]['prenom'];
                } else {
                    $client =  $data[0]['raisonSocial'];
                }

            }

       // dd($client[0]['nom']);
            $textRun = $section->addTextRun(['spaceAfter' => 0]);
            $textRun->addText('                                                         
                                                    '.$client,$bold);
           // $textRun->addText($courrier->getNumero(), $bold);

            $textRun = $section->addTextRun(['spaceAfter' => 0.8]);
            $section->addTextBreak(0.5);
            $textRun = $section->addTextRun();

            $section->addTextBreak(0.8);
            $textRun->addText('                                                         
                                   Abidjan',$bold);
            $textRun->addText(' ', $bold);

            $textRun = $section->addTextRun(['spaceAfter' => 0]);
            $textRun->addText('AFFAIRE : ',['underline' => 'single','bold'=>true]);
            if($courrier->getDossier())
                $textRun->addText($courrier->getDossier()->getObjet() ? $courrier->getDossier()->getObjet(): '');


            $textRun = $section->addTextRun(['spaceAfter' => 0.5]);
            $section->addTextBreak(0.5);
            $textRun = $section->addTextRun();

            $section->addTextBreak(0.8);
            $textRun->addText('OBJET : ',['underline' => 'single','bold'=>true]);
            if($courrier)
                $textRun->addText($courrier->getObjet() ?: '');



            $textRun = $section->addTextRun(['spaceAfter' => 0.5]);
            $section->addTextBreak(0.5);
            $textRun = $section->addTextRun();
            $textRun->addText('                 Monsieur/Madame,');
            $textRun = $section->addTextRun(['spaceAfter' => 0.5]);
           /* $section->addTextBreak(0.5);
            $textRun = $section->addTextRun();*/

            $renderHtml($courrier->getCourrier(), $section);
            /*$text = strip_tags($courrier->getCourrier());
        $textRun->addText($text);*/


        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $fileName  = "Courrier" .'.docx';
        $objWriter->save($fileName);

        if ($download) {
            return $this->file($fileName);
        }

        return new Response();

    }

    private function generateWord($distinct = true, $download = false)
    {

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $header = ['size' => 16, 'bold' => true];

// 1. Basic table

        $rows = 10;
        $cols = 5;
        $section->addText('Basic table', $header);

        $table = $section->addTable();
        for ($r = 1; $r <= $rows; ++$r) {
            $table->addRow();
            for ($c = 1; $c <= $cols; ++$c) {
                $table->addCell(1750)->addText("Row {$r}, Cell {$c}");
            }
        }

// 2. Advanced table

        $section->addTextBreak(1);
        $section->addText('Fancy table', $header);

        $fancyTableStyleName = 'Fancy Table';
        $fancyTableStyle = ['borderSize' => 6, 'borderColor' => '006699', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER, 'cellSpacing' => 50];
        $fancyTableFirstRowStyle = ['borderBottomSize' => 18, 'borderBottomColor' => '0000FF', 'bgColor' => '66BBFF'];
        $fancyTableCellStyle = ['valign' => 'center'];
        $fancyTableCellBtlrStyle = ['valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR];
        $fancyTableFontStyle = ['bold' => true];
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
        $table = $section->addTable($fancyTableStyleName);
        $table->addRow(900);
        $table->addCell(2000, $fancyTableCellStyle)->addText('Row 1', $fancyTableFontStyle);
        $table->addCell(2000, $fancyTableCellStyle)->addText('Row 2', $fancyTableFontStyle);
        $table->addCell(2000, $fancyTableCellStyle)->addText('Row 3', $fancyTableFontStyle);
        $table->addCell(2000, $fancyTableCellStyle)->addText('Row 4', $fancyTableFontStyle);
        $table->addCell(500, $fancyTableCellBtlrStyle)->addText('Row 5', $fancyTableFontStyle);
        for ($i = 1; $i <= 8; ++$i) {
            $table->addRow();
            $table->addCell(2000)->addText("Cell {$i}");
            $table->addCell(2000)->addText("Cell {$i}");
            $table->addCell(2000)->addText("Cell {$i}");
            $table->addCell(2000)->addText("Cell {$i}");
            $text = (0 == $i % 2) ? 'X' : '';
            $table->addCell(500)->addText($text);
        }

        /*
         *  3. colspan (gridSpan) and rowspan (vMerge)
         *  ---------------------
         *  |     |   B    |    |
         *  |  A  |--------|  E |
         *  |     | C |  D |    |
         *  ---------------------
         */

        $section->addPageBreak();
        $section->addText('Table with colspan and rowspan', $header);

        $fancyTableStyle = ['borderSize' => 6, 'borderColor' => '999999'];
        $cellRowSpan = ['vMerge' => 'restart', 'valign' => 'center', 'bgColor' => 'FFFF00'];
        $cellRowContinue = ['vMerge' => 'continue'];
        $cellColSpan = ['gridSpan' => 2, 'valign' => 'center'];
        $cellHCentered = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
        $cellVCentered = ['valign' => 'center'];

        $spanTableStyleName = 'Colspan Rowspan';
        $phpWord->addTableStyle($spanTableStyleName, $fancyTableStyle);
        $table = $section->addTable($spanTableStyleName);

        $table->addRow();

        $cell1 = $table->addCell(2000, $cellRowSpan);
        $textrun1 = $cell1->addTextRun($cellHCentered);
        $textrun1->addText('A');
        $textrun1->addFootnote()->addText('Row span');

        $cell2 = $table->addCell(4000, $cellColSpan);
        $textrun2 = $cell2->addTextRun($cellHCentered);
        $textrun2->addText('B');
        $textrun2->addFootnote()->addText('Column span');

        $table->addCell(2000, $cellRowSpan)->addText('E', null, $cellHCentered);

        $table->addRow();
        $table->addCell(null, $cellRowContinue);
        $table->addCell(2000, $cellVCentered)->addText('C', null, $cellHCentered);
        $table->addCell(2000, $cellVCentered)->addText('D', null, $cellHCentered);
        $table->addCell(null, $cellRowContinue);

        /*
         *  4. colspan (gridSpan) and rowspan (vMerge)
         *  ---------------------
         *  |     |   B    |  1 |
         *  |  A  |        |----|
         *  |     |        |  2 |
         *  |     |---|----|----|
         *  |     | C |  D |  3 |
         *  ---------------------
         * @see https://github.com/PHPOffice/PHPWord/issues/806
         */

        $section->addPageBreak();
        $section->addText('Table with colspan and rowspan', $header);

        $styleTable = ['borderSize' => 6, 'borderColor' => '999999'];
        $phpWord->addTableStyle('Colspan Rowspan', $styleTable);
        $table = $section->addTable('Colspan Rowspan');

        $row = $table->addRow();
        $row->addCell(1000, ['vMerge' => 'restart'])->addText('A');
        $row->addCell(1000, ['gridSpan' => 2, 'vMerge' => 'restart'])->addText('B');
        $row->addCell(1000)->addText('1');

        $row = $table->addRow();
        $row->addCell(1000, ['vMerge' => 'continue']);
        $row->addCell(1000, ['vMerge' => 'continue', 'gridSpan' => 2]);
        $row->addCell(1000)->addText('2');

        $row = $table->addRow();
        $row->addCell(1000, ['vMerge' => 'continue']);
        $row->addCell(1000)->addText('C');
        $row->addCell(1000)->addText('D');
        $row->addCell(1000)->addText('3');

// 5. Nested table

        $section->addTextBreak(2);
        $section->addText('Nested table in a centered and 50% width table.', $header);

        $table = $section->addTable(['width' => 50 * 50, 'unit' => 'pct', 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER]);
        $cell = $table->addRow()->addCell();
        $cell->addText('This cell contains nested table.');
        $innerCell = $cell->addTable(['alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER])->addRow()->addCell();
        $innerCell->addText('Inside nested table');

// 6. Table with floating position

        $section->addTextBreak(2);
        $section->addText('Table with floating positioning.', $header);

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'position' => ['vertAnchor' => TablePosition::VANCHOR_TEXT, 'bottomFromText' => Converter::cmToTwip(1)]]);
        $cell = $table->addRow()->addCell();
        $cell->addText('This is a single cell.');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $fileName  = $this->getParameter('images_directory') .'12'. '.docx';
        $objWriter->save($fileName);

        if ($download) {
            return $this->file($fileName);
        }

        return new Response();

        }
       // }


    /**
     * @Route("/courier-depart/{id}/show", name="courierDepart_show", methods={"GET"})
     * @param CourierArrive $courierArrive
     * @param CourierArriveRepository $repository
     * @return Response
     */
    public function show($id,CourierArrive $courierArrive,CourierArriveRepository $repository): Response
    {
        //$type = $courierArrive->getType();

        $form = $this->createForm(CourierDepartType::class, $courierArrive, [

            'method' => 'POST',
            'action' => $this->generateUrl('courierDepart_show', [
                'id' => $courierArrive->getId(),
            ])
        ]);

        return $this->render('_admin/depart/voir.html.twig', [
            'titre'=>'DEPART',
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courier-depart/{id}/word", name="word", methods={"GET"})
     * @param CourierArrive $courierArrive
     * @param CourierArriveRepository $repository
     * @return Response
     */
    public function word(CourierArrive $courierArrive,CourierArriveRepository $repository): Response
    {
        //$type = $courierArrive->getType();
     return $this->generateFile($courierArrive,false, true);
    }



    /**
     * @Route("/courier-depart/new", name="courierDepart_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UploaderHelper $uploaderHelper
     * @param CourierArriveRepository $repository
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $em,FormError $formError, UploaderHelper $uploaderHelper,CourierArriveRepository $repository): Response
    {


        $courierArrive = new CourierArrive();
       /* $annee = date('y');
        $numero = $repository->getNumero('DEPART');

        $numero = $annee.'-'.$numero;*/
        $courierArrive->setNumero($repository->getNumeroIncrementation('DEPART'));
        $courierArrive->setExpediteur("KASSY ETUDER");


        $form = $this->createForm(CourierDepartType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('courierDepart_new')
        ]);


        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            $statut = 1;
            $redirect = $this->generateUrl('courierDepart');


        //    dd($brochureFile);
            if ($form->isValid()) {

                $courierArrive->setEtat("cree");
                $courierArrive->setType('DEPART');
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

        return $this->render('_admin/depart/new.html.twig', [
            'titre'=>'DEPART',
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courier-depart/{id}/edit", name="courierDepart_edit", methods={"GET","POST"})
     * @param Request $request
     * @param CourierArrive $courierArrive
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit($id,Request $request,FormError $formError, CourierArrive $courierArrive, EntityManagerInterface $em,CourierArriveRepository $repository): Response
    {

        $form = $this->createForm(CourierDepartType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('courierDepart_edit', [
                'id' => $courierArrive->getId(),
            ])
        ]);

        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            $redirect = $this->generateUrl('courierDepart');

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

        return $this->render('_admin/depart/edit.html.twig', [
            'titre'=>'DEPART',
            'data'=>$repository->getFichier($id),
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/accuse/{id}/edit", name="courierDepart_accuse_edit", methods={"GET","POST"})
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
            'titre'=>'DEPART',
            'data'=>$repository->getFichier($id),
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courier-depart/{id}/accuse", name="courierDepart_recep", methods={"GET","POST"})
     * @param Request $request
     * @param CourierArrive $courierArrive
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function accuse(Request $request, CourierArrive $courierArrive,FormError $formError, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CourierDepartType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('courierDepart_recep', [
                'id' => $courierArrive->getId(),
            ]
            )
        ]);

        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();
     //   $type = $form->getData()->getType();
        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('courierDepart');

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
     * @Route("/courier-depart/delete/{id}", name="courierDepart_delete", methods={"POST","GET","DELETE"})
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
                    'courierDepart_delete'
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

            $redirect = $this->generateUrl('courierDepart');

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
        return $this->render('_admin/depart/delete.html.twig', [
            'courierArrive' => $courierArrive,
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
     * @Route("/courier-depart/{id}/active", name="courierDepart_active", methods={"GET"})
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
     * @Route("/existe_depart", name="exsite_depart", methods={"GET","POST"})
     * @param CourierArriveRepository $repository
     * @param Request $request
     * @return Response
     */
    public function existeDepart(CourierArriveRepository $repository,Request $request): Response
    {
        $response = new Response();
        $format="";
        if ($request->isXmlHttpRequest()) {
            $nombre = $repository->getNombre();

            $date = date('y');


                $format = $date.'-'.$nombre.' '.'D';


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
