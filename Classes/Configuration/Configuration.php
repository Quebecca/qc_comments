<?php
declare(strict_types=1);

/***
 *
 * This file is part of Qc Comments project.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 <techno@quebec.ca>
 *
 ***/
namespace Qc\QcComments\Configuration;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class Configuration
{
    /**
     * @var mixed[]
     */
    protected $tsConfig = [];


    public function __construct()
    {
        $this->loadPageTsConfig();
    }

    /**
     * Get the qc_comments modTSconfig
     */
    public function loadPageTsConfig(): void
    {
        $this->setTsConfig($this->getBackendUser()->getTSConfig()['mod.']['qcComments.'] ?? []);
    }

    /**
     * @param mixed[] $tsConfig
     */
    public function setTsConfig(array $tsConfig): void
    {
        $this->tsConfig = $tsConfig;
    }

    public function getCsvSeparator(){
        return $this->tsConfig['csvExport.']['separator'] ?? ',';
    }

    public function getCsvEnclosure(){
        return $this->tsConfig['csvExport.']['enclosure'] ?? '"';
    }

    public function getCsvEscape(){
        return $this->tsConfig['csvExport.']['escape'] ?? '\\';
    }

    public function getCsvDateFormat(){
        return $this->tsConfig['csvExport.']['filename.']['dateFormat'] ?? 'YmdHi';
    }

    public function getCommentsOrderType(){
        //@todo :  Vérifier si ASC ou DESC
        return $this->tsConfig['comments.']['orderType'] ?? 'DESC';
    }

    public function getCommentsMaxRecords(){
        // @todo : Il faut vérifier que c'est un nombre
        return $this->tsConfig['comments.']['maxRecords'] ?? '100';
    }

    public function getCommentsNumberOfSubPages(){
        return $this->tsConfig['comments.']['numberOfSubPages'] ?? '50';
    }

/*    public function getCommentsMaxCharacters(){
        return $this->tsConfig['comments']['maxCharacters'] ?? '10';
    }*/

    public function getStatisticsMaxRecords(){
        return $this->tsConfig['statistics.']['maxRecords'] ?? '30';
    }

    public function showStatisticsForHiddenPage(): bool
    {
        return $this->tsConfig['statistics.']['showStatisticsForHiddenPages'] == '1';
    }

    public function showCommentsForHiddenPage(): bool
    {
        return $this->tsConfig['comments.']['showCommentsForHiddenPages'] == '1';
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }


}