<?php
namespace Qc\QcComments\Domain\Repository;

use Doctrine\DBAL\Driver\Exception;
use Qc\QcComments\Domain\Dto\Filter;
use Qc\QcComments\Traits\InjectPDO;
use Qc\QcComments\Traits\InjectTranslation;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CommentsRepository
{
    use InjectPDO, InjectTranslation;
    protected int $root_id = 0;
    protected array $settings;
    protected string $tableName = 'tx_gabarit_pgu_form_comments_problems';
    /**
     * @param Filter $filter
     * @param array $ids_list
     * @param string $query_name
     * @return string
     */
    protected function getQueryStub(Filter $filter, $ids_list = [], $query_name = 'comments joins pages'): string
    {
        $ids_list = $ids_list ?: $this->getPageIdsList($filter->getDepth());
        $ids_csv = implode(',', $ids_list);
        $min_date = $filter->getDateForRange();
        $lang_criteria = $filter->getLangCriteria();
        $date_criteria = $filter->getDateCriteria();
        $join = $filter->getIncludeEmptyPages() ? 'left join' : 'join';
        return [
            'comments joins pages' => "
                select * from (
                      select %select
                        from pages p 
                            $join tx_gabarit_pgu_form_comments_problems comm 
                                on p.uid = uid_orig $date_criteria $lang_criteria 
                        where  
                              p.uid in ($ids_csv) 
                        %group_by
                        %limit

                ) a
                ",
            'comments count' => "select count(*) total
                from tx_gabarit_pgu_form_comments_problems comm 
                where uid_orig in ($ids_csv) $date_criteria $lang_criteria 
                ",
        ][$query_name];
    }


    public function generateQueryBuilder(): QueryBuilder
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($this->tableName);
    }


    /**
     * @param Filter $filter
     * @return array
     */
    public function setFilterForQuery(QueryBuilder  $queryBuilder, Filter $filter, $ids_list = [])  {
        $constrains = [
            'joinCond' => '',
            'whereClause' => ''
        ];
        $ids_list = $ids_list ?: $this->getPageIdsList($filter->getDepth());
        $ids_csv = implode(',', $ids_list);
        $lang_criteria = $filter->getLangCriteria();
        $date_criteria = $filter->getDateCriteria();

        $constrains['joinCond'] = " p.uid = uid_orig $date_criteria $lang_criteria";
        $constrains['whereClause'] = " p.uid in ($ids_csv)";

        /*
        "
                select * from (
                      select %select
                        from pages p
                            $join tx_gabarit_pgu_form_comments_problems comm
                                on p.uid = uid_orig $date_criteria $lang_criteria
                        where
                              p.uid in ($ids_csv)
                        %group_by
                        %limit

                ) a
                "

        */


        return $constrains;
    }


    /**
     * @param Filter $filter
     * @param int|null $limit
     * @return array
     * @throws Exception
     */
    public function getDataList(Filter $filter, int $limit = null): array
    {
        $queryBuilder = $this->generateQueryBuilder();
        $constraints = $this->setFilterForQuery($queryBuilder, $filter);
        debug($constraints);
        return $queryBuilder
            ->select('p.uid','p.title', 'date_heure', 'commentaire', 'utile')
            ->from($this->tableName)
            ->join(
                $this->tableName,
                'pages',
                'p',
                $constraints['joinCond']
            )
            ->where(
                $constraints['whereClause']
            )

            ->execute()
            ->fetchAllAssociative();
    }



    /**
     * @param Filter $filter
     * @param int $fetch_mode
     * @param false $limit
     * @param array $ids
     * @return array|false
     */
    public function getListData(Filter $filter, $fetch_mode = \PDO::FETCH_ASSOC, $limit = false, $ids = [])
    {
        $comments_limit = $limit ? 'limit ' . $this->settings['maxComments'] : '';
        $query = strtr($this->getQueryStub($filter, $ids), [
            '%select' => 'p.uid page_uid, p.title page_title,  date_heure, commentaire, utile',
            '%group_by' => '',
            "%limit" => $comments_limit,
        ]);
        $tr = [
            0 => $this->translate('negative'),
            1 => $this->translate('positive'),
        ];
        $stmt = $this->getPdo()->query($query);
        $rows = $stmt->fetchAll($fetch_mode | \PDO::FETCH_FUNC,
            function () use ($tr) {
                $args = func_get_args();
                $vals = array('page_uid', 'page_title', 'date_heure', 'commentaire', 'appreciation');
                if (count($args) < count($vals)) {
                    array_shift($vals);
                }
                $output = array_combine($vals, $args);
                $output['appreciation'] = $tr[$output['appreciation']];
                return $output;
            });

        return $rows;

    }

    /**
     * @param Filter $filter
     * @return int
     */
    public function getListCount(Filter $filter): int
    {
        $query = $this->getQueryStub($filter, [], 'comments count');
        $total = (int) $this->getPdo()->query($query)->fetch(\PDO::FETCH_COLUMN);
        return $total;
    }

    /**
     * @param Filter $filter
     * @param array $page_ids
     * @param bool $limit
     * @return array|false
     */
    public function getStatsData(Filter $filter, $page_ids = [], $limit = true)
    {
        $page_ids = $page_ids ?: $this->getPageIdsList($filter->getDepth());

        $query = strtr($this->getQueryStub($filter, $page_ids), [
            '%select' => 'p.uid page_uid, p.title page_title, ifNull(sum(utile), 0) total_pos, count(uid_orig)-ifNull(sum(utile), 0) total_neg, count(uid_orig) total, ifNull(avg(utile),0) avg',
            '%group_by' => 'group by p.uid, p.title',
            '%limit' => ($limit ? 'limit ' . ($this->settings['maxStats'] + 1) : '')
        ]);

        return $this->getPdo()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
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
        $page_ids[] = $this->root_id;
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