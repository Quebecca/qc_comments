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
     * @var int
     */
    protected int $useful = 0;

    /**
     * @var string
     */
    protected string $comment = '';

    protected string $urlOrig = '';

    protected int $uidOrig = 0;

    protected int $uidPermsGroup = 0;

    protected string $dateHour = '';

    /**
     * @return int
     */
    public function getUseful(): int
    {
        return $this->useful;
    }

    /**
     * @param int $useful
     */
    public function setUseful(int $useful): void
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
}
