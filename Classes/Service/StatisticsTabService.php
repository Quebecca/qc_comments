<?php

namespace Qc\QcComments\Service;

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


use Psr\Http\Message\ResponseInterface;
use Qc\QcComments\Domain\Filter\Filter;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

class StatisticsTabService extends QcBackendModuleService
{

    public function getPageStatistics()
    {
            $pages_ids = $this->commentsRepository->getPageIdsList();
            $currentPageId = $this->root_id;
            $maxRecords = $this->userTS['statistics.']['maxRecords'];
            $resultData = $this->commentsRepository->getStatistics($pages_ids, $maxRecords);
            $tooMuchResults = count($resultData) > $maxRecords;
           /* if ($tooMuchResults) {
                array_pop($resultData); // last line was there to check that limit has been reached
            }*/
            return [
                'tooMuchResults' => $tooMuchResults,
                'maxRecords' => $maxRecords,
                'headers' => $this->getHeaders(),
                'rows' => $resultData,
                'pagesId' => $pages_ids,
                'settings',
                'currentPageId' => $currentPageId
            ];

    }

    /**
     * This function is used to return the headers used in the exported file and the BE module table
     * @return array
     */
    protected function getHeaders(): array
    {
        $headers = [];
        foreach (['page_uid', 'page_title', 'total_pos', 'total_neg', 'total', 'avg'] as $col) {
            $headers[$col] = $this->localizationUtility->translate(self::QC_LANG_FILE . 'stats.h.' . $col);
        }
        return $headers;
    }


    /**
     * @param Filter $filter
     * @param int $currentPageId
     * @return ResponseInterface
     */
    public function exportStatisticsData(Filter  $filter, int $currentPageId): ResponseInterface
    {
        $pagesIds = $this->getPagesIds($filter, $currentPageId);
        $data = $this->commentsRepository->getStatistics($pagesIds, false);
        $headers = $this->getHeaders();

        // Resort the array elements for export
        $mappedData = [];
        $i = 0;
        foreach ($data as $record) {
            foreach ($headers as $headerKey => $header) {
                $mappedData[$record['pages_uid']][$i][$headerKey] = $record[$headerKey];
            }
            $i++;
        }
        return parent::export($filter,$currentPageId,'stats', $headers, $mappedData);

    }


}
