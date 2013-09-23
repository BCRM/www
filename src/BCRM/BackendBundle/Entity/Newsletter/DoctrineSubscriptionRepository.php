<?php

namespace BCRM\BackendBundle\Entity\Newsletter;

use Doctrine\ORM\EntityRepository;
use PhpOption\None;
use PhpOption\Some;

class DoctrineSubscriptionRepository extends EntityRepository implements SubscriptionRepository
{
    /**
     * @param $email
     *
     * @return \PhpOption\Option|void
     */
    public function getSubscription($email)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->andWhere('s.email = :email')->setParameter('email', $email);
        $subscription = $qb->getQuery()->getOneOrNullResult();
        return $subscription === null ? None::create() : new Some($subscription);
    }

    /**
     * @return Subscription[]
     */
    public function getNewSubscriptions()
    {
        $qb = $this->createQueryBuilder('s');
        $qb->andWhere('s.confirmed=0');
        $qb->andWhere('s.confirmationKey IS NULL');
        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $id
     * @param string $key
     *
     * @return \PhpOption\Option
     */
    public function getSubscriptionByIdAndKey($id, $key)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->andWhere('s.id = :id')->setParameter('id', $id);
        $qb->andWhere('s.confirmationKey = :key')->setParameter('key', $key);
        $subscription = $qb->getQuery()->getOneOrNullResult();
        return $subscription === null ? None::create() : new Some($subscription);
    }
}
