<?php

namespace Qc\QcComments\Service;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcComments\Configuration\TsConfiguration;
use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Domain\Repository\CommentRepository;
use Qc\QcComments\Domain\Session\BackendSession;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

abstract class QcBackendModuleService
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
        $this->localizationUtility = GeneralUtility::makeInstance(LocalizationUtility::class);
        $this->backendSession = GeneralUtility::makeInstance(BackendSession::class);
        $this->tsConfiguration = GeneralUtility::makeInstance(TsConfiguration::class);
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
            $filter = $this->backendSession->get('filter');
            if ($filter == null) {
                $filter = new Filter();
            }
        } else {
            if ($filter->getDateRange() != 'userDefined') {
                $filter->setStartDate(null);
                $filter->setEndDate(null);
            }

            $this->backendSession->store('filter', $filter);
        }
        $this->commentsRepository->setFilter($filter);
        $this->commentsRepository->setRootId($this->root_id);
        return $filter;
    }


    /**
     * @param Filter $filter
     * @param $fileName
     * @param $csvDateFormat
     * @param $pageId
     * @return string
     */
    protected function getCSVFilename(Filter $filter, $fileName, $csvDateFormat, $pageId): string
    {
        $format = $csvDateFormat;
        if($filter->getDateRange() == 'userDefined'){
            $from = date($format,strtotime($filter->getStartDate()));
            $now = date($format,strtotime($filter->getEndDate()));
        }
        else{
            $now = date($format, strtotime('-'.$filter->getDateRange(), strtotime(date($format))));
        }

        return implode('-', array_filter([
                $this->localizationUtility->translate(self::QC_LANG_FILE . $fileName),
                $filter->getLang(),
                'uid-' . $pageId,
                $from,
                $now,
            ])) . '.csv';
    }


    /**
     * @param Filter $filter
     * @param int $currentPageId
     * @param string $fileName
     * @param array $headers
     * @param array $data
     * @return ResponseInterface
     */
    public function export(Filter $filter, int $currentPageId,string $fileName,array $headers, array $data): ResponseInterface
    {
        $separator = $this->tsConfiguration->getCsvSeparator();
        $enclosure = $this->tsConfiguration->getCsvEnclosure();
        $escape = $this->tsConfiguration->getCsvEscape();
        $csvDateFormat =$this->tsConfiguration->getCsvDateFormat();

        $fileName = $this->getCSVFilename($filter, $fileName, $csvDateFormat, $currentPageId);

        $response = new Response(
            'php://output',
            200,
            ['Content-Type' => 'text/csv; charset=utf-8',
                'Content-Description' => 'File transfer',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ]
        );

        $fp = fopen('php://output', 'wb');
        // BOM utf-8 pour excel
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, $headers, $separator, $enclosure, $escape);
        foreach ($data as $row) {
            foreach ($row as $item) {
                fputcsv($fp, $item, $separator, $enclosure, $escape);
            }
        }
        //  rewind($fp);
        rtrim(stream_get_contents($fp), "\n");
        fclose($fp);
        return $response;
    }

    /**
     * This function is used to generate a filter object from the ServerRequest
     * @param ServerRequestInterface $request
     * @return Filter
     */
    public function getFilterFromRequest(ServerRequestInterface $request): Filter
    {
        $filter = new Filter();
        $filter->setLang($request->getQueryParams()['parameters']['lang']);
        $filter->setDepth(intval($request->getQueryParams()['parameters']['depth']));
        $filter->setDateRange($request->getQueryParams()['parameters']['selectDateRange']);
        $filter->setStartDate($request->getQueryParams()['parameters']['startDate']);
        $filter->setEndDate($request->getQueryParams()['parameters']['endDate']);
        $filter->setIncludeEmptyPages($request->getQueryParams()['parameters']['includeEmptyPages'] === 'true');
        return $filter;
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
     * @return array
     */
    public function statisticsDataFormatting($data) : array{
        $rows = [];
        foreach ($data as $item) {
            $item['total_neg'] = $item['total'] - $item['total_pos'];
            $total = $item['total_pos'];
            $item['avg'] = $item['total'] > 0 ?
                ' ' . number_format((($total) / $item['total']), 2) * 100 . ' %'
                : '0 %';

            $item['total_pos'] = $item['total_pos'] ?: '0';

            $rows[] = $item;

        }
        return $rows;
    }

    abstract protected function getHeaders(): array;

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
}