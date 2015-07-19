<?php

namespace BCRM\WebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            ->add('email', 'email', array('label' => 'E-Mail', 'required' => true, 'attr' => array('placeholder' => 'name@domain.de')))
            ->add('name', 'text', array('label' => 'Name', 'required' => true, 'trim' => true, 'attr' => array('placeholder' => 'Max Musterman')))
            ->add('twitter', 'text', array('label' => 'Twitter-Handle', 'required' => false, 'trim' => true, 'attr' => array('placeholder' => '@max_mustermann', 'pattern' => '@[a-zA-Z0-9_]{1,15}')))
            ->add('tags', 'text', array('label' => 'Beschreibe deine Interesse in maximal 3 Tags (je 15 Zeichen)', 'required' => false, 'trim' => true, 'attr' => array('placeholder' => '#foo #bar', 'pattern' => '#[^\s]{1,15}( #[^\s]{1,15}){0,2}')))
            ->add('days', 'choice', array('label' => 'An welchen Tagen möchtest Du am BarCamp teilnehmen?', 'required' => true, 'choices' => array(3 => 'beide Tage', 1 => 'Samstag', 2 => 'Sonntag'), 'expanded' => true))
            ->add('food', 'choice', array('label' => 'Möchtest Du veganes Essen?', 'required' => true, 'choices' => array('default' => 'nein', 'vegan' => 'ja'), 'expanded' => true))
            ->add('arrival', 'choice', array('label' => 'Wie wirst Du anreisen?', 'required' => true, 'choices' => array('public' => 'ÖPNV', 'private' => 'Privat'), 'expanded' => true))
            ->add('participantList', 'choice', array('label' => 'Auf der Teilnehmerliste anzeigen?', 'required' => true, 'choices' => array(1 => 'ja', 0 => 'nein'), 'expanded' => true))
            ->add('save', 'submit', array('label' => 'Absenden'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => 'BCRM\WebBundle\Form\EventRegisterModel',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'event_register';
    }

}
