<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Form\EmployeType;
use App\Form\TypeActeType;
use App\Repository\EmployeRepository;
use App\Service\PaginationService;
use App\Service\Services;
use App\Entity\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/admin")
 * il s'agit du typeActe des module
 */
class EmployeController extends AbstractController
{
    /**
     * @Route("/employe", name="employe")
     * @param EmployeRepository $repository
     * @return Response
     */
    public function index(EmployeRepository $repository): Response
    {

        $pagination = $repository->findBy(['active'=>1]);
//dd($pagination);
        return $this->render('_admin/employe/index.html.twig', [
           'pagination'=>$pagination,
            'tableau'=>['email'=>'email'],
            'modal' => 'modal',

            'titre' => 'Liste des types actes',
        ]);
    }

    /**
     * @Route("/employe/new", name="employe_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface  $em): Response
    {
        $employe = new Employe();
        $form = $this->createForm(EmployeType::class,$employe, [
            'method' => 'POST',
            'action' => $this->generateUrl('employe_new')
        ]);
        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest();

        if($form->isSubmitted())
        {
            $response = [];
            $redirect = $this->generateUrl('employe');

           if($form->isValid()){
               $employe->setActive(1);
               $em->persist($employe);
               $em->flush();

               $message       = 'Opération effectuée avec succès';
               $statut = 1;
               $this->addFlash('success', $message);

           }
            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/employe/new.html.twig', [
           /* 'typeActe' => $typeActe,*/
            'form' => $form->createView(),
            'titre' => 'Type Acte',
        ]);
    }

    /**
     * @Route("/employe/{id}/edit", name="employe_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Type $typeActe
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Request $request,Type $typeActe, EntityManagerInterface  $em): Response
    {

        $form = $this->createForm(TypeActeType::class,$typeActe, [
            'method' => 'POST',
            'action' => $this->generateUrl('typeActe_edit',[
                'id'=>$typeActe->getId(),
            ])
        ]);
        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest();

        if($form->isSubmitted())
        {

            $response = [];
            $redirect = $this->generateUrl('typeActe');

            if($form->isValid()){
                $em->persist($typeActe);
                $em->flush();

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);

            }

            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/typeActe/edit.html.twig', [
            'typeActe' => $typeActe,
            'form' => $form->createView(),
            'titre' => 'Type Acte',
        ]);
    }

    /**
     * @Route("/employe/{id}/show", name="employe_show", methods={"GET"})
     * @param Employe $typeActe
     * @return Response
     */
    public function show(Employe $typeActe): Response
    {
        $form = $this->createForm(EmployeType::class,$typeActe, [
            'method' => 'POST',
            'action' => $this->generateUrl('typeActe_show',[
                'id'=>$typeActe->getId(),
            ])
        ]);

        return $this->render('_admin/employe/voir.html.twig', [
            'typeActe' => $typeActe,
            'titre' => 'Type Acte',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/employe/{id}/active", name="employe_active", methods={"GET"})
     * @param $id
     * @param Type $typeActe
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active($id,Type $typeActe, EntityManagerInterface $entityManager): Response
    {



        if ($typeActe->getActive() == 1){

            $typeActe->setActive(0);

        }else{

            $typeActe->setActive(1);

        }

        $entityManager->persist($typeActe);
        $entityManager->flush();
        return $this->json([
            'code'=>200,
            'message'=>'ça marche bien',
            'active'=>$typeActe->getActive(),
        ],200);


    }


    /**
     * @Route("/typeActe/delete/{id}", name="typeActe_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param Type $typeActe
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em,Type $typeActe): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'typeActe_delete'
                    ,   [
                        'id' => $typeActe->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->remove($typeActe);
            $em->flush();

            $redirect = $this->generateUrl('typeActe');

            $message = 'Opération effectuée avec succès';

            $response = [
                'statut'   => 1,
                'message'  => $message,
                'redirect' => $redirect,
            ];

            $this->addFlash('success', $message);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirect($redirect);
            } else {
                return $this->json($response);
            }



        }
        return $this->render('_admin/typeActe/delete.html.twig', [
            'typeActe' => $typeActe,
            'form' => $form->createView(),
        ]);
    }

}
