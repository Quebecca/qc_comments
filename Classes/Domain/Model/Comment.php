<?php
namespace Qc\QcComments\Domain\Model;

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

}