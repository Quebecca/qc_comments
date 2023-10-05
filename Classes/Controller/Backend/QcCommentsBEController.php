<?php
namespace Qc\QcComments\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Qc\QcComments\Service\QcBackendModuleService;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class QcCommentsBEController extends ActionController
{
    /**
     * @var ModuleData|null
     */
    protected ?ModuleData $moduleData = null;
    /**
     * @var ModuleTemplate
     */
    protected ModuleTemplate $moduleTemplate;
    /**
     * @var int
     */
    protected int $root_id;
    /**
     * @var string
     */
    protected string $controllerName = '';
    /**
     * @var QcBackendModuleService
     */
    protected QcBackendModuleService  $qcBeModuleService;

    const QC_LANG_FILE = 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:';

    /**
     * @var ModuleTemplateFactory
     */
    protected ModuleTemplateFactory $moduleTemplateFactory;
    /**
     * @var PageRenderer
     */
    protected PageRenderer $pageRenderer;
    /**
     * @var LocalizationUtility
     */
    protected LocalizationUtility $localizationUtility;

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
                ->setHref($this->uriBuilder->uriFor('statistics', [],'Backend\StatisticsBE'))
                ->setActive($currentAction === 'statistics')
        );
        $menu->addMenuItem(
            $menu->makeMenuItem()
                ->setTitle('Comments')
                ->setHref($this->uriBuilder->uriFor('comments', [], 'Backend\CommentsBE'))
                ->setActive($currentAction === 'comments')
        );
        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * This function is used to handle requests, when no action selected
     */
    public function handleRequestsAction(): ResponseInterface
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(QcBackendModuleService::class);
        $currentAction =  $this->request->getControllerActionName();
        if ($currentAction === 'handleRequests') {
            $lastAction = $this->qcBeModuleService->getBackendSession()->get('lastAction');
            $lastActionName = $lastAction['actionName'] ?? 'statistics';
            $lastControllerName =  $lastAction['controllerName'] ?? 'Backend\StatisticsBE';
            return (new ForwardResponse($lastActionName))
                    ->withControllerName($lastControllerName);
        }
        else {
            $this->qcBeModuleService->getBackendSession()->store(
                'lastAction',
                [
                    'controllerName' => $this->controllerName,
                    'actionName' => $currentAction
                ]
            );
            return new ForwardResponse($currentAction);
        }
    }

}