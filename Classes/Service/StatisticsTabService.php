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
use Qc\QcComments\Domain\Filter\StatisticsFilter;
use TYPO3\CMS\Core\Http\Response;

class StatisticsTabService extends QcBackendModuleService
{

    protected StatisticsFilter $filter;
    protected bool $showStatisticsForHiddenPage;

    public function __construct()
    {
        parent::__construct();
        $this->showStatisticsForHiddenPage
            = $this->tsConfiguration->showForHiddenPage("statistics");
    }

    public function getPageStatistics(): array
    {
        $pages_ids = $this->commentsRepository->getPageIdsList();
        $currentPageId = $this->root_id;
        $maxRecords = $this->tsConfiguration->getMaxRecords("statistics");
        $resultData = $this->commentsRepository
                        ->getStatistics(
                            $pages_ids,
                            $maxRecords,
                            $this->showStatisticsForHiddenPage
                        );
        $formattedData = $this->statisticsDataFormatting($resultData);
        $tooMuchResults = count($resultData) > $maxRecords;
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
     * This function is used to format the statistics data and the avg of dissatisfaction by page and reason
     * @param $data
     * @param bool $exportRequest
     * @return array
     */
    public function statisticsDataFormatting($data, bool $exportRequest = false) : array {
        $rows = [];

        foreach ($data as $item) {
            $item['total_neg'] = $item['total'] - $item['total_pos'];
            $total = $item['total_pos'];
            $item['avg'] = $item['total'] > 0 ?
                ' ' . number_format((($total) / $item['total']), 2) * 100 . ' %'
                : '0 %';
            $item['total_pos'] = $item['total_pos'] ?: '0';
            if(!$exportRequest && $this->filter->getCommentReason() !== '%'){
                $avg = $this->commentsRepository->getDissatisfactionAvg($item['page_uid']);
                $item['dissatisfaction'] = number_format(($avg), 2) * 100 . ' %';
            }
            $rows[] = $item;
        }
        return $rows;
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
     * This function is used to generate a filter object from the ServerRequest
     * @param ServerRequestInterface $request
     * @return Filter
     */
    public function getFilterFromRequest($request): Filter
    {
        $filter = new StatisticsFilter();
        $filter->setLang($request->getQueryParams()['parameters']['lang']);
        $filter->setDepth(intval($request->getQueryParams()['parameters']['depth']));
        $filter->setDateRange($request->getQueryParams()['parameters']['selectDateRange']);
        $filter->setStartDate($request->getQueryParams()['parameters']['startDate'] ?? '');
        $filter->setEndDate($request->getQueryParams()['parameters']['endDate'] ?? '');
        $filter->setIncludeEmptyPages($request->getQueryParams()['parameters']['includeEmptyPages'] ?? false);
        return $filter;
    }

    /**
     * This function is used to get the filter from the backend session
     * @param Filter|null $filter
     * @return Filter|null
     */
    public function processFilter(Filter $filter = null): ?Filter
    {
        // Add filtering to records
        if ($filter === null) {
            // Get filter from session if available
            $filter = $this->backendSession->get('statisticsFilter');
            if ($filter == null) {
                $filter = new StatisticsFilter();
            }
        } else {
            if ($filter->getDateRange() != 'userDefined') {
                $filter->setStartDate(null);
                $filter->setEndDate(null);
            }

            $this->backendSession->store('statisticsFilter', $filter);
        }
        $this->commentsRepository->setFilter($filter);
        $this->commentsRepository->setRootId($this->root_id);
        $this->filter = $filter;
        return $filter;
    }


    /**
     * This function is used to return the headers used in the exported file and the BE module table
     * @param bool $headersForExport
     * @return array
     */
    protected function getHeaders(bool $headersForExport = false): array
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
        if($headersForExport){
            $columns[] = 'technicalProblems';
        }

        foreach ($columns as $col) {
            $headers[$col] = $this->localizationUtility
                ->translate(self::QC_LANG_FILE . 'stats.h.' . $col);
        }
        return $headers;
    }

    /**
     * @param Filter $filter
     * @param int $currentPageId
     * @return Response
     */
    public function exportStatisticsData(Filter  $filter): Response
    {
        $pagesIds = $this->getPagesIds($filter, $this->root_id);
        $data = $this->commentsRepository
            ->getStatistics(
                $pagesIds,
                false,
                $this->showStatisticsForHiddenPage
            );

        $formattedData = $this->statisticsDataFormatting($data, true);
        $headers = $this->getHeaders(true);
        // Resort the array elements for export
        $mappedData = [];
        $i = 0;
        foreach ($formattedData as $record) {
            foreach ($headers as $headerKey => $header) {
                $mappedData[$record['pages_uid'] ?? ''][$i][$headerKey] = $record[$headerKey] ?? '';
            }
            $pageUid = $mappedData[$record['pages_uid']][$i]['page_uid'];
            $mappedData[$record['pages_uid']][$i]['technicalProblems'] = $this->commentsRepository->getCountTechnicalProblemsByPageUid($pageUid);
            $i++;
        }

        return parent::export($filter,$this->root_id,'stats', $headers, $mappedData);
    }


}
