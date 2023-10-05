<?php
namespace Qc\QcComments\Controller\v12;

use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Service\CommentsTabService;
use Qc\QcComments\Service\QcBackendModuleService;
use Qc\QcComments\Service\StatisticsTabService;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Routing\UriBuilder as BackendUriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Beuser\Domain\Repository\BackendUserGroupRepository;
use TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Beuser\Domain\Repository\BackendUserSessionRepository;
use TYPO3\CMS\Beuser\Domain\Repository\FileMountRepository;
use TYPO3\CMS\Beuser\Service\UserInformationService;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class QcCommentsBEv12Controller extends ActionController
{
    protected ?ModuleData $moduleData = null;
    protected ModuleTemplate $moduleTemplate;
    protected int $root_id;
    protected string $controllerName = '';
    protected QcBackendModuleService  $qcBeModuleService;
    const QC_LANG_FILE = 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:';
    protected string $extKey;
    protected bool $actionForwarded = false;
    /**
     * @var mixed|object|ModuleTemplateFactory
     */
    private mixed $moduleTemplateFactory;
    /**
     * @var mixed|object|PageRenderer
     */
    private mixed $pageRenderer;
    /**
     * @var mixed|object|LocalizationUtility
     */
    private mixed $localizationUtility;

    public function __construct(
    ) {
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $this->localizationUtility = GeneralUtility::makeInstance(LocalizationUtility::class);
    }

    /**
     * Init module state.
     * This isn't done within __construct() since the controller
     * object is only created once in extbase when multiple actions are called in
     * one call. When those change module state, the second action would see old state.
     */
    public function initializeAction(): void
    {
        $this->moduleData = $this->request->getAttribute('moduleData');
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->moduleTemplate->setTitle('QcComments');
        $this->moduleTemplate->setFlashMessageQueue($this->getFlashMessageQueue());
        $this->extKey = $this->request->getControllerExtensionKey();
        $this->controllerName = $this->request->getControllerName();
        $this->root_id = GeneralUtility::_GP('id') ?? 0;
    }

    /**
     * Load JS module
     */
    protected function initializeView(): void
    {
        $this->pageRenderer->loadRequireJsModule(
            'TYPO3/CMS/QcComments/AdministrationModule'
        );
        $this->pageRenderer->loadRequireJsModule(
            'TYPO3/CMS/Backend/DateTimePicker'
        );
        $this->pageRenderer->addCssFile(
            'EXT:qc_comments/Resources/Public/Css/be_qc_comments.css'
        );

    }
    /**
     * Doc header main drop down
     */
    protected function addMainMenu(string $currentAction): void
    {
        $this->uriBuilder->setRequest($this->request);
        $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('QcCommentsMenu');
        $menu->addMenuItem(
            $menu->makeMenuItem()
                ->setTitle('Statistics')
                ->setHref($this->uriBuilder->uriFor('statistics'))
                ->setActive($currentAction === 'statistics')
        );
        $menu->addMenuItem(
            $menu->makeMenuItem()
                ->setTitle('Comments')
                ->setHref($this->uriBuilder->uriFor('comments'))
                ->setActive($currentAction === 'comments')
        );
        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * @param Filter|null $filter
     */
    public function statisticsAction(Filter $filter = null,  string $operation = ''): ResponseInterface
    {
       // debug($this->request->getArguments());

        if($operation === 'reset-filters'){
            $filter = new Filter();
        }
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(StatisticsTabService::class);
        $this->qcBeModuleService->getBackendSession()->store('lastAction', 'statistics');

        $this->qcBeModuleService->setRootId($this->root_id);
        $this->qcBeModuleService->processFilter();
        $this->addMainMenu('statistics');
        if (!$this->root_id) {
            $this->moduleTemplate->assign('noPageSelected', true);
        }
        else {
            if ($filter) {
                $this->qcBeModuleService->processFilter($filter);
                $this->moduleTemplate->assign('filter', $filter);

            }
            $data = $this->qcBeModuleService->getPageStatistics();
            if($data['tooMuchResults'] == true){
                $message = $this->localizationUtility
                    ->translate(
                        self::QC_LANG_FILE . 'tooMuchPages',
                        null,
                        [$data['maxRecords']]
                    );
                $this->addFlashMessage($message, null, AbstractMessage::WARNING);
            }
            $statsByDepth = $this->qcBeModuleService->getStatisticsByDepth();
            $this->moduleTemplate->assignMultiple([
                'headers' => $data['headers'],
                'rows' => $data['rows'],
                'settings',
                'currentPageId' => $data['currentPageId'],
                'totalSection_headers' => $statsByDepth['headers'],
                'totalSection_row' => $statsByDepth['row']
            ]);
        }
        $filter = $this->qcBeModuleService->processFilter();

        $this->moduleTemplate->assign('filter', $filter);

        return $this->moduleTemplate->renderResponse('Statistics');
    }



    public function commentsAction(Filter $filter = null, string $operation = ''): ResponseInterface{

        // return new ForwardResponse('statistics');
        if($operation === 'reset-filters'){
            $filter = new Filter();
        }
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(CommentsTabService::class);
        $this->qcBeModuleService->getBackendSession()->store('lastAction', 'comments');

        $this->addMainMenu('comments');

        $this->qcBeModuleService->setRootId($this->root_id);
        $this->qcBeModuleService->processFilter();

        if (!$this->root_id) {
            $this->moduleTemplate->assign('noPageSelected', true);
        }
        else {
            if ($filter) {
                $this->qcBeModuleService->processFilter($filter);
                $this->moduleTemplate->assign('filter', $filter);
            }
            $data = $this->qcBeModuleService->getComments();
            if($data['tooMuchResults'] === true){
                $message = $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'tooMuchResults',
                        null, (array)[$data['numberOfSubPages'], $data['maxRecords']]);
                $this->addFlashMessage($message, null, AbstractMessage::WARNING);
            }

            $this
                ->moduleTemplate
                ->assignMultiple(
                    [
                        /*'csvButton' => $csvButton,
                        'resetButton' => $resetButton,*/
                        'commentHeaders' => $data['commentHeaders'],
                        'stats' => $data['stats'],
                        'comments' => $data['comments'],
                        'pagesId' => $data['pagesId'],
                        'currentPageId' => $data['currentPageId']
                    ]
                );
        }
        $filter = $this->qcBeModuleService->processFilter();
        $this->moduleTemplate->assign('filter', $filter);
        return $this->moduleTemplate->renderResponse('Comments');

    }

    /**
     * This function is used to handle requests, when no action selected
     * @param Filter|null $filter
     */
    public function handleRequestsAction(Filter $filter = null, string $operation = ''): ResponseInterface
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(CommentsTabService::class);

        $currentAction =  $this->request->getControllerActionName(); //v12\QcCommentsBEv12::handleRequests
        if ($currentAction === 'handleRequests') {
            $lastAction = $this->qcBeModuleService->getBackendSession()->get('lastAction') ?? 'statistics';
            return new ForwardResponse($lastAction);
        }
        else {
            $this->qcBeModuleService->getBackendSession()->store('lastAction', $currentAction);
            return new ForwardResponse($currentAction);
        }
    }

    /**
     * This function is used to export statistics records on a csv file
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function exportStatisticsAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(StatisticsTabService::class);
        $filter = $this->qcBeModuleService->getFilterFromRequest($request);
        $filter->setDepth( intval($request->getQueryParams()['parameters']['depth']));
        $currentPageId = intval($request->getQueryParams()['parameters']['currentPageId']);
        return $this->qcBeModuleService->exportStatisticsData($filter, $currentPageId);
    }

}