<?php


namespace Qc\QcComments\Traits;

trait injectT3Utilities
{
    /**
     * This function is used to return the extension key
     * @param $key
     * @return string
     */
    static  protected function addTrKey($key): string
    {

        if ($_GET['addTrKey'] == 1) {
            return " ($key)";
        }
        return '';
    }
}