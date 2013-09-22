<?php

namespace BCRM\WebBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NewsletterSubscribeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array('label' => 'E-Mail', 'required' => true, 'attr' => array('placeholder' => 'name@domain.de')))
            ->add('futurebarcamps', 'checkbox', array('label' => 'Informiert mich auch über zukünftige BarCamps', 'required' => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BCRM\WebBundle\Form\NewsletterSubscribeModel',
        ));
    }

    public function getName()
    {
        return 'newsletter_subscribe';
    }

}