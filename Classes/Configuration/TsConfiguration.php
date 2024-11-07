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

class TsConfiguration
{
    /**
     * @var array
     */
    protected array $tsConfig = [];


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

    public function getDateFormat(){
        return $this->tsConfig['csvExport.']['filename.']['dateFormat'] ?? 'YmdHi';
    }

    /**
     * @param $moduleName
     * @return string
     */
    public function getOrderType($moduleName) : string {
        if($this->tsConfig[$moduleName.'.']['orderType'] == null
            || !in_array(strtoupper($this->tsConfig[$moduleName.'.']['orderType']), ['ASC', 'DESC'])){
            return 'DESC';
        }
        return $this->tsConfig[$moduleName.'.']['orderType'];
    }


    /**
     * @param $moduleName
     * @return string
     */
    public function getMaxRecords($moduleName): string {
        if($this->tsConfig[$moduleName.'.']['maxRecords'] == null
            || !is_numeric($this->tsConfig[$moduleName.'.']['maxRecords'])){
            return '100';
        }
        return $this->tsConfig[$moduleName.'.']['maxRecords'];
    }

    /**
     * @return string
     */
    public function getNumberOfSubPages($moduleName) : string {
        if($this->tsConfig[$moduleName.'.']['numberOfSubPages'] == null
            || !is_numeric($this->tsConfig[$moduleName.'.']['numberOfSubPages'])){
            return '50';
        }
        return $this->tsConfig[$moduleName.'.']['numberOfSubPages'];
    }


    /**
     * @param $moduleName
     * @return bool
     */
    public function showForHiddenPage($moduleName): bool
    {
        return ($this->tsConfig[$moduleName.'.']['showRecordsForHiddenPages'] ?? false) == '1';
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return bool
     */
    public function isFixButtonEnabled(): bool {
        return ($this->tsConfig['technicalProblems.']['enableFixButton'] ?? false) == '1';
    }

    /**
     * @return bool
     */
    public function isRemoveButtonEnabled(): bool {
        return ($this->tsConfig['comments.']['enableRemoveButton'] ?? false) == '1';
    }

    /**
     * @param $section
     * @return bool
     */
    public function isDeleteButtonEnabled($section): bool {
        return ($this->tsConfig[$section.'.']['enableDeleteButton'] ?? false) == '1';
    }
}
