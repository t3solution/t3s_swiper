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

class DimensionsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('settings', 'array', 'Swiper settings.', true);
    }

    /**
     * Render the URI to the resource. The filename is used from child content.
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): array
    {
        $settings = $arguments['settings'];
        $dimensions['sliderwidth'] = !empty($settings['width']) ? (int)$settings['width'] : 1300;

        if ($settings['effectType'] === 'slide' && $settings['slidesPerView'] > 1) {
            $dimensions['width'] = (int)($dimensions['sliderwidth'] / $settings['slidesPerView']);
        } else {
            $dimensions['width'] = $dimensions['sliderwidth'];
        }

        if ($settings['thumbnailsSlidesPerView'] > 1) {
            $dimensions['thumbnailwidth'] = (int)($dimensions['sliderwidth'] / $settings['thumbnailsSlidesPerView']);
        } else {
            $dimensions['thumbnailwidth'] = $dimensions['sliderwidth'];
        }

        $ratio = !empty($settings['ratio']) ? $settings['ratio'] : '16:9';

        if (str_contains($ratio, ':')) {
            $ratioArr = explode(':', $ratio);
            $ratio_multiplier = $ratioArr[1] / $ratioArr[0];
        } else {
            $ratio_multiplier = 9/16;
        }

        $dimensions['sliderheight'] = (int)($dimensions['width'] * $ratio_multiplier);
        $dimensions['height'] = (int)($dimensions['width'] * $ratio_multiplier);
        $dimensions['thumbnailheight'] = (int)($dimensions['thumbnailwidth'] * $ratio_multiplier);

        return $dimensions;
    }
}
