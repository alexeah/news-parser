<?php

namespace Np\NewsBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * FeedRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FeedRepository extends EntityRepository
{
    private function getItemCandidates()
    {
        $feeds = $this->findAll();
        $itemsCandidates = array();
        foreach ($feeds as $feed) {
            $itemsCandidates = array_merge($feed->getItemCandidates(), $itemsCandidates);
            $feed->setLastPullTime($_SERVER['REQUEST_TIME']);
        }
        $this->getEntityManager()->flush();
        return $itemsCandidates;
    }

    public function pull()
    {
        $itemsCandidates = $this->getItemCandidates();

        if (count($itemsCandidates)) {
            $this->getEntityManager()->getRepository('NpNewsBundle:FeedItem')->mergeItemCandidates($itemsCandidates);
        }
    }
}
