<?php

namespace Qc\QcComments\Controller\Frontend;

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

use Qc\QcComments\Configuration\TyposcriptConfiguration;
use Qc\QcComments\Domain\Model\Comment;
use Qc\QcComments\Domain\Repository\CommentRepository;
use Qc\QcComments\SpamShield\SpamShieldValidator;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

// FrontEnd Controller
class CommentsController extends ActionController
{
    /**
     * @var CommentRepository
     */
    protected CommentRepository $commentsRepository;

    /**
     * @var TyposcriptConfiguration
     */
    protected TyposcriptConfiguration  $typoscriptConfiguration;


    /**
     * @var array
     */
    private array $tsConfig = [];

    private const DEFAULT_MAX_CHARACTERS = 500;

    private const DEFAULT_MIN_CHARACTERS = 3;

    private const QC_LANG_FILE = 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:';

    /**
     * @var LocalizationUtility
     */
    protected LocalizationUtility $localizationUtility;

    /**
     * @var bool
     */
    protected bool $isSpamShieldEnabled = false;

    public function injectCommentsRepository(CommentRepository $commentsRepository)
    {
        $this->commentsRepository = $commentsRepository;
    }

    public function __construct(
    ) {
        $this->localizationUtility = GeneralUtility::makeInstance(LocalizationUtility::class);
        $this->typoscriptConfiguration = GeneralUtility::makeInstance(TyposcriptConfiguration::class);
        $this->isSpamShieldEnabled = $this->typoscriptConfiguration->isSpamShieldEnabled();
    }


    /**
     * This function is used to render the comments form
     * @param array $args
     */
    public function showAction(array $args = [])
    {
        $commentLengthconfig = [
            'maxCharacters' => $this->typoscriptConfiguration->getCommentsMaxCharacters(),
            'minCharacters' => $this->typoscriptConfiguration->getCommentsMinCharacters()
        ];
        $recaptchaConfig = [
            'enabled' => $this->typoscriptConfiguration->isRecaptchaEnabled(),
            'sitekey' => $this->typoscriptConfiguration->getRecaptchaSitekey(),
            'secret' => $this->typoscriptConfiguration->getRecaptchaSecretKey()
        ];
        $this->view->assignMultiple([
            'submitted' => $this->request->getArguments()['submitted'],
            'validationResults' => $this->request->getArguments()['validationResults'],
            'comment' => new Comment(),
            'config' => $commentLengthconfig,
            'recaptchaConfig' => $recaptchaConfig,
            'isSpamShieldEnabled' => $this->isSpamShieldEnabled
        ]);
    }

    /**
     * This function is used to save comment
     * @param Comment|null $comment
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     */
    public function saveCommentAction(Comment $comment = null)
    {
        $spamErrors = false;
        if($this->isSpamShieldEnabled){
            $validator = GeneralUtility::makeInstance(SpamShieldValidator::class);
            $validationResults = $validator->validate($comment);
            $spamErrors = $validationResults->hasErrors();
            if($spamErrors){
                $this->forward('show', null, null, ['submitted' => 'false','validationResults' => $validationResults]);
            }
        }
        if(!$spamErrors){
            if ($comment) {
                $pageUid = $comment->getUidOrig();
                $comment->setUidPermsGroup(
                    BackendUtility::getRecord('pages', $pageUid, 'perms_groupid', "uid = $pageUid")['perms_groupid']
                );
                $comment->setComment(substr($comment->getComment(), 0,$this->typoscriptConfiguration->getCommentsMaxCharacters()));
                $comment->setDateHour(date('Y-m-d H:i:s'));
                $this->commentsRepository->add($comment);
            }
            $this->forward('show', null, null, ['submitted' => 'true']);
        }
    }

}
