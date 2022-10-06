<?php

declare(strict_types=1);

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

namespace Qc\QcCommentsTest\Tests\Functional\Comments;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Domain\Repository\CommentRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \Qc\QcComments\Domain\Repository\CommentRepository
 */
class QcCommentsRepositoryFunctionalTest extends FunctionalTestCase
{
    /**
     * @var CommentRepository
     */
    protected CommentRepository $commentRepository;

    /**
     * @var array<int,string>
     */
    protected $coreExtensionsToLoad = [
        'backend',
        'beuser',
        'fluid',
        'info',
        'install',
        'core'
    ];
    /**
     * @var array<int, non-empty-string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/qc_comments', 'typo3conf/ext/backend_module'];

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['LANG'] = $this->getContainer()->get(LanguageServiceFactory::class)->create('default');
        /** @var Typo3Version $versionInformation */
        $versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
        if ($versionInformation->getMajorVersion() >= 11) {
            $this->commentRepository = $this->getContainer()->get(CommentRepository::class);
        } else {
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->commentRepository = $objectManager->get(CommentRepository::class);
        }
        $filter = new Filter();
        $filter->setDepth(10);
        $filter->setDateRange('userDefined');
        $filter->setStartDate('2021-07-08 00:00:00');
        $filter->setIncludeEmptyPages(true);
        $this->commentRepository->setFilter($filter);
    }

    /**
     * @test
     * @throws \Doctrine\DBAL\Driver\Exception|\TYPO3\TestingFramework\Core\Exception
     */
    public function getComments(): void
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Comments/Comments.xml');
        $row = $this->commentRepository->getComments([1, 2], true);
        self::assertNotNull($row);
        self::assertSame('Positif comment', $row[1][0]['comment']);
        self::assertSame('2022-07-08 00:00:00', $row[1][0]['date_houre']);
        self::assertSame(1, $row[1][0]['useful']);
        self::assertSame('Page 1', $row[1][0]['title']);
    }

    /**
    * @test
    * @throws \Doctrine\DBAL\Driver\Exception|\TYPO3\TestingFramework\Core\Exception
    */
    public function getStatistics(): void
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Comments/Comments.xml');
        $row = $this->commentRepository->getStatistics([1, 2], false);
        self::assertNotNull($row);
        $expectedData = [
            [1, 'Page 1', ' 100 %', '1', 1, 0],
            [2, 'Page 2', ' -100 %', '0', 1, 1]
        ];

        foreach ($row as $key => $dataItem) {
            $i = 0;
            foreach ($dataItem as $item) {
                self::assertSame($expectedData[$key][$i], $item);
                $i++;
            }
        }
    }
}
