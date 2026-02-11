<?php

namespace Qc\QcComments\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Qc\QcComments\Configuration\TsConfiguration;
use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Domain\Repository\CommentRepository;
use Qc\QcComments\Domain\Session\BackendSession;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class QcBackendModuleService
{
    /**
     * @var LocalizationUtility
     */
    protected LocalizationUtility $localizationUtility;

    /**
     * @var BackendSession
     */
    protected BackendSession $backendSession;

    /**
     * @var CommentRepository
     */
    protected CommentRepository $commentsRepository;

    protected PersistenceManagerInterface $persistenceManager;

    /**
     * @var int|mixed
     */
    protected $root_id;

    protected TsConfiguration $tsConfiguration;

    const QC_LANG_FILE = 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:';

    public function injectCommentRepository(CommentRepository $commentsRepository)
    {
        $this->commentsRepository = $commentsRepository;
    }

    public function __construct(){
        $this->localizationUtility
            = GeneralUtility::makeInstance(LocalizationUtility::class);
        $this->backendSession
            = GeneralUtility::makeInstance(BackendSession::class);
        $this->tsConfiguration
            = GeneralUtility::makeInstance(TsConfiguration::class);
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
    }

    /**
     * This function is used to get the filter from the backend session
     * @param Filter|null $filter
     * @return Filter|null
     */
    public function processFilter(Filter $filter = null): ?Filter
    {
        return null;
    }


    /**
     * @param Filter $filter
     * @param $fileName
     * @param $dateFormat
     * @param $pageId
     * @return string
     */
    protected function getFilename(Filter $filter, $fileName, $dateFormat, $pageId): string
    {
        $format = $dateFormat;
        if($filter->getDateRange() == 'userDefined'){
            $from = date($format,strtotime($filter->getStartDate()));
            $now = date($format,strtotime($filter->getEndDate()));
        }
        else{
            $now = date(
                $format,
                strtotime(
                    '-'.$filter->getDateRange(),
                    strtotime(date($format))
                )
            );
        }

        return implode('-', array_filter([
                $this->localizationUtility->translate(self::QC_LANG_FILE . $fileName),
                $filter->getLang(),
                'uid-' . $pageId,
                $from ?? '',
                $now,
            ])) . '.xlsx';
    }


    /**
     * @param Filter $filter
     * @param int $currentPageId
     * @param string $fileName
     * @param array $headers
     * @param array $data
     * @return Response
     */
    public function export(
        Filter $filter,
        int $currentPageId,
        string $fileName,
        array $headers,
        array $data
    ): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($fileName);
        $sheet->fromArray($headers, NULL, 'A1');
        $rowIndex = 2;
        foreach ($data as $row) {
            $sheet->fromArray($row, NULL, 'A'.$rowIndex, true);
            $rowIndex++;
        }
        $writer = new Xlsx($spreadsheet);
         $dateFormat =$this->tsConfiguration->getDateFormat();
        $fileName = $this->getFilename($filter, $fileName, $dateFormat, $currentPageId);
          header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
          header("Content-Disposition: attachment;filename=\"$fileName\"");
          $writer->save("php://output");
          return new Response(
              'php://output',
              200,
              ['Content-Type' => 'application/vnd.ms-excel',
                  'Content-Description' => 'File transfer',
                  'Content-Disposition' => 'attachment; filename="' . $fileName
              ]
          );
    }

    /**
     * This function is used to get the pages IDs
     * @param Filter $filter
     * @param int $currentPageId
     * @return int[]
     */
    public function getPagesIds(Filter $filter,int $currentPageId): array
    {
        $this->commentsRepository->setRootId($currentPageId);
        $this->commentsRepository->setFilter($filter);
        $pagesData = $this->commentsRepository->getPageIdsList();
        if($filter->getDepth() == 0){
            $pagesData = [$currentPageId];
        }
        return $pagesData;
    }

    /**
     * This function is used to format the statistics data
     * @param $data
     * @param bool $exportRequest
     * @return array
     */
    public function statisticsDataFormatting($data, bool $exportRequest = false) : array{
        $rows = [];
        foreach ($data as $key => $item) {
            $item['total_neg'] = $item['total'] - $item['total_pos'];
            $total = $item['total_pos'];
            $item['avg'] = $item['total'] > 0 ?
                ' ' . number_format((($total) / $item['total']), 2) * 100 . ' %'
                : '0 %';

            $rows[$key] = $item;
        }
        return $rows;
    }


    /**
     * This function is used to mark the technical problem as solved (deleted = 1)
     * @param $recordUid
     * @return bool
     * @throws AspectNotFoundException
     */
    public function deleteComment($recordUid) : bool
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $userBeUid = $context->getPropertyFromAspect('backend.user', 'id');
        $comment = $this->commentsRepository->findByUid($recordUid);
        if($comment != null){
            $comment->setDeletedByUserUid($userBeUid);
            $comment->setDeletingDate(date('Y-m-d H:i:s'));
            $comment->setDeleted(1);
            $this->updateComment($comment);
            $this->persistenceManager->persistAll();
        }
        return true;
    }



    /**
     * This function is used to mark the comment as (removed = 1)
     * @param $recordUid
     * @return bool
     * @throws AspectNotFoundException
     */
    public function hideComment($recordUid) : bool
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $userBeUid = $context->getPropertyFromAspect('backend.user', 'id');
        $comment = $this->commentsRepository->findByUid($recordUid);
        if($comment != null){
            $comment->setHiddenByUserUid($userBeUid);
            $comment->setHiddenDate(date('Y-m-d H:i:s'));
            $comment->setHiddenComment(1);
            $this->updateComment($comment);
            $this->persistenceManager->persistAll();
        }
        return true;
    }



    public function getComment($commentUid){
        return $this->commentsRepository->findByUid($commentUid);
    }

    public function updateComment($comment){
        $this->commentsRepository->update($comment);
    }
    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return BackendSession
     */
    public function getBackendSession(): BackendSession
    {
        return $this->backendSession;
    }

    /**
     * @param int|mixed $root_id
     */
    public function setRootId($root_id): void
    {
        $this->root_id = $root_id;
    }

    /**
     * @return bool
     */
    public function isRemoveButtonEnabled() : bool {
        return $this->tsConfiguration->isRemoveButtonEnabled();
    }

    /**
     * @param $section
     * @return bool
     */
    public function isDeleteButtonEnabled($section) : bool {
        return $this->tsConfiguration->isDeleteButtonEnabled($section);
    }
}
