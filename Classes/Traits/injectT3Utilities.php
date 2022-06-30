<?php


namespace Qc\QcComments\Traits;


use Symfony\Polyfill\Intl\Icu\NumberFormatter;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\TypoScriptAspect;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

trait injectT3Utilities
{

    /**
     * return a TS value according to the path
     * @param $path
     * @return array
     */
    protected function getTS($path) {
        $val = $GLOBALS['TSFE']->tmpl->setup;
        $stack = explode('.', $path);
        if ($stack[count($stack) - 1] == '') {
            unset($stack[count($stack) - 1]);
            $stack[count($stack) - 1] .= '.';
        }
        $len = count($stack);
        foreach ($stack as $i => $segment) {
            $val = $val[$segment.($i < $len-1 ? '.' : '')] ?? null;
        }
        return $val;
    }


    static  protected function addTrKey($key)
    {

        if ($_GET['addTrKey'] == 1) {
            return " ($key)";
        }
        return '';
    }

    /**
     * @return mixed|SiteLanguage
     */
    static protected function getSiteLanguage()
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            $sites = GeneralUtility::makeInstance(SiteFinder::class)->getAllSites();
            $site = reset($sites);
            if (!$site instanceof Site) {
                $site = new NullSite();
            }
        }
        $language = $request->getAttribute('language');
        if (!$language instanceof SiteLanguage) {
            $language = $site->getDefaultLanguage();
        }
        return $language;
    }

    static protected function getCurrencyFormatter()
    {
        $locale = self::getSiteLanguage()->getLocale();
        $fmt = numfmt_create( $locale, NumberFormatter::CURRENCY );
        $fmt->setSymbol(NumberFormatter::CURRENCY_SYMBOL, '$');
        $fmt->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
        return $fmt;

    }

    static protected function getPercentFormatter()
    {
        $locale = self::getSiteLanguage()->getLocale();
        return \NumberFormatter::create($locale, \NumberFormatter::PERCENT);
    }

    static protected function getNumberFormatter()
    {
        $locale = self::getSiteLanguage()->getLocale();
        return \NumberFormatter::create($locale, \NumberFormatter::DECIMAL);
    }

    static protected function getDateFormatter(
        $dateType = \IntlDateFormatter::LONG,
        $timeType = \IntlDateFormatter::SHORT,
        $pattern = ''
    )
    {
        $locale = self::getSiteLanguage()->getLocale();
        return \IntlDateFormatter::create(explode('.',$locale)[0],$dateType , $timeType,null,null, $pattern);
    }


    static protected function getFormatters()
    {
        $fmt = [
            'number' => self::getNumberFormatter(),
            'percent' => self::getPercentFormatter(),
            'currency' => self::getCurrencyFormatter(),
        ];
        return $fmt;
    }

    static protected function isFr()
    {
        return GeneralUtility::makeInstance(Context::class)
            ->getAspect('language')
            ->getId() == 0;

    }

    static protected function getSiteLanguages()
    {
        /**
         * @var SiteMatcher $siteMatcher
         */
        $siteMatcher = GeneralUtility::makeInstance(SiteMatcher::class);
        /**
         * @var SiteInterface $site
         */
        $site = $siteMatcher->matchByPageId(1);

        return $site->getAvailableLanguages($GLOBALS['BE_USER'], true, 1);

    }

    /**
     * Initialize tsfe
     * @return void
     */
    protected function initTsfe()
    {

        /* init tsfe */
        if (empty($GLOBALS['TSFE'])) {
            if (!is_object($GLOBALS['TT'])) {
                $GLOBALS['TT'] = GeneralUtility::makeInstance(TimeTracker::class, false);
                $GLOBALS['TT']->start();
            }

            $context = GeneralUtility::makeInstance(Context::class);
            $typoScriptAspect = GeneralUtility::makeInstance(TypoScriptAspect::class, true);
            $context->setAspect('typoscript', $typoScriptAspect);
            /** @var Site $site */
            $sites = GeneralUtility::makeInstance(SiteFinder::class)->getAllSites();
            $site = array_shift($sites);

            $_SERVER['HTTP_HOST'] = $site->getBase()->getHost();
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                \Clickstorm\CsSeo\Controller\TypoScriptFrontendController::class,
                $context,
                $site,
                $site->getLanguageById(0)
            );
            $GLOBALS['TSFE']->fe_user = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
            $GLOBALS['TSFE']->id = $site->getConfiguration()['rootPageId'];

            $GLOBALS['TSFE']->newCObj();
            $GLOBALS['TSFE']->determineId();

            $GLOBALS['TSFE']->getConfigArray();
            $GLOBALS['TSFE']->config['config']['sys_language_uid'] = 0;
            $GLOBALS['TSFE']->settingLanguage();
        }
    }

}