<?php
/**
 * @copyright: Copyright (C) 2016 Heliopsis. All rights reserved.
 * @license  : proprietary
 */

namespace Prism\PollBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Prism\PollBundle\Entity\Poll;

/**
 * Class PollService
 */
class PollService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param int $limit
     *
     * @return array
     */
    public function getLastPolls( $limit = 1 )
    {
        $pollRepository = $this->entityManager->getRepository( get_class( new Poll() ) );

        $polls = $pollRepository->findBy(
            array( 'published' => true, 'closed' => false ),
            array( 'createdAt' => 'DESC' ),
            $limit
        );

        return $polls;
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager( $entityManager )
    {
        $this->entityManager = $entityManager;
    }
}