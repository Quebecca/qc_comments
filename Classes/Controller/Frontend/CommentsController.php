<?php

namespace Qc\QcComments\Controller\Frontend;

/***
 *
 * This file is part of Qc Comments project.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2022 <techno@quebec.ca>
 *
 ***/

use Qc\QcComments\Domain\Model\Comment;
use Qc\QcComments\Domain\Repository\CommentRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

// FrontEnd Controller
class CommentsController extends ActionController
{
    // @Todo : Update ReadMe
    // @Todo : Utilisation des Unix timestamp(Modify export task for map the date column )

    /**
     * @var CommentRepository
     */
    protected CommentRepository $commentsRepository;

    private const DEFAULT_MAX_CHARACTERS = 500;

    const QC_LANG_FILE = 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:';

    private array $tsConfig = [];

    /**
     * @var LocalizationUtility
     */
    protected LocalizationUtility $localizationUtility;

    public function injectCommentsRepository(CommentRepository $commentsRepository)
    {
        $this->commentsRepository = $commentsRepository;
    }

    public function __construct(
    ) {
        $this->localizationUtility = GeneralUtility::makeInstance(LocalizationUtility::class);
    }
    protected function initializeAction()
    {
        parent::initializeAction();
        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $typoScriptSettings = $typoScriptService->convertTypoScriptArrayToPlainArray($GLOBALS['TSFE']->tmpl->setup);
        $this->tsConfig =$typoScriptSettings['plugin']['commentsForm']['settings'];
        $this->tsConfig['comments']['maxCharacters'] = (int)($this->tsConfig['comments']['maxCharacters']) > 0
            ? (int)($this->tsConfig['comments']['maxCharacters'])
            : self::DEFAULT_MAX_CHARACTERS;
    }

    /**
     * This function is used to render comments form
     * @param array $args
     */
    public function showAction(array $args = [])
    {
        $config = [];
        foreach ($this->tsConfig['comments'] as $key => $val) {
            if ($key != 'maxCharacters') {
                $config[$key] = $val !== '' ? $val : $this->localizationUtility->translate(self::QC_LANG_FILE . $key);
            } else {
                $config[$key] = $val;
            }
        }
        $this->view->assignMultiple([
            'submitted' => $this->request->getArguments()['submitted'],
            'comment' => new Comment(),
            'config' => $config,
            'recaptchaConfig' => $this->tsConfig['recaptcha']
        ]);
    }

    /**
     * This function is used to save user comment
     * @param Comment|null $comment
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     */
    public function saveCommentAction(Comment $comment = null)
    {
        if ($comment) {
            $pageUid = $comment->getUidOrig();
            $comment->setUidPermsGroup(
                BackendUtility::getRecord('pages', $pageUid, 'perms_groupid', "uid = $pageUid")['perms_groupid']
            );
            // set limit for 500 characters
            $comment->setComment(substr($comment->getComment(), 0, $this->tsConfig['comments']['maxCharacters']));
            $comment->setDateHoure(date('Y-m-d H:i:s'));
            $this->commentsRepository->add($comment);
        }
        $this->forward('show', null, null, ['submitted' => true]);
    }
}
