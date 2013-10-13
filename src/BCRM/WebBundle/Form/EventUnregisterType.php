<?php

namespace BCRM\WebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventUnregisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            ->add('email', 'email', array('label' => 'E-Mail', 'required' => true, 'attr' => array('placeholder' => 'name@domain.de')))
            ->add('days', 'choice', array('label' => 'Welche Tagen möchtest Du stornieren?', 'required' => true, 'choices' => array(3 => 'beide Tage', 1 => 'Samstag', 2 => 'Sonntag'), 'expanded' => true))
            ->add('save', 'submit', array('label' => 'Absenden'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => 'BCRM\WebBundle\Form\EventUnregisterModel',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'event_unregister';
    }

}
