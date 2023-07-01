<?php

namespace App\Service;
use App\Entity\Dossier;
use Doctrine\ORM\EntityManagerInterface;
use PPCA\MissionBundle\Entity\Localite;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DossierListener implements EventSubscriberInterface
{
    /**
     * @var mixed
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT  => 'onPreSubmit',
            FormEvents::PRE_SET_DATA => 'onPreSetData'
        ];
    }



    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $formName = $form->getName();

        $localites = $data->getDossier();

        foreach ($localites as $localite) {

        }
    }

   

    /**
     * @param FormEvent $event
     * @return null
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $formName = $form->getName();

        $localites = isset($data['localites']) ? $data['localites'] : [];


        $repLocalite = $this->em->getRepository(Dossier::class);
       
        foreach ($localites as &$localite) {

            if (!($_localite = $replocalite->exists($localite['localite']))) {
                $_localite = new localite();
                $_localite->setLibelle($localite['localite']);
                $this->em->persist($_localite);
                $id = $_localite->getid();
                //$this->em->flush();
            } else {
                $id = $_localite['id'];
            }

            $localite['localite'] = $id;

        }

        $data['localites'] = $localites;

        

        $this->em->flush();

        unset($localite);

        $event->setData($data);
    }

}
