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
        $constrains['joinCond'] .= " AND ". $this->filter->getUsibiltyCriteria();
        if($this->filter->getUsibiltyCriteria() == " useful like 'NA'"){
            $constrains['user'] = 'fixed_by_user_uid';
        }
        else {
            $constrains['user'] = 'hidden_by_user_uid';
        }
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
        $constraints['joinCond'] .= $this->filter->getRecordVisibility();
        $joinMethod = $this->filter->getIncludeEmptyPages() ? 'rightJoin' : 'join';
        $data= $queryBuilder
                ->select(
                    'p.uid', $this->tableName.'.uid as recordUid',
                    'beUsers.realName', 'beUsers.email',  'p.title',
                    $this->tableName.'.date_hour',
                    $this->tableName.'.comment',
                    $this->tableName.'.useful',
                    $this->tableName.'.hidden_date',
                    $this->tableName.'.fixed_date',
                    $this->tableName.'.reason_short_label',
                    $this->tableName.'.url_orig',
                    $this->tableName.".deleted",
                    $this->tableName.'.fixed'

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
                    'beUsers.uid = '.$constraints["user"]
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
            $rows[$item['uid']]['records'][] = $item;
            $rows[$item['uid']]['title'] = $item['title'];
        }
        return $rows;
    }

    /**
     * This function is used to get the number of records by the depth for BE rendering verification
     * @param bool $hiddenComment
     * @return int
     */
    public function getListCount(string $constraint  = ''): int
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
        $constraints .= $this->date_criteria . ' ' . $this->lang_criteria . $constraint;
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
     * @param array $comments
     * @return array
     */
    public function getStatistics($page_ids, $limit,bool $showForHiddenPages = false, array $comments = []): array
    {
        $queryBuilder = $this->generateQueryBuilder();
        if($showForHiddenPages){
            $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        }
        $joinMethod = $this->filter->getIncludeEmptyPages() ? 'rightJoin' : 'join';
        $constraints = $this->getConstraints($page_ids, false);
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
                $constraints['joinCond'] . ' and hidden_comment = 0'
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
        if(!empty($comments)){
            foreach ($rows as $row){
                $comments[$row['page_uid']]['avg'] = $row['avg'];
                $comments[$row['page_uid']]['total_pos'] = $row['total_pos'];
                $comments[$row['page_uid']]['total'] = $row['total'];
            }
            return $comments;
        }
       return $rows;
    }


    /**
     * This function is used to get the number of technical problems by page
     * @param $pageUid
     * @return int|mixed
     */
    public function getCountTechnicalProblemsByPageUid($pageUid){
        $queryBuilder = $this->generateQueryBuilder();
        $data = $queryBuilder
            ->addSelectLiteral(
                $queryBuilder->expr()->count('uid_orig', 'technicalProblemsCount'),
            )
            ->from($this->tableName)

            ->where(
                "uid_orig = ".$pageUid . " and useful like 'NA' and fixed = 0"
            )
            ->groupBy('uid_orig');
        return $data->execute()
            ->fetchAssociative()['technicalProblemsCount'] ?? 0;
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
        $reason = str_replace("'", "\\'", $this->filter->getCommentReason());
        $data =  $queryBuilder
            ->select()
            ->addSelectLiteral(
                $queryBuilder->expr()->count('*', 'total'),
            )
            ->from($this->tableName)

            ->where(
                "uid_orig = ".$pageUid." and useful like '0' and reason_code like '".$reason. "'"
            );
        $reasonTotal = $data
            ->execute()
            ->fetchAllAssociative()[0]['total'];
        return  $total <= 0 ? 1 : $reasonTotal / $total;

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
        return $queryBuilder
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
