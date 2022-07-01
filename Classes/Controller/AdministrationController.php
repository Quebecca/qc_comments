<?php

namespace Qc\QcComments\Controller;

use Qc\QcComments\Domain\Dto\Filter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcComments\Traits\InjectPDO;
use Qc\QcComments\Traits\InjectTranslation;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Fluid\View\StandaloneView;


class AdministrationController extends QcBackendModuleActionController
{
    use InjectPDO, InjectTranslation;

    protected $tableName = 'tx_gabarit_pgu_form_comments_problems';
    /**
     * @var int|mixed
     */
    protected $root_id;

    /**
     * @var Icon
     */
    protected $icon;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Set up the doc header properly here
     *
     * @param ViewInterface $view
     * @return void
     * @throws RouteNotFoundException
     */
    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
        $moduleTemplate = $view->getModuleTemplate();
        if ($this->root_id && $moduleTemplate) {
            $record = BackendUtility::readPageAccess($this->root_id, $this->getBackendUser()->getPagePermsClause(1));
            $moduleTemplate->getDocHeaderComponent()->setMetaInformation($record);

            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/DateTimePicker');

            $this->pageRenderer->addCssFile('EXT:qc_comments/Resources/Public/Css/qc_comments.css');
        }
    }

    /**
     * Function will be called before every other action
     *
     * @return void
     * @throws StopActionException
     */
    public function initializeAction()
    {
        $this->root_id = GeneralUtility::_GP('id');

        parent::initializeAction();
    }

    public function initializeStatsAction()
    {
        $this->sharedPreChecks();
    }

    /**
     * @throws NoSuchArgumentException
     */
    public function initializeListAction()
    {
        if (!isset($this->settings['dateFormat'])) {
            $this->settings['dateFormat'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?: 'd-m-Y';
        }
        if (!isset($this->settings['timeFormat'])) {
            $this->settings['timeFormat'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
        }

        $constraintConfiguration = $this->arguments->getArgument('filter')->getPropertyMappingConfiguration();
        $constraintConfiguration->allowAllProperties();
        $this->sharedPreChecks();
    }

    protected function sharedPreChecks()
    {
        $this->forwardIfNoPageSelected();
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->icon = $this->iconFactory->getIcon('actions-document-export-csv', Icon::SIZE_SMALL);

    }

    protected function setMenu()
    {
        if (!$this->root_id) {
            return;
        }
        // Define menu items
        $this->setMenuIdentifier('commentsMenu');
        $menuItems = [
            [
                'label' => $this->translate('menu.stats'),
                'action' => 'stats',
                'controller' => 'Administration'
            ],
            [
                'label' => $this->translate('menu.list'),
                'action' => 'list',
                'controller' => 'Administration'
            ],

        ];
        $this->setMenuItems($menuItems);
    }

    /**
     * @param Filter|null $filter // we need to specify the filter class in the argument to prevent map error
     * @return void
     */
    public function listAction(Filter $filter = null)
    {
        $filter = $this->processFilter($filter);

        $csvButton = [
            'href' => $this->getUrl('exportList'),
            'icon' => $this->icon,
        ];

        $resetButton = [
            'href' => $this->getUrl('resetFilter')
        ];
        $tooMuchPages = false;
        $tooMuchComments = $this->getListCount($filter) > $this->settings['maxComments'];
        $pages_ids = $this->getPageIdsList($filter->getDepth());
        if (count($pages_ids) > $this->settings['maxStats'] && $filter->getIncludeEmptyPages()) {
            $tooMuchPages = true;
            $pages_ids = array_slice($pages_ids, 0, $this->settings['maxStats']);
        }
        $stats = $this->getStatsData($filter, $pages_ids, true);
        $tooMuchPages = $tooMuchPages ?: count($stats) > $this->settings['maxStats'];
        $pages_ids = array_map(function ($row) {
            return $row['page_uid'];
        }, $stats);
        if ($tooMuchComments | $tooMuchPages) {
            $message = $this->translate('tooMuchResults', [$this->settings['maxStats'], $this->settings['maxComments']]);
            $this->addFlashMessage($message, null, AbstractMessage::WARNING);
        }
        $comments = $this->getListData($filter, \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC, true, $pages_ids);
        $statsHeaders = $this->getStatsHeaders();
        $commentHeaders = $this->getCommentHeaders();
        $this
            ->view
            ->assignMultiple(compact(
                'csvButton',
                'resetButton',
                'statsHeaders',
                'commentHeaders',
                'stats',
                'comments'
            ));

    }

    public function resetFilterAction(){
        $filter = $this->processFilter(new Filter());
        $this->redirect('list', NULL, NULL, ['filter' => $filter]);
    }

    /**
     * @param Filter|null $filter // we need to specify the filter class in the argument to prevent map error
     * @return void
     */
    public function statsAction(Filter $filter = null)
    {
        $filter = $this->processFilter($filter);
        $pages_ids = $this->getPageIdsList($filter->getDepth());
        $tooMuchResults = false;
        if (count($pages_ids) > $this->settings['maxStats'] && $filter->getIncludeEmptyPages()) {
            $tooMuchResults = true;
            $pages_ids = array_slice($pages_ids, 0, $this->settings['maxStats']);
        }
        $rows = $this->getStatsData($filter, $pages_ids);
        if ($tooMuchResults || count($rows) > $this->settings['maxStats']) {
            $message = $this->translate('tooMuchPages', [$this->settings['maxStats']]);
            $this->addFlashMessage($message, null, AbstractMessage::WARNING);
            array_pop($rows); // last line was there to check that limit has been reached
        }
        $this->view
            ->assign('csvButton', [
                'href' => $this->getUrl('exportStats'),
                'icon' => $this->icon,
            ])
            ->assign('resetButton', [
                'href' => $this->getUrl('resetFilter'),
            ])
            ->assign('headers', $this->getStatsHeaders())
            ->assign('rows', $rows);
    }

    public function noPageSelectedAction()
    {
        $this->setMenuItems([]);
    }

    protected function getUrl($action, $arguments = [], $controller = null)
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        return $uriBuilder->uriFor($action, $arguments, $controller);
    }

    /**
     * @param Filter|null $filter
     * @return mixed|Filter
     */
    protected function processFilter(Filter $filter = null)
    {
        // Add filtering to records

        if ($filter === null) {
            // Get filter from session if available
            $filter = $this->backendSession->get('filter');
            if (!$filter instanceof Filter) {
                // No filter available, create new one
                $filter = new Filter();
            }
        } else {
            if($filter->getDateRange() != 'userDefined'){
                $filter->setStartDate(null);
                $filter->setEndDate(null);
            }
            $this->backendSession->store('filter', $filter);
        }
        $this->view->assign('filter', $filter);
        return $filter;

    }

    protected function getCSVFilename(Filter $filter, $base_name)
    {
        $format = $this->settings['csvExport']['filename']["dateFormat"];
        $now = date($format);
        $from = $filter->getDateForRange($format);
        return implode('-', array_filter([
                $this->translate($base_name),
                $filter->getLang(),
                'uid' . $this->root_id,
                $from,
                $now,
            ])) . '.csv';

    }

    protected function getStatsHeaders()
    {
        $headers = [];
        foreach (['page_uid', 'page_title', 'total_pos', 'total_neg', 'total', 'avg'] as $col) {
            $headers[$col] = $this->translate('stats.h.' . $col);
        }
        return $headers;
    }

    protected function getCommentHeaders($include_csv_headers = false)
    {
        $headers = [];

        foreach (['date_heure', 'commentaire', 'appreciation',] as $col) {
            $headers[$col] = $this->translate('comments.h.' . $col);
        }
        if ($include_csv_headers) {
            $headers = array_merge([
                'page_uid' => $this->translate('csv.h.page_uid'),
                'page_title' => $this->translate('stats.h.page_title'),
            ], $headers);
        }
        return $headers;
    }

    /**
     * @param Filter $filter
     * @param array $page_ids
     * @return array
     */
    protected function getStatsData(Filter $filter, $page_ids = [], $limit = true)
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
     * @param Filter $filter
     * @return array
     */
    protected function getListData(Filter $filter, $fetch_mode = \PDO::FETCH_ASSOC, $limit = false, $ids = [])
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
       // debug($query);
       // die();
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

    protected function getListCount(Filter $filter)
    {
        $query = $this->getQueryStub($filter, [], 'comments count');
        $total = (int) $this->getPdo()->query($query)->fetch(\PDO::FETCH_COLUMN);
        return $total;
    }

    /**
     * @param null $filter
     */
    public function exportStatsAction($filter = null)
    {
        $filter = $this->processFilter($filter);
        $this->view = $this->objectManager->get(CsvView::class);
        $this->view->setFilename($this->getCSVFilename($filter, 'stats'));
        $this->view->setControllerContext($this->controllerContext);
        $this->view->assign('headers', $this->getStatsHeaders());
        $filter->setIncludeEmptyPages(true);
        $this->view->assign('rows', $this->getStatsData($filter, [], false));
    }

    /**
     * @param null $filter
     */
    public function exportListAction($filter = null)
    {
        $filter = $this->processFilter($filter);
        $this->view = $this->objectManager->get(CsvView::class);
        $this->view->setFilename($this->getCSVFilename($filter, 'comments'));
        $this->view->setControllerContext($this->controllerContext);
        $this->view->assign('headers', $this->getCommentHeaders(true));
        $filter->setIncludeEmptyPages(true);
        $rows = $this->getListData($filter);
        $this->view->assign('rows', $rows);
    }

    /**
     * @param $depth
     * @return array
     */
    protected function getPageTreeIds($depth)
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


    protected function forwardIfNoPageSelected()
    {
        if (!$this->root_id) {
            $this->forward('noPageSelected');
        }
    }


    /**
     * @param Filter $filter
     * @param array $ids_list
     * @return string
     */
    protected function getQueryStub(Filter $filter, $ids_list = [], $query_name = 'comments joins pages')
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


    protected function getPageIdsList($depth)
    {
        $page_ids = [];
        if ($depth > 0) {
            $page_ids = $this->getPageTreeIds($depth);
        }
        $page_ids[] = $this->root_id;
        return $page_ids;
    }


    /**
     * Renvoie les clause de jointure pour les niveaux de profondeur du pagetree
     * @param $depth - niveau de profondeur
     * @param $comm_alias - alias de la table des commentaires
     * @return array - la clause de jointure indexées par alias de niveau de page
     */
    protected function getPageTreeView($depth)
    {

        $child = $parent = 'lvl_0';
        $clauses = [];
        for ($i = 1; $i <= $depth; $i++) {
            $child = 'lvl_' . $i;
            $clauses[] = "left join pages $child on $parent.uid in ($child.uid, $child.pid) and !$child.deleted";
            $parent = $child;
        }
        $joins = implode("\n", $clauses);
        return "select distinct $child.* from pages lvl_0 $joins where lvl_0.uid = $this->root_id";
    }


}
