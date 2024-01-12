<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension t3s_swiper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3S\T3sSwiper\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class IsLoadedViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('extensionkey', 'string', 'Extension key', true);
    }

    /**
     * Render the URI to the resource. The filename is used from child content.
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): bool
    {
        if (ExtensionManagementUtility::isLoaded($arguments['extensionkey'])) {
            return true;
        } else {
            return false;
        }
    }
}
