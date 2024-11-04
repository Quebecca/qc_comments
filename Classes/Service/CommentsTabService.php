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
use Qc\QcComments\Domain\Filter\Filter;
use TYPO3\CMS\Core\Http\Response;

class CommentsTabService extends QcBackendModuleService
{
    /**
     * @var bool
     */
    protected bool $showCommentsForHiddenPage;

    protected const DEFAULT_ORDER_TYPES = 'DESC';

    public function __construct()
    {
        parent::__construct();
        $this->showCommentsForHiddenPage = $this->tsConfiguration->showForHiddenPage("comments");
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

        $maxRecords = $this->tsConfiguration->getMaxRecords("comments");

        $numberOfSubPages = $this->tsConfiguration->getNumberOfSubPages("comments");

        $orderType = $this->tsConfiguration->getOrderType("comments");

        $tooMuchPages = count($pages_ids) > $numberOfSubPages;
        $pages_ids = array_slice(
            $pages_ids,
            0,
            $numberOfSubPages
        );

        $commentsRecords = $this->commentsRepository
            ->getComments(
                $pages_ids,
                $maxRecords,
                $orderType,
                $this->showCommentsForHiddenPage
            );

        $commentsWithStats = $this->commentsRepository
            ->getStatistics($pages_ids, $maxRecords, $this->showCommentsForHiddenPage, $commentsRecords);
        $comments = $this->statisticsDataFormatting($commentsWithStats);

        $tooMuchResults = $this->commentsRepository->getListCount() > $maxRecords || $tooMuchPages;

        $pagesId = $pages_ids;
        $currentPageId = $this->root_id;
        $commentHeaders = $this->getHeaders();

        return compact(
            'commentHeaders',
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
    public function getFilterFromRequest($request): Filter
    {
        $filter = new CommentsFilter();
        $filter->setLang($request->getQueryParams()['parameters']['lang']);
        $filter->setDepth(intval($request->getQueryParams()['parameters']['depth']));
        $filter->setDateRange($request->getQueryParams()['parameters']['selectDateRange']);
        $filter->setStartDate($request->getQueryParams()['parameters']['startDate'] ?? '');
        $filter->setEndDate($request->getQueryParams()['parameters']['endDate'] ?? '');
        $filter->setIncludeEmptyPages(
            $request->getQueryParams()['parameters']['includeEmptyPages'] === 'true'
        );
        $filter->setUseful($request->getQueryParams()['parameters']['useful'] ?? '');
        $filter->setCommentReason($request->getQueryParams()['parameters']['commentReason'] ?? '');
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
              $filter = $this->backendSession->get('commentsFilter');
              if ($filter == null) {
                  $filter = new CommentsFilter();
              }
          } else {
              if ($filter->getDateRange() != 'userDefined') {
                  $filter->setStartDate(null);
                  $filter->setEndDate(null);
              }

              $this->backendSession->store('commentsFilter', $filter);
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
     * @param false $headersForExport
     * @return array
     */
    protected function getHeaders(bool $headersForExport = false): array
    {
        $headers = [];

        if ($headersForExport) {
            foreach (['page_uid', 'page_title','date_hour','reason', 'comment', 'useful','deleted'] as $col) {
                $headers[$col] = $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'comments.h.' . $col);
            }
        }
        else{
            foreach (['date_hour', 'comment', 'useful', 'comment_option', ''] as $col) {
                $headers[$col] = $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'comments.h.' . $col);
            }
        }
        return $headers;
    }

    /**
     * @param Filter $filter
     * @param int $currentPageId
     * @return Response
     */
    public function exportCommentsData(Filter  $filter, int $currentPageId): Response
    {
        $pagesIds = $this->getPagesIds($filter, $currentPageId);

        $data = $this->commentsRepository
            ->getComments(
                $pagesIds,
                false,
                self::DEFAULT_ORDER_TYPES, $this->showCommentsForHiddenPage
            );

        $headers = $this->getHeaders(true);
        $items = [];
        $i = 0;

        foreach ($data as $row) {
            foreach ($row['records'] as $item){
                $items[$i]['page_uid'] = $item['uid'];
                $items[$i]['page_title'] = $item['title'];
                $items[$i]['date_hour'] = $item['date_hour'];
                $items[$i]['reason'] = $item['reason_short_label'];
                $comment = str_replace("\r", ' ', $item['comment']) ;
                $comment = str_replace("\t", ' ', $comment);
                $items[$i]['comment'] = $comment;
                $items[$i]['useful'] = $item['useful'];
                $items[$i]['deleted'] = $item['deleted'] ?? '';
                $i++;
            }
        }

        return parent::export($filter,$currentPageId,'comments', $headers, $items);
    }

}
