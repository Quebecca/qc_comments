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

    public function getPageStatistics(): array
    {
            $pages_ids = $this->commentsRepository->getPageIdsList();
            $currentPageId = $this->root_id;
            $maxRecords = $this->userTS['statistics.']['maxRecords'];
            $resultData = $this->commentsRepository->getStatistics($pages_ids, $maxRecords);
            $formattedData = $this->statisticsDataFormatting($resultData);
            $tooMuchResults = count($resultData) > $maxRecords;
            $headers = $this->getHeaders();
           /* if ($tooMuchResults) {
                array_pop($resultData); // last line was there to check that limit has been reached
            }*/
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
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getStatisticsByDepth(){
        $pages_ids = $this->commentsRepository->getPageIdsList();

        $resultData = $this->commentsRepository->getStatistics($pages_ids,false);
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
        $avg = $avg / count($resultData). ' %';
        // Getting the number of comments
        $total_comment = $this->commentsRepository->getTotalNonEmptyComment();

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
        $headers['totalNonEmptyComments'] = $this->localizationUtility->translate(self::QC_LANG_FILE . 'stats.h.nonEmptyComment');

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
        foreach (['page_uid', 'page_title', 'total_pos', 'total_neg', 'total', 'avg'] as $col) {
            $headers[$col] = $this->localizationUtility->translate(self::QC_LANG_FILE . 'stats.h.' . $col);
        }
        return $headers;
    }

    public function statisticsDataFormatting($data) : array{
        $rows = [];
        foreach ($data as $item) {
            $item['total_neg'] = $item['total'] - $item['total_pos'];

            $total =  $item['total_neg'] >  $item['total_pos']
                ? - ((int)($item['total_neg']) - (int)($item['total_pos']))
                :  $item['total_pos'];

            $item['avg'] = $item['total'] > 0 ?
                ' ' . number_format((($total) / $item['total']), 3) * 100 . ' %'
                : '0 %';

            $item['total_pos'] = $item['total_pos'] ?: '0';

            $rows[] = $item;

        }
        return $rows;
    }


    /**
     * @param Filter $filter
     * @param int $currentPageId
     * @return ResponseInterface
     */
    public function exportStatisticsData(Filter  $filter, int $currentPageId): ResponseInterface
    {
        $pagesIds = $this->getPagesIds($filter, $currentPageId);

        $data = $this->commentsRepository->getStatistics($pagesIds,false);
        $formattedData = $this->statisticsDataFormatting($data);
        $headers = $this->getHeaders();

        // Resort the array elements for export
        $mappedData = [];
        $i = 0;
        foreach ($formattedData as $record) {
            foreach ($headers as $headerKey => $header) {
                $mappedData[$record['pages_uid']][$i][$headerKey] = $record[$headerKey];
            }
            $i++;
        }
        return parent::export($filter,$currentPageId,'stats', $headers, $mappedData);

    }


}
