<?php

namespace Qc\QcComments\Controller;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PagesConfigurationMigrationToDB
{
    public function transposeExceptionsPagesForDisbledBasPage(){
        $idsStr = "getTSFE().id == 3 || getTSFE().id == 12 || getTSFE().id == 167 || getTSFE().id == 664 || 1210 in tree.rootLineIds
         || 1426 in tree.rootLineIds || 1428 in tree.rootLineIds || 1415 in tree.rootLineIds 
         || 1348 in tree.rootLineIds || 827 in tree.rootLineIds || 1886 in tree.rootLineIds || 1876 in tree.rootLineIds
          || 1865 in tree.rootLineIds || 1855 in tree.rootLineIds || 1845 in tree.rootLineIds || 1835 in tree.rootLineIds 
          || 1825 in tree.rootLineIds || 1815 in tree.rootLineIds || 1805 in tree.rootLineIds || 1795 in tree.rootLineIds || 1785 in tree.rootLineIds 
          || 1775 in tree.rootLineIds || 1765 in tree.rootLineIds || 1755 in tree.rootLineIds 
          || 1745 in tree.rootLineIds || 1735 in tree.rootLineIds || 1725 in tree.rootLineIds || 1715 in tree.rootLineIds 
          || 1685 in tree.rootLineIds || 1705 in tree.rootLineIds || 1695 in tree.rootLineIds || 1675 in tree.rootLineIds 
          || 1866 in tree.rootLineIds || 2505 in tree.rootLineIds || 2506 in tree.rootLineIds 
          || 2507 in tree.rootLineIds || 2508 in tree.rootLineIds || 2509 in tree.rootLineIds || 2510 in tree.rootLineIds 
          || 2511 in tree.rootLineIds || 2512 in tree.rootLineIds || 2513 in tree.rootLineIds || 2514 in tree.rootLineIds || 2515 in tree.rootLineIds 
          || 2516 in tree.rootLineIds || 2517 in tree.rootLineIds || 2518 in tree.rootLineIds || 2519 in tree.rootLineIds 
          || 2926 in tree.rootLineIds || 2927 in tree.rootLineIds || getTSFE().id == 5854 || getTSFE().id == 5871 || getTSFE().id == 6089 
          || getTSFE().id == 6090 || getTSFE().id == 6104 || getTSFE().id == 6129 || getTSFE().id == 6095 || getTSFE().id == 6298
         || getTSFE().id == 6375 || getTSFE().id == 6609 || getTSFE().id == 6764 || getTSFE().id == 6769 || getTSFE().id == 2925 
         || getTSFE().id == 6557
         
       
         || getTSFE().id == 4751 || getTSFE().id == 2632 || getTSFE().id == 5057 || getTSFE().id == 6391

         
         || 4305 in tree.rootLineIds || 475 in tree.rootLineIds || 4796 in tree.rootLineIds || 5156 in tree.rootLineIds 
         || 5875 in tree.rootLineIds || 5880 in tree.rootLineIds || 6208 in tree.rootLineIds 
         || 6182 in tree.rootLineIds || 7078 in tree.rootLineIds || 7255 in tree.rootLineIds";

        $ids[] = '1514';
        $ids[] = '17';

        $ids = $this->getIdsArray($idsStr);
        //$this->updatePagesTable("mode 1", $ids);
    }

    public function transposeExceptionsPagesForEnabledBasPage(){
        $idsStr = "
        1345 in tree.rootLineIds || 2212 in tree.rootLineIds || 4351 in tree.rootLineIds || 5069 in tree.rootLineIds 
        || 5635 in tree.rootLineIds || 2211 in tree.rootLineIds

        || 4508 in tree.rootLineParentIds || 4502 in tree.rootLineParentIds || 4499 in tree.rootLineParentIds 
        || 2631 in tree.rootLineParentIds || 1608 in tree.rootLineParentIds 
        || 4753 in tree.rootLineParentIds || 5056 in tree.rootLineParentIds || 5391 in tree.rootLineParentIds";

        $ids = $this->getIdsArray($idsStr);
        //$this->updatePagesTable("mode 1", $ids);

    }

    /**
     * @param string $ids
     * @return array
     */
    public function getIdsArray(string $ids): array
    {
        $strF = explode('||',$ids);
        $formattedS = [];
        $strToBeRemoved = ["getTSFE().id == ", " in tree.rootLineIds", " in tree.rootLineParentIds"];
        foreach ($strF as $item){
            foreach ($strToBeRemoved as $str){
                $id =  str_replace($str,"", $item);
                $item = $id;
            }
            $formattedS[] = $id;
        }
        return $formattedS;
    }

    public function updatePagesTable(string $mode, array $ids){
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder
            ->update('pages')
            ->where(
                $queryBuilder->expr()->in('pages', $queryBuilder->createNamedParameter($ids))
            )
            ->set('tx_select_bas_page_mode', $mode)
            ->execute();
    }

}