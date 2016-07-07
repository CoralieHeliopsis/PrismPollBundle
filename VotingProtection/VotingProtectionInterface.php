<?php

namespace Prism\PollBundle\VotingProtection;

use Prism\PollBundle\Entity\Opinion;
use Prism\PollBundle\Entity\Poll;

/**
 * @copyright: Copyright (C) 2016 Heliopsis. All rights reserved.
 * @license  : proprietary
 */
interface VotingProtectionInterface
{
    /**
     * @param Poll    $poll
     * @param Opinion $opinion
     *
     * @return mixed
     */
    public function addVotingProtection( Poll $poll, Opinion $opinion );

    /**
     * @param Poll $poll
     *
     * @return bool
     */
    public function hasVoted( Poll $poll );

    /**
     * @param Poll $poll
     *
     * @return array
     */
    public function getVotingProtections( Poll $poll );
}