<?php
/**
 * @copyright: Copyright (C) 2016 Heliopsis. All rights reserved.
 * @license  : proprietary
 */

namespace Prism\PollBundle\Export;

use Eovi\EspaceAdherentBundle\Model\Personne;
use League\Csv\Writer;
use SiteBundle\Entity\VotingProtectionEntity;

/**
 * Class PollResultsCsvWriter
 *
 * @package Prism\PollBundle\Export
 */
abstract class PollResultsCsvWriter
{
    /**
     * @var Writer
     */
    protected $csvWriter;

    function __construct( Writer $csvWriter, $insertHeaders = false )
    {
        $this->csvWriter = $csvWriter;
        if ( $insertHeaders )
        {
            $this->insertHeaders();
        }
    }

    abstract protected function insertHeaders();

    abstract public function addPollResult( $pollResult );
}