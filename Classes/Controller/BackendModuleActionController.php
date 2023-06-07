<?php

declare(strict_types=1);

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
namespace Qc\QcComments\Controller;

use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

/**
 * Extend this controller to get convenience methods for backend modules
 */
class BackendModuleActionController extends ActionController
{
    /**
     * @var int
     */
    protected int $pageUid = 0;

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * The menu identifier for the backend module
     *
     * @var string
     */
    protected string $menuIdentifier = 'menuidentifier';

    /**
     *
     * @var array
     */
    protected array $menuItems = [];

    /**
     * The extension key of the controller extending this class
     *
     * @var string
     */
    protected string $extKey;

    /**
     * @var PageRenderer
     */
    protected PageRenderer $pageRenderer;

    /**
     * @param ViewInterface $view
     */
    protected function initializeView(ViewInterface $view)
    {
        /** @var BackendTemplateView $view */
        $this->pageRenderer = $this->view->getModuleTemplate()->getPageRenderer();
        $this->createMenu();
    }

    /**
     * Create menu for backend module
     */
    protected function createMenu()
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        $menu = $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier($this->menuIdentifier);

        foreach ($this->menuItems as $menuItem) {
            $item = $menu->makeMenuItem()
                ->setTitle($menuItem['label'])
                ->setHref((string)$uriBuilder->reset()->uriFor($menuItem['action'], [], $menuItem['controller']))
                ->setActive($this->request->getControllerActionName() === $menuItem['action']
                                    && $this->request->getControllerName() === $menuItem['controller']);
            $menu->addMenuItem($item);
        }

        $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }


    /**
     * @param $menuIdentifier
     */
    public function setMenuIdentifier($menuIdentifier)
    {
        $this->menuIdentifier = $menuIdentifier;
    }

    /**
     * @param $menuItems
     */
    public function setMenuItems($menuItems)
    {
        $this->menuItems = $menuItems;
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

}
