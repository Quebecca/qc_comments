<?php
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
namespace Qc\QcComments\Domain\Repository;

use Doctrine\DBAL\Connection as ConnectionAlias;
use Doctrine\DBAL\Driver\Exception;
use Qc\QcComments\Domain\Filter\Filter;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class CommentRepository extends Repository
{
    public $persistenceManager;
    /**
     * @var int
     */
    protected int $root_id = 0;

    /**
     * @var array
     */
    protected array $settings = [];

    /**
     * @var string
     */
    protected string $tableName = 'tx_qccomments_domain_model_comment';

    /**
     * @var Filter
     */
    protected Filter $filter;

    /**
     * @var string
     */
    protected string $lang_criteria = '';

    /**
     * @var string
     */
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

    /**
     * @return QueryBuilder
     */
    public function generateQueryBuilder(): QueryBuilder
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($this->tableName);
    }


    /**
     * This function is used to get the SQL constraints for the comments and statistics queries
     * @param array $page_ids
     * @param bool $usefulCond
     * @return string[]
     */
    public function getConstraints(array $page_ids, bool $usefulCond = true): array
    {
        $constrains = [
            'joinCond' => '',
            'whereClause' => ''
        ];
        $ids_list = $page_ids ?: $this->getPageIdsList();

        $ids_csv = implode(',', $ids_list);
        $constrains['joinCond'] = " p.uid = uid_orig $this->date_criteria $this->lang_criteria";
        $constrains['whereClause'] = " p.uid in ($ids_csv)";
        /*if($usefulCond === true){
            $usefulCond = $this->filter->getUseful() != ''
               ?  "useful like '" . $this->filter->getUseful()."'"
                : '';
            if ($usefulCond != '') {
                $constrains['whereClause'] .= " AND $usefulCond";
            }
        }*/
        $constrains['whereClause'] .= " AND ". $this->filter->getUsibiltyCriteria();
        return $constrains;
    }

    /**
     * This function is used to get pages comments for BE rendering and for export as well
     * @param array $pages_ids
     * @param string $limit
     * @param string $orderType
     * @param bool $showForHiddenPages
     * @return array
     */
    public function getComments(
        array $pages_ids,
        string $limit,
        string $orderType,
        bool $showForHiddenPages = false
    ): array
    {
        $queryBuilder = $this->generateQueryBuilder();
        if($showForHiddenPages === true){
            $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        }

        $constraints = $this->getConstraints($pages_ids);

        if($this->filter->getRecordVisbility() === true){
            $queryBuilder->getRestrictions()->removeByType(DeletedRestriction::class);
        }
        $joinMethod = $this->filter->getIncludeEmptyPages() ? 'rightJoin' : 'join';
        $data= $queryBuilder
                ->select(
                    'p.uid', $this->tableName.'.uid as recordUid',
                    'beUsers.realName', 'beUsers.email',  'p.title', 'date_hour', 'comment', 'useful', 'fixing_date',
                    'reason_short_label', $this->tableName.".deleted"

                )
                ->from($this->tableName)
                ->$joinMethod(
                    $this->tableName,
                    'pages',
                    'p',
                    $constraints['joinCond']
                )
                ->leftJoin(
                    $this->tableName,
                    'be_users',
                    'beUsers',
                    'beUsers.uid = user_uid_fixing_problem'
                )
                ->where(
                    $constraints['whereClause']
                );

        if ($limit) {
            $data = $data->setMaxResults($limit);
        }
        $data = $data
                ->orderBy('date_hour', $orderType)
                ->execute()
                ->fetchAllAssociative();
        $rows = [];
        foreach ($data as $item) {
            $rows[$item['uid']][] = $item;
        }
        return $rows;
    }

    /**
     * This function is used to get the number of records by the depth for BE rendering verification
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    public function getListCount(): int
    {
        $ids_list = $this->getPageIdsList();
        $queryBuilder = $this->generateQueryBuilder();
        $constraints = $queryBuilder->expr()
            ->in(
                'uid_orig',
                $queryBuilder->createNamedParameter(
                    $ids_list,
                    ConnectionAlias::PARAM_INT_ARRAY
                )
            );
        $constraints .= $this->date_criteria . ' ' . $this->lang_criteria;
        return $queryBuilder
            ->count('*')
            ->from($this->tableName)
            ->where(
                $constraints
            )
            ->execute()
            ->fetchAssociative()['COUNT(*)'];
    }


    /**
     * @param $uid
     * @return void
     */
    public function deleteComment($uid) : void {
        $queryBuilder = $this->generateQueryBuilder();
        $queryBuilder
            ->update($this->tableName)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid))
            )
            ->set('deleted', 1)
            ->executeStatement();
    }

    /**
     * This function is used to get pages statistics for BE rendering and for export as well
     * @param $page_ids
     * @param $limit
     * @param false $showForHiddenPages
     * @return array
     */
    public function getStatistics($page_ids, $limit,bool $showForHiddenPages = false): array
    {
        $queryBuilder = $this->generateQueryBuilder();
        if($showForHiddenPages){
            $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        }
        $joinMethod = $this->filter->getIncludeEmptyPages() ? 'rightJoin' : 'join';
        $constraints = $this->getConstraints($page_ids, false);
        //@todo : only for comments and problems module
        $queryBuilder->getRestrictions()->removeByType(DeletedRestriction::class);
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

        if ($limit) {
            // we add one more record to check if there are more than rendering results
            $data = $data->setMaxResults($limit + 1);
        }
        $rows =  $data
            ->execute()
            ->fetchAllAssociative();
        // Check fo reason to get the dissatisfation result
        if(get_class($this->filter) == "Qc\QcComments\Domain\Filter\StatisticsFilter"){
            if($this->filter && $this->filter->getCommentReason() !== ""){


            }
        }
        return $rows;
    }

    /**
     * This function is used to get the average dissatisfaction by page
     * @param $pageUid
     * @return float
     */
    public function getDissatisfactionAvg($pageUid) : float{
        $queryBuilder = $this->generateQueryBuilder();
        $queryBuilder->getRestrictions()->removeByType(DeletedRestriction::class);

        $data =  $queryBuilder
            ->select()
            ->addSelectLiteral(
                $queryBuilder->expr()->count('*', 'total'),
            )
            ->from($this->tableName)

            ->where(
                "uid_orig = ".$pageUid." and useful like '0'"
            );
        $total = $data
            ->execute()
            ->fetchAllAssociative()[0]['total'];


        $queryBuilder = $this->generateQueryBuilder();
        $queryBuilder->getRestrictions()->removeByType(DeletedRestriction::class);

        $data =  $queryBuilder
            ->select()
            ->addSelectLiteral(
                $queryBuilder->expr()->count('*', 'total'),
            )
            ->from($this->tableName)

            ->where(
                "uid_orig = ".$pageUid." and useful like '0' and reason_short_label like '".$this->filter->getCommentReason(). "'"
            );
        $reasonTotal = $data
            ->execute()
            ->fetchAllAssociative()[0]['total'];

        return $reasonTotal / $total;
    }

    /**
     * This function is used to get the total number of non-empty comments
     * @param false $showForHiddenPages
     * @return mixed
     */
    public function getTotalNonEmptyComment(bool $showForHiddenPages = false): mixed
    {
        $pages_ids = $this->getPageIdsList();
        $constraints = $this->getConstraints($pages_ids, false);
        $constraints['whereClause'] .= " AND trim($this->tableName.comment) <> ''";
        $joinMethod = $this->filter->getIncludeEmptyPages() ? 'rightJoin' : 'join';
        $queryBuilder = $this->generateQueryBuilder();
        if($showForHiddenPages){
            $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        }
        return  $queryBuilder
            ->count($this->tableName.'.uid')
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
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * Getting pages uid based on the selected depth
     * @return array
     */
    public function getPageIdsList(): array
    {
      //  $this->filter = new Filter();
        $depth = $this->filter->getDepth();
        $page_ids = [];
        if ($depth > 0) {
            $page_ids = $this->getPageTreeIds($depth);
        }
        if ($this->filter->getDepth() == 0) {
            $page_ids[] = $this->root_id;
        }
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
