<?php

namespace Qc\QcComments\Service;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use Qc\QcComments\Domain\Filter\Filter;
use TYPO3\CMS\Core\Context\Context;
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
        $this->showCommentsForHiddenPage = $this->tsConfiguration->showCommentsForHiddenPage();
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

        $maxRecords = $this->tsConfiguration->getCommentsMaxRecords();

        $numberOfSubPages = $this->tsConfiguration->getCommentsNumberOfSubPages();

        $orderType = $this->tsConfiguration->getCommentsOrderType();

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
     * This function is used to return the headers used in the exported file and the BE module table
     * @param false $include_csv_headers
     * @return array
     */
    protected function getHeaders(bool $include_csv_headers = false): array
    {
        $headers = [];

        foreach (['date_hour', 'description', 'type_problem','be_user_name', 'fix_date', ''] as $col) {
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

    /**
     * This function is used to mark the technical problem as solved
     * @param $recordUid
     * @return bool
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function markProblemAsFixed($recordUid) : bool
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $userBeUid = $context->getPropertyFromAspect('backend.user', 'id');
        $comment = $this->commentsRepository->findByUid($recordUid);
        if($comment != null){
            $comment->setUserUidFixingProblem($userBeUid);
            $comment->setFixingDate(date('Y-m-d H:i:s'));
            $comment->setDeleted(1);
            $this->updateComment($comment);
            $this->commentsRepository->persistenceManager->persistAll();
        }
        return true;
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
