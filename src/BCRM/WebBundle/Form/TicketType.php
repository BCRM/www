<?php

namespace BCRM\WebBundle\Form;

use BCRM\BackendBundle\Entity\Event\Registration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            ->add('email', 'email', array('label' => 'E-Mail', 'required' => true, 'attr' => array('placeholder' => 'name@domain.de')))
            ->add('name', 'text', array('label' => 'Name', 'required' => true, 'attr' => array('placeholder' => 'Max Musterman')))
            ->add('tags', 'text', array('label' => 'Tags', 'required' => false, 'attr' => array('placeholder' => '#foo #bar', 'pattern' => '#[^\s]{1,15}( #[^\s]{1,15}){0,2}')))
            ->add('saturday', 'checkbox', array('label' => 'Samstag', 'required' => false))
            ->add('sunday', 'checkbox', array('label' => 'Sonntag', 'required' => false))
            ->add('type', 'choice', array('label' => 'Typ', 'required' => true, 'choices' => array(Registration::TYPE_VIP => 'VIP', Registration::TYPE_SPONSOR => 'Sponsor'), 'expanded' => true))
            ->add('save', 'submit', array('label' => 'Absenden', 'attr' => array('class' => 'btn-primary')));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BCRM\BackendBundle\Entity\Event\Registration',
        ));
    }

    public function getName()
    {
        return 'registration';
    }

}
