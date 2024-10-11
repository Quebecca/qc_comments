<?php

namespace Qc\QcComments\Domain\Model;

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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Comment extends AbstractEntity
{
    /**
     * @var string
     */
    protected string $submittedFormUid = '0';

    /**
     * @var string
     */
    protected string $useful = '0';

    /**
     * @var string
     */
    protected string $comment = '';

    /**
     * @var string
     */
    protected string $urlOrig = '';

    /**
     * @var int
     */
    protected int $uidOrig = 0;

    /**
     * @var int
     */
    protected int $uidPermsGroup = 0;

    /**
     * @var string
     */
    protected string $dateHour = '';

    /**
     * @var string
     */
    protected string $reasonCode = '';

    /**
     * @var string
     */
    protected string $reasonLongLabel = '';

    /**
     * @var string
     */
    protected string $reasonShortLabel = '';

    /**
     * @var int
     */
    protected int $userUidFixingProblem = 0;

    /**
     * @var string
     */
    protected string $fixingDate = '';

    /**
     * @var int
     */
    protected int $deleted = 0;

    /**
     * @return int
     */
    public function getUseful(): string
    {
        return $this->useful;
    }

    /**
     * @param string $useful
     */
    public function setUseful(string $useful): void
    {
        $this->useful = $useful;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getUrlOrig(): string
    {
        return $this->urlOrig;
    }

    /**
     * @param string $urlOrig
     */
    public function setUrlOrig(string $urlOrig): void
    {
        $this->urlOrig = $urlOrig;
    }

    /**
     * @return int
     */
    public function getUidOrig(): int
    {
        return $this->uidOrig;
    }

    /**
     * @param int $uidOrig
     */
    public function setUidOrig(int $uidOrig): void
    {
        $this->uidOrig = $uidOrig;
    }

    /**
     * @return int
     */
    public function getUidPermsGroup(): int
    {
        return $this->uidPermsGroup;
    }

    /**
     * @param int $uidPermsGroup
     */
    public function setUidPermsGroup(int $uidPermsGroup): void
    {
        $this->uidPermsGroup = $uidPermsGroup;
    }

    /**
     * @return string
     */
    public function getDateHour(): string
    {
        return $this->dateHour;
    }

    /**
     * @param string $dateHour
     */
    public function setDateHour(string $dateHour): void
    {
        $this->dateHour = $dateHour;
    }

    /**
     * @return string
     */
    public function getReasonCode(): string
    {
        return $this->reasonCode;
    }

    /**
     * @param string $reasonCode
     */
    public function setReasonCode(string $reasonCode): void
    {
        $this->reasonCode = $reasonCode;
    }
    /**
     * @param string $reasonShorLabel
     */
    public function setReasonShortLabel(string $reasonShortLabel): void
    {
        $this->reasonShortLabel = $reasonShortLabel;
    }

    /**
     * @param string $reasonLongLabel
     */
    public function setReasonLongLabel(string $reasonLongLabel): void
    {
        $this->reasonLongLabel = $reasonLongLabel;
    }

    /**
     * @return string
     */
    public function getReasonLongLabel(): string
    {
        return $this->reasonLongLabel;
    }

    /**
     * @return string
     */
    public function getReasonShortLabel(): string
    {
        return $this->reasonShortLabel;
    }

    /**
     * @param string $submittedFormUid
     */
    public function setSubmittedFormUid(string $submittedFormUid): void
    {
        $this->submittedFormUid = $submittedFormUid;
    }

    /**
     * @return string
     */
    public function getSubmittedFormUid(): string
    {
        return $this->submittedFormUid;
    }

    /**
     * @return int
     */
    public function getUserUidFixingProblem(): int
    {
        return $this->userUidFixingProblem;
    }

    /**
     * @param int $userUidFixingProblem
     */
    public function setUserUidFixingProblem(int $userUidFixingProblem): void
    {
        $this->userUidFixingProblem = $userUidFixingProblem;
    }

    /**
     * @return string
     */
    public function getFixingDate(): string
    {
        return $this->fixingDate;
    }

    /**
     * @param string $fixingDate
     */
    public function setFixingDate(string $fixingDate): void
    {
        $this->fixingDate = $fixingDate;
    }

    /**
     * @param int $deleted
     */
    public function setDeleted(int $deleted): void
    {
        $this->deleted = $deleted;
    }

    /**
     * @return int
     */
    public function getDeleted(): int
    {
        return $this->deleted;
    }
}
