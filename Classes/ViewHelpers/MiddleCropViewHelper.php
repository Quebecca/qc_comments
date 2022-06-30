<?php

namespace Qc\QcComments\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class MiddleCropViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        // Convertir la chaîne pour retirer les caractères spéciaux (ex. &quot);
        $str = htmlspecialchars_decode($renderChildrenClosure(),ENT_QUOTES);
        $maxlength = $arguments['maxLength'];
        if (strlen($str) <= $maxlength) {
            return $str;
        }
        $separator = ' […]';
        $separatorlength = strlen($separator) ;

        $maxlength = $arguments['maxLength'] - $separatorlength;

        // nb caractères avant et après l'ellipse
        $len = $maxlength / 2 ;

        // texte avant ellipse
        $avant = mb_substr($str, 0, $len);
        // texte après ellipse
        $apres = mb_substr($str, -$len);

        return sprintf('<span title="%s">%s</span>', $str, $avant . $separator . $apres);
    }

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('maxLength','integer','Max length for the text',false, 95);
    }

}
