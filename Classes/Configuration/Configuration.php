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

    /**
     * @return string
     */
    public function getCsvSeparator() : string {
        return $this->tsConfig['csvExport.']['separator'] ?? ',';
    }

    /**
     * @return string
     */
    public function getCsvEnclosure() : string {
        return $this->tsConfig['csvExport.']['enclosure'] ?? '"';
    }

    /**
     * @return string
     */
    public function getCsvEscape() : string{
        return $this->tsConfig['csvExport.']['escape'] ?? '\\';
    }

    public function getCsvDateFormat(){
        return $this->tsConfig['csvExport.']['filename.']['dateFormat'] ?? 'YmdHi';
    }

    /**
     * @return string
     */
    public function getCommentsOrderType() : string {
        if($this->tsConfig['comments.']['orderType'] == null
            || !in_array(strtoupper($this->tsConfig['comments.']['orderType']), ['ASC', 'DESC'])){
            return 'DESC';
        }
        return $this->tsConfig['comments.']['orderType'];
    }

    /**
     * @return string

     */
    public function getCommentsMaxRecords(): string {
        if($this->tsConfig['comments.']['maxRecords'] == null
            || !is_numeric($this->tsConfig['comments.']['maxRecords'])){
            return '100';
        }
        return $this->tsConfig['comments.']['maxRecords'];
    }

    /**
     * @return string
     */
    public function getCommentsNumberOfSubPages() : string {
        if($this->tsConfig['comments.']['numberOfSubPages'] == null
            || !is_numeric($this->tsConfig['comments.']['numberOfSubPages'])){
            return '50';
        }
        return $this->tsConfig['comments.']['numberOfSubPages'];
    }

    /**
     * @return string
     */
    public function getStatisticsMaxRecords(): string {
        if($this->tsConfig['statistics.']['maxRecords'] == null
            || !is_numeric($this->tsConfig['statistics.']['maxRecords'])){
            return '30';
        }
        return $this->tsConfig['statistics.']['maxRecords'];
    }

    /**
     * @return bool
     */
    public function showStatisticsForHiddenPage(): bool
    {
        return $this->tsConfig['statistics.']['showStatisticsForHiddenPages'] == '1';
    }

    /**
     * @return bool
     */
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