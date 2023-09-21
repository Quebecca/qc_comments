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

class StatisticsTabService extends QcBackendModuleService
{

    protected bool $showStatisticsForHiddenPage;

    public function __construct()
    {
        parent::__construct();
        $this->showStatisticsForHiddenPage
            = $this->tsConfiguration->showStatisticsForHiddenPage();
    }

    public function getPageStatistics(): array
    {
        $pages_ids = $this->commentsRepository->getPageIdsList();
        $currentPageId = $this->root_id;
        $maxRecords = $this->tsConfiguration->getStatisticsMaxRecords();
        $resultData = $this->commentsRepository
                        ->getStatistics(
                            $pages_ids,
                            $maxRecords,
                            $this->showStatisticsForHiddenPage
                        );
        $formattedData = $this->statisticsDataFormatting($resultData);
        $tooMuchResults = count($resultData) > $maxRecords && $this->showStatisticsForHiddenPage;
        $headers = $this->getHeaders();
        return [
            'tooMuchResults' => $tooMuchResults,
            'maxRecords' => $maxRecords,
            'headers' => $headers,
            'rows' => $formattedData,
            'pagesId' => $pages_ids,
            'settings',
            'currentPageId' => $currentPageId
        ];

    }


    /**
     * This function is used to get the statistics data by depth
     * @return array
     */
    public function getStatisticsByDepth(): array
    {
        $pages_ids = $this->commentsRepository->getPageIdsList();
        $resultData = $this->commentsRepository
                        ->getStatistics(
                            $pages_ids,
                            false,
                            $this->showStatisticsForHiddenPage
                        );
        $data = $this->statisticsDataFormatting($resultData);
        $avg = 0;
        $total_pos = 0;
        $total_neg = 0;
        $total = 0;
        $page_title = '';
        foreach ($data as $item){
            if($item['page_uid'] == $this->root_id){
                $page_title = $item['page_title'];
            }
            $itemAvg = trim(' ', $item['avg']);
            $itemAvg = floatval(str_replace('%', '', $item['avg']));
            $avg += $itemAvg;
            $total_pos += intval($item['total_pos']);
            $total_neg += intval($item['total_neg']);
           $total += $item['total'];
        }
        $itemLength = count($resultData) > 0 ? count($resultData) : 1;
        $avg = number_format(($avg / $itemLength), 1). ' %';
        // Getting the number of comments
        $total_comment = $this->commentsRepository
                            ->getTotalNonEmptyComment($this->showStatisticsForHiddenPage);

        $result = compact(
            'page_title',
            'total_pos',
            'total_neg',
            'total',
            'avg',
            'total_comment'
        );
        $result['page_uid'] = $this->root_id;

        $headers = $this->getHeaders();
        $headers['totalNonEmptyComments'] = $this->localizationUtility
                                        ->translate(self::QC_LANG_FILE . 'stats.h.nonEmptyComment');

        return [
            'headers' => $headers,
            'row' => $result
        ];
    }

    /**
     * This function is used to return the headers used in the exported file and the BE module table
     * @return array
     */
    protected function getHeaders(): array
    {
        $headers = [];
        $columns = [
            'page_uid',
            'page_title',
            'total_pos',
            'total_neg',
            'total',
            'avg'
        ];
        foreach ($columns as $col) {
            $headers[$col] = $this->localizationUtility
                ->translate(self::QC_LANG_FILE . 'stats.h.' . $col);
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
        $data = $this->commentsRepository
            ->getStatistics(
                $pagesIds,
                false,
                $this->showStatisticsForHiddenPage
            );
        $formattedData = $this->statisticsDataFormatting($data);
        $headers = $this->getHeaders();

        // Resort the array elements for export
        $mappedData = [];
        $i = 0;
        foreach ($formattedData as $record) {
            foreach ($headers as $headerKey => $header) {
                $mappedData[$record['pages_uid'] ?? ''][$i][$headerKey] = $record[$headerKey] ?? '';
            }
            $i++;
        }
        return parent::export($filter,$currentPageId,'stats', $headers, $mappedData);

    }


}
