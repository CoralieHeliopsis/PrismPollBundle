<?php
/**
 * @copyright: Copyright (C) 2016 Heliopsis. All rights reserved.
 * @license  : proprietary
 */

namespace Prism\PollBundle\Export;

use League\Csv\AbstractCsv;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CsvFacade
 *
 * @package Prism\PollBundle\Export
 */
abstract class CsvFacade
{
    /**
     * @var string
     */
    protected $exportPath;

    /**
     * @var string
     */
    protected $archivedPath;

    /**
     * @param string $archivedPath
     */
    public function setArchivedPath( $archivedPath )
    {
        $this->archivedPath = $archivedPath;
    }

    /**
     * @param string $exportPath
     */
    public function setExportPath( $exportPath )
    {
        $this->exportPath = $exportPath;
    }

    /**
     * @param $pollId
     *
     * @return PollResultsCsvWriter
     */
    abstract public function getPollResultsWriter( $pollId );

    /**
     * @param $pollId
     *
     * @return Response
     */
    abstract public function getDownloadResponse( $pollId );

    /**
     * @param $fileName
     * @return \SplFileInfo
     */
    protected function getFileInfo( $fileName )
    {
        $fileInfo = new \SplFileInfo( $this->getFullPath( $fileName ) );
        $pathInfo = $fileInfo->getPathInfo();

        if ( $fileInfo->isFile() && !$fileInfo->isWritable() )
        {
            throw new \RuntimeException( sprintf( "Impossible de modifier le fichier %s", $fileInfo->getPathname() ) );
        }

        if( !$fileInfo->isFile() && !$pathInfo->isWritable() )
        {
            throw new \RuntimeException( sprintf( "Impossible de créer le fichier %s", $fileInfo->getPathname() ) );
        }

        return $fileInfo;
    }

    /**
     * Configure le format du CSV
     * @param AbstractCsv $csv
     */
    protected function configureCSVFormat( AbstractCsv $csv )
    {
        $csv->setDelimiter( ';' );
        $csv->setEnclosure( '"' );
    }

    /**
     * @param $fileName
     *
     * @return string
     */
    protected function getFullPath( $fileName )
    {
        return $this->exportPath . DIRECTORY_SEPARATOR . $fileName;
    }
}