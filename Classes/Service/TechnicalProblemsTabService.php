<?php

namespace Qc\QcComments\Service;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Domain\Filter\TechnicalProblemsFilter;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TechnicalProblemsTabService extends QcBackendModuleService
{
    /**
     * @var bool
     */
    protected bool $showCommentsForHiddenPage;

    protected const DEFAULT_ORDER_TYPES = 'DESC';

    public function __construct()
    {
        parent::__construct();
        $this->showCommentsForHiddenPage = $this->tsConfiguration->showForHiddenPage("technicalProblems");
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

        $maxRecords = $this->tsConfiguration->getMaxRecords("technicalProblems");

        $numberOfSubPages = $this->tsConfiguration->getNumberOfSubPages("technicalProblems");

        $orderType = $this->tsConfiguration->getOrderType("technicalProblems");

        $tooMuchPages = count($pages_ids) > $numberOfSubPages;
        $pages_ids = array_slice(
            $pages_ids,
            0,
            $numberOfSubPages
        );

        $records = $this->commentsRepository
            ->getComments(
                $pages_ids,
                $maxRecords,
                $orderType,
                $this->showCommentsForHiddenPage
            );
        $comments = $records['rows'];
        $tooMuchResults = $records['count'] > $maxRecords;
        $pagesId = $pages_ids;
        $currentPageId = $this->root_id;
        $commentHeaders = $this->getHeaders();

        return compact(
            'commentHeaders',
            'comments',
            'pagesId',
            'currentPageId',
            'tooMuchResults',
            'tooMuchPages',
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
        $filter = new TechnicalProblemsFilter();
        $filter->setLang($request->getQueryParams()['parameters']['lang']);
        $filter->setDepth(intval($request->getQueryParams()['parameters']['depth']));
        $filter->setDateRange($request->getQueryParams()['parameters']['selectDateRange']);
        $filter->setStartDate($request->getQueryParams()['parameters']['startDate'] ?? '');
        $filter->setEndDate($request->getQueryParams()['parameters']['endDate'] ?? '');
        $filter->setIncludeFixedTechnicalProblems($request->getQueryParams()['parameters']['includeFixedTechnicalProblems'] ?? false);
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
            $filter = $this->backendSession->get('technicalProblemsFilter');
            if ($filter == null) {
                $filter = new TechnicalProblemsFilter();
            }
        } else {
            if ($filter->getDateRange() != 'userDefined') {
                $filter->setStartDate(null);
                $filter->setEndDate(null);
            }

            $this->backendSession->store('technicalProblemsFilter', $filter);
        }
        $this->commentsRepository->setFilter($filter);
        $this->commentsRepository->setRootId($this->root_id);
        return $filter;
    }

    /**
     * @return bool
     */
    public function isFixButtonEnabled() : bool {
        return $this->tsConfiguration->isFixButtonEnabled();
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
           foreach (['page_uid','page_title', 'date_hour', 'reason', 'comment', 'url_orig', 'fixed', 'fixed_by', 'fix_date'] as $col) {
               $headers[$col] = $this->localizationUtility
                   ->translate(self::QC_LANG_FILE . 'comments.h.' . $col);
           }
        }
       else{
           foreach (['date_hour', 'description', 'type_problem','fixed_by', 'fix_date', ''] as $col) {
               $headers[$col] = $this->localizationUtility
                   ->translate(self::QC_LANG_FILE . 'comments.h.' . $col);
           }
       }
        return $headers;
    }

    /**
     * @param Filter $filter
     * @return Response
     */
    public function exportTechnicalProblemsData(Filter  $filter): Response
    {
        $pagesIds = $this->getPagesIds($filter, $this->root_id);

        $data = $this->commentsRepository
            ->getComments(
                $pagesIds,
                false,
                self::DEFAULT_ORDER_TYPES, $this->showCommentsForHiddenPage
            )['rows'];

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
                // Do not export the url parameters
                $items[$i]['url_orig'] = explode('?', $item['url_orig'])[0];
                $items[$i]['fixed'] = $item['fixed'];
                $items[$i]['realName'] = $item['realName'] ?: $item['email'] ?: $item['username'];
                $items[$i]['fixed_date'] = $item['fixed_date'];

                $i++;
            }
        }
        return parent::export($filter,$this->root_id,'technicalProblems', $headers, $items);
    }

    /**
     * @param $problemUid
     * @return true
     * @throws AspectNotFoundException
     */
    public function markProblemAsFixed($problemUid){
        $context = GeneralUtility::makeInstance(Context::class);
        $userBeUid = $context->getPropertyFromAspect('backend.user', 'id');
        $comment = $this->commentsRepository->findByUid($problemUid);
        if($comment != null){
            $comment->setFixedByUserUid($userBeUid);
            $comment->setFixedDate(date('Y-m-d H:i:s'));
            $comment->setFixed(1);
            $this->updateComment($comment);
            $this->persistenceManager->persistAll();
        }
        return true;
    }

}
