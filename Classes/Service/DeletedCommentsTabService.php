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

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use Qc\QcComments\Domain\Filter\CommentsFilter;
use Qc\QcComments\Domain\Filter\DeletedCommentsFilter;
use Qc\QcComments\Domain\Filter\Filter;
use Psr\Http\Message\ServerRequestInterface;

class DeletedCommentsTabService extends QcBackendModuleService
{
    /**
     * @var bool
     */
    protected bool $showCommentsForHiddenPage;

    protected const DEFAULT_ORDER_TYPES = 'DESC';

    public function __construct()
    {
        parent::__construct();
        $this->showCommentsForHiddenPage = $this->tsConfiguration->showForHiddenPage("deletedComments");
    }

    /**
     * This function is used to get the list of comments in BE module
     * @param Filter|null $filter
     * @throws Exception
     * @throws DBALException
     */
    public function getComments(Filter $filter = null): array
    {
        $pages_ids = $this->commentsRepository->getPageIdsList();

        $maxRecords = $this->tsConfiguration->getMaxRecords("deletedComments");

        $numberOfSubPages = $this->tsConfiguration->getNumberOfSubPages("deletedComments");

        $orderType = $this->tsConfiguration->getOrderType("deletedComments");

        $tooMuchPages = count($pages_ids) > $numberOfSubPages;
        $pages_ids = array_slice(
            $pages_ids,
            0,
            $numberOfSubPages
        );
        $stats = $this->commentsRepository
            ->getStatistics($pages_ids, $maxRecords, $this->showCommentsForHiddenPage);

        $comments = $this->commentsRepository
            ->getComments(
                $pages_ids,
                $maxRecords,
                $orderType,
                $this->showCommentsForHiddenPage
            );

        $stats = $this->statisticsDataFormatting($stats);

        $tooMuchResults = $this->commentsRepository->getListCount() > $maxRecords
                            || $tooMuchPages;
        $pagesId = $pages_ids;
        $currentPageId = $this->root_id;
        $commentHeaders = $this->getHeaders();

        return compact(
            'commentHeaders',
            'stats',
            'comments',
            'pagesId',
            'currentPageId',
            'tooMuchResults',
            'numberOfSubPages',
            'maxRecords'
        );

    }

    /**
     * This function is used to generate a filter object from the ServerRequest
     * @param ServerRequestInterface $request
     * @return Filter
     */
    public function getFilterFromRequest(ServerRequestInterface $request): Filter
    {
        $filter = new DeletedCommentsFilter();
        $filter->setLang($request->getQueryParams()['parameters']['lang']);
        $filter->setDepth(intval($request->getQueryParams()['parameters']['depth']));
        $filter->setDateRange($request->getQueryParams()['parameters']['selectDateRange']);
        $filter->setStartDate($request->getQueryParams()['parameters']['startDate'] ?? '');
        $filter->setEndDate($request->getQueryParams()['parameters']['endDate'] ?? '');
        $filter->setIncludeEmptyPages(
            $request->getQueryParams()['parameters']['includeEmptyPages'] === 'true'
        );
        $filter->setUseful($request->getQueryParams()['parameters']['useful'] ?? '');
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
              $filter = $this->backendSession->get('deletedCommentsFilter');
              if ($filter == null) {
                  $filter = new CommentsFilter();
              }
          } else {
              if ($filter->getDateRange() != 'userDefined') {
                  $filter->setStartDate(null);
                  $filter->setEndDate(null);
              }

              $this->backendSession->store('deletedCommentsFilter', $filter);
          }
          $this->commentsRepository->setFilter($filter);
          $this->commentsRepository->setRootId($this->root_id);
          return $filter;
    }

    /**
     * @return bool
     */
    public function isDeleteButtonEnabled() : bool {
        return $this->tsConfiguration->isDeleteButtonEnabled();
    }

    /**
     * This function is used to return the headers used in the exported file and the BE module table
     * @param false $include_csv_headers
     * @return array
     */
    protected function getHeaders(bool $include_csv_headers = false): array
    {
        $headers = [];
        foreach (['date_hour', 'comment', 'useful', 'comment_option', 'deleted_by', 'deleted_on'] as $col) {
            $headers[$col] = $this->localizationUtility
                ->translate(self::QC_LANG_FILE . 'comments.h.' . $col);
        }
        if ($include_csv_headers) {
            $headers = array_merge([
                'page_uid' => $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'csv.h.page_uid'),
                'page_title' => $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'stats.h.page_title'),
            ], $headers);
        }
        return $headers;
    }

    public function deleteComment($commentUid) {

        $this->commentsRepository->deleteComment($commentUid);
    }

    /**
     * @param Filter $filter
     * @param int $currentPageId
     * @return ResponseInterface
     */
    public function exportCommentsData(Filter  $filter, int $currentPageId): ResponseInterface
    {
        $pagesIds = $this->getPagesIds($filter, $currentPageId);

        $data = $this->commentsRepository
            ->getComments(
                $pagesIds,
                false,
                self::DEFAULT_ORDER_TYPES, $this->showCommentsForHiddenPage
            );

        $headers = $this->getHeaders(true);
        foreach ($data as $row) {
            array_walk($row, function (&$field) {
                $field = str_replace("\r", ' ', $field);
                $field = str_replace("\n", ' ', $field);
            });
        }
        return parent::export($filter,$currentPageId,'comments', $headers, $data);
    }

}
