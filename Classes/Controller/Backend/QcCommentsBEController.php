<?php
namespace Qc\QcComments\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Qc\QcComments\Service\QcBackendModuleService;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Psr\Http\Message\ServerRequestInterface;

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
        $statisticsTitle = $this->localizationUtility->translate(
            self::QC_LANG_FILE . 'statisticsTitle'
        );
        $commentsTitle = $this->localizationUtility->translate(
            self::QC_LANG_FILE . 'commentsTitle'
        );
        $technicalProblemsTitle = $this->localizationUtility->translate(
            self::QC_LANG_FILE . 'technicalProblemsTitle'
        );
        $deletedCommentsTitle = $this->localizationUtility->translate(
            self::QC_LANG_FILE . 'removedCommentsTitle'
        );
        $menu->addMenuItem(
            $menu->makeMenuItem()
                ->setTitle($statisticsTitle)
                ->setHref($this->uriBuilder->uriFor('statistics', [],'Backend\StatisticsBE'))
                ->setActive($currentAction === 'statistics')
        );
        $menu->addMenuItem(
            $menu->makeMenuItem()
                ->setTitle($commentsTitle)
                ->setHref($this->uriBuilder->uriFor('comments', [], 'Backend\CommentsBE'))
                ->setActive($currentAction === 'comments')
        );
        $menu->addMenuItem(
            $menu->makeMenuItem()
                ->setTitle($deletedCommentsTitle)
                ->setHref($this->uriBuilder->uriFor('hiddenComments', [], 'Backend\HiddenCommentsBE'))
                ->setActive($currentAction === 'hiddenComments')
        );
        $menu->addMenuItem(
            $menu->makeMenuItem()
                ->setTitle($technicalProblemsTitle)
                ->setHref($this->uriBuilder->uriFor('technicalProblems', [], 'Backend\TechnicalProblemsBE'))
                ->setActive($currentAction === 'technicalProblems')
        );
        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * This function is used to handle requests, when no action selected
     */
    public function handleRequestsAction(): ForwardResponse
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
