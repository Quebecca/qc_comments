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
use Qc\QcComments\Traits\InjectTranslation;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

// FrontEnd Controller
class CommentsController extends ActionController
{
    // @Todo : Template for public part
    // @Todo : Test on typo3 v11
    // @Todo : Utilisation des Unix timestamp(Modify export task for map the date column )

    use InjectTranslation;

    /**
     * @var CommentRepository
     */
    protected CommentRepository $commentsRepository;

    private const DEFAULT_MAX_CHARACTERS = 500;

    private array $tsConfig = [];

    public function injectCommentsRepository(CommentRepository $commentsRepository)
    {
        $this->commentsRepository = $commentsRepository;
    }

    protected function initializeAction()
    {
        parent::initializeAction();
        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $typoScriptSettings = $typoScriptService->convertTypoScriptArrayToPlainArray($GLOBALS['TSFE']->tmpl->setup);
        $this->tsConfig =$typoScriptSettings['plugin']['commentsForm']['settings'];
        $this->tsConfig['comments']['maxCharacters'] = intval($this->tsConfig['comments']['maxCharacters']) > 0
            ? intval($this->tsConfig['comments']['maxCharacters'])
            : self::DEFAULT_MAX_CHARACTERS;
    }


    /**
     * This function is used to render comments form
     * @param array $args
     */
    public function showAction(array $args = [])
    {
        $config = [];
        foreach ($this->tsConfig['comments'] as $key => $val){
            if($key != 'maxCharacters')
                $config[$key] = $val !== '' ? $val : $this->translate($key);
            else
                $config[$key] = $val;
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
