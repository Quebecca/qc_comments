<?php

namespace Qc\QcComments\Controller;

use Qc\QcComments\Domain\Repository\CommentsRepository;
use Qc\QcComments\Domain\Dto\Filter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcComments\Traits\InjectPDO;
use Qc\QcComments\Traits\injectT3Utilities;
use Qc\QcComments\Traits\InjectTranslation;
use Qc\QcComments\View\CsvView;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
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
    // @todo : repalce root_id  - $context->getPropertyFromAspect('language', 'id');

    use InjectTranslation, injectT3Utilities;

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

    protected CommentsRepository $commentsRepository;

    // should place the DI before any methods
    public function injectCommentRepository(CommentsRepository $commentsRepository){
        $this->commentsRepository = $commentsRepository;
    }

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
        //$context = GeneralUtility::makeInstance(Context::class);
       // debug($context->getPropertyFromAspect('id', ''));
        parent::initializeAction();
        $this->commentsRepository->setRootId((int)$this->root_id);
        $this->commentsRepository->setSettings($this->settings);
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
        $tooMuchComments = $this->commentsRepository->getListCount($filter) > $this->settings['maxComments'];
        $pages_ids = $this->commentsRepository->getPageIdsList($filter->getDepth());
        if (count($pages_ids) > $this->settings['maxStats'] && $filter->getIncludeEmptyPages()) {
            $tooMuchPages = true;
            $pages_ids = array_slice($pages_ids, 0, $this->settings['maxStats']);
        }
        $stats = $this->commentsRepository->getStatsData($filter, $pages_ids, true);
        $tooMuchPages = $tooMuchPages ?: count($stats) > $this->settings['maxStats'];
        $pages_ids = array_map(function ($row) {
            return $row['page_uid'];
        }, $stats);
        if ($tooMuchComments | $tooMuchPages) {
            $message = $this->translate('tooMuchResults', [$this->settings['maxStats'], $this->settings['maxComments']]);
            $this->addFlashMessage($message, null, AbstractMessage::WARNING);
        }
        $comments = $this->commentsRepository->getListData($filter, \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC, true, $pages_ids);
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
        $pages_ids = $this->commentsRepository->getPageIdsList($filter->getDepth());
        $tooMuchResults = false;
        if (count($pages_ids) > $this->settings['maxStats'] && $filter->getIncludeEmptyPages()) {
            $tooMuchResults = true;
            $pages_ids = array_slice($pages_ids, 0, $this->settings['maxStats']);
        }
        $rows = $this->commentsRepository->getStatsData($filter, $pages_ids,true);
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
        $this->view->assign('rows', $this->commentsRepository->getStatsData($filter, [], false));
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
        $rows = $this->commentsRepository->getListData($filter);
        $this->view->assign('rows', $rows);
    }




    protected function forwardIfNoPageSelected()
    {
        if (!$this->root_id) {
            $this->forward('noPageSelected');
        }
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
