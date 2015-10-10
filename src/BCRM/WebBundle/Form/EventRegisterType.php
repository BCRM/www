<?php

namespace BCRM\WebBundle\Form;

use BCRM\BackendBundle\Entity\Event\Event;
use Dothiv\Bundle\MoneyFormatBundle\Service\MoneyFormatServiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventRegisterType extends AbstractType
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
        $days  = array(
            3 => sprintf('beide Tage (%s zzgl. Gebühren)', $this->moneyFormat->format(2 * $event->getPrice() / 100, 'de')),
            1 => sprintf('Samstag (%s zzgl. Gebühren)', $this->moneyFormat->format($event->getPrice() / 100, 'de')),
            2 => sprintf('Sonntag (%s zzgl. Gebühren)', $this->moneyFormat->format($event->getPrice() / 100, 'de'))
        );
        $payment = array(
            'paypal' => 'Mit Paypal (zzgl. 1,9% + 0,35 Cent)',
            // 'barzahlen.de' => 'Mit barzahlen.de (zzgl. 3,0% + 0,35 Cent)',
        );
        $builder
            ->setMethod('POST')
            ->add('email', 'email', array('label' => 'E-Mail', 'required' => true, 'attr' => array('placeholder' => 'z.B. "name@domain.de"')))
            ->add('name', 'text', array('label' => 'Name', 'required' => true, 'trim' => true, 'attr' => array('placeholder' => 'z.B. "Max Musterman"')))
            ->add('twitter', 'text', array('label' => 'Twitter-Handle', 'required' => false, 'trim' => true, 'attr' => array('placeholder' => 'z.B. "@max_mustermann"', 'pattern' => '@[a-zA-Z0-9_]{1,15}')))
            ->add('tags', 'text', array('label' => 'Beschreibe deine Interesse in maximal 3 Tags (je maximal 25 Zeichen)', 'required' => false, 'trim' => true, 'attr' => array('placeholder' => 'z.B. "#foo #bar"', 'pattern' => '#[^\s]{1,25}( #[^\s]{1,25}){0,2}')))
            ->add('days', 'choice', array('label' => 'An welchen Tagen möchtest Du am BarCamp teilnehmen?', 'required' => true, 'choices' => $days, 'expanded' => true))
            ->add('donationEur', 'text', array('label' => 'Freiwillige Spende? (in €)', 'required' => false, 'pattern' => '^[0-9]+(,[0-9]{2})?$'))
            // ->add('payment', 'choice', array('label' => 'Wie möchtest Du bezahlen?', 'required' => true, 'choices' => $payment, 'expanded' => true))
            ->add('payment', 'hidden', array('required' => true))
            ->add('food', 'choice', array('label' => 'Möchtest Du veganes Essen?', 'required' => true, 'choices' => array('default' => 'nein', 'vegan' => 'ja'), 'expanded' => true))
            ->add('arrival', 'choice', array('label' => 'Wie wirst Du anreisen?', 'required' => true, 'choices' => array('public' => 'ÖPNV', 'private' => 'Privat'), 'expanded' => true))
            ->add('participantList', 'choice', array('label' => 'Auf der Teilnehmerliste anzeigen?', 'required' => true, 'choices' => array(1 => 'ja', 0 => 'nein'), 'expanded' => true))
            ->add('save', 'submit', array('label' => 'Registrierung überprüfen'));
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
