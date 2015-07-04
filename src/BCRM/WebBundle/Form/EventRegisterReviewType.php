<?php

namespace BCRM\WebBundle\Form;

use BCRM\BackendBundle\Entity\Event\Event;
use Dothiv\Bundle\MoneyFormatBundle\Service\MoneyFormatServiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventRegisterReviewType extends AbstractType
{
    /**
     * @var MoneyFormatServiceInterface
     */
    private $moneyFormat;

    /**
     * @param MoneyFormatServiceInterface $moneyFormat
     */
    public function __construct(MoneyFormatServiceInterface $moneyFormat)
    {
        $this->moneyFormat = $moneyFormat;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var EventRegisterModel $model */
        $model = $builder->getData();
        /** @var Event $event */
        $event = $model->event;
        $builder
            ->setMethod('POST')
            ->add('norefund', 'choice', array('label' => 'Mir ist bekannt, dass ich die Registrierungsgebühr nicht zurück erstattet bekomme.', 'required' => true, 'choices' => array(1 => 'ja', 0 => 'nein'), 'expanded' => true))
            ->add('save', 'submit', array('label' => 'Registrierung bestätigen'));
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
        return 'event_register_review';
    }

}
