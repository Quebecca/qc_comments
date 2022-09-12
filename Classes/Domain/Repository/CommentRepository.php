<?php

namespace Qc\QcComments\Domain\Repository;

use Doctrine\DBAL\Connection as ConnectionAlias;
use Doctrine\DBAL\Driver\Exception;
use Qc\QcComments\Domain\Dto\Filter;
use Qc\QcComments\Traits\InjectTranslation;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class CommentRepository extends Repository
{
    use  InjectTranslation;
    protected int $root_id = 0;
    protected array $settings = [];
    protected string $tableName = 'tx_qccomments_domain_model_comment';
    protected Filter $filter;
    protected string $lang_criteria = '';
    protected string $date_criteria = '';
    /**
     * @param Filter $filter
     */
    public function setFilter(Filter $filter): void
    {
        $this->filter = $filter;
        $this->lang_criteria = $filter->getLangCriteria();
        $this->date_criteria = $filter->getDateCriteria();
    }

    public function generateQueryBuilder(): QueryBuilder
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($this->tableName);
    }

    /**
     * @param array $ids_list
     * @return string[]
     */
    public function getConstraints($ids_list = []): array
    {
        $constrains = [
            'joinCond' => '',
            'whereClause' => ''
        ];
        $ids_list = $ids_list ?: $this->getPageIdsList($this->filter->getDepth());

        $ids_csv = implode(',', $ids_list);
        $constrains['joinCond'] = " p.uid = uid_orig $this->date_criteria $this->lang_criteria";
        $constrains['whereClause'] = " p.uid in ($ids_csv)";
        return $constrains;
    }

    /**
     * This function is used to get pages comments for BE rendering and for export as well
     * QueryBuilder
     * @param array $ids_list
     * @param int|null $limit false id the function is called for export to export all comment, number for BE table
     * @return array
     * @throws Exception
     */
    public function getDataList($ids_list = [], $limit): array
    {
        $queryBuilder = $this->generateQueryBuilder();
        $constraints = $this->getConstraints($ids_list);
        $tr = [
            0 => $this->translate('negative'),
            1 => $this->translate('positive'),
        ];
        $joinMethod = $this->filter->getIncludeEmptyPages() ? 'rightJoin' : 'join';

        $data= $queryBuilder
                ->select('p.uid', 'p.title', 'date_houre', 'comment', 'useful')
                ->from($this->tableName)
                ->$joinMethod(
                    $this->tableName,
                    'pages',
                    'p',
                    $constraints['joinCond']
                )
                ->where(
                    $constraints['whereClause']
                );

        if($limit != false){
            $data = $data->setMaxResults($limit);
        }
        $data = $data
                ->execute()
                ->fetchAllAssociative();

        $rows = [];
        foreach ($data as $item) {
            $rows[$item['uid']][] = $item;
        }

        return $rows;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getListCount(): int
    {
        $ids_list = $this->getPageIdsList($this->filter->getDepth());
        $queryBuilder = $this->generateQueryBuilder();
        $constraints = $queryBuilder->expr()->in('uid_orig', $queryBuilder->createNamedParameter($ids_list, ConnectionAlias::PARAM_INT_ARRAY));
        $constraints .= $this->date_criteria . ' ' . $this->lang_criteria;
        $total = $queryBuilder
            ->count('*')
            ->from($this->tableName)
            ->where(
                $constraints
            )
            ->execute()
            ->fetchAssociative()['COUNT(*)'];
        return $total;
    }

    /**
     * This function is used to get pages statistics for BE rendering and for export as well
     * QueryBuilder
     * @param $page_ids
     * @param bool $limit
     * @return array
     * @throws Exception
     */
    public function getDataStats($page_ids, $limit): array
    {
        $queryBuilder = $this->generateQueryBuilder();
        $joinMethod = $this->filter->getIncludeEmptyPages() ? 'rightJoin' : 'join';
        $constraints = $this->getConstraints($page_ids);
        $data =  $queryBuilder
            ->select('p.uid as page_uid', 'p.title as page_title')
            ->addSelectLiteral(
                $queryBuilder->expr()->avg('useful', 'avg'),
                $queryBuilder->expr()->sum('useful', 'total_pos'),
                $queryBuilder->expr()->count('uid_orig', 'total'),
            )
            ->from($this->tableName)
            ->$joinMethod(
                $this->tableName,
                'pages',
                'p',
                $constraints['joinCond']
            )
            ->where(
                $constraints['whereClause']
            )
            ->groupBy('p.uid', 'p.title');

        if($limit != false)
            // we add one record to limit to verify if there are more than limit results
            $data = $data->setMaxResults($limit + 1);
        $data = $data
            ->execute()
            ->fetchAllAssociative();

        $rows = [];
        foreach ($data as $item) {
            $item['total_neg'] = $item['total'] - $item['total_pos'];
            $x =  $item['total_neg'] >  $item['total_pos'] ? - ((int)($item['total_neg']) - (int)($item['total_pos'])) :  $item['total_pos'];
            $item['avg'] = $item['total'] > 0 ?
                ' ' . number_format((($x) / $item['total']), 3) * 100 . ' %'
            : '0 %';
            $item['total_pos'] = $item['total_pos'] ?: '0';
            $rows[] = $item;
        }
        return $rows;
    }

    /**
     * @param $depth
     * @return array
     */
    public function getPageIdsList($depth): array
    {
        $page_ids = [];
        if ($depth > 0) {
            $page_ids = $this->getPageTreeIds($depth);
        }
      //  $page_ids[] = $this->root_id;
        return $page_ids;
    }

    /**
     * @param $depth
     * @return array
     */
    protected function getPageTreeIds($depth): array
    {
        $page_ids = [];
        if ($depth > 0) {
            /** @var PageTreeView $pageTree */
            $pageTree = GeneralUtility::makeInstance(PageTreeView::class);
            $pageTree->init('AND ' . $GLOBALS['BE_USER']->getPagePermsClause(1));
            $pageTree->makeHTML = 0;
            $pageTree->fieldArray = ['uid'];
            $pageTree->getTree($this->root_id, $depth);
            $page_ids = $pageTree->ids;
        }
        array_unshift($page_ids, $this->root_id);
        return $page_ids;
    }

    /**
     * @param int $root_id
     */
    public function setRootId(int $root_id): void
    {
        $this->root_id = $root_id;
    }

    /**
     * @param array $settings
     */
    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }
}
