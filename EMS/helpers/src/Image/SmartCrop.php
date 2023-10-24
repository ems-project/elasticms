<?php

declare(strict_types=1);

namespace EMS\Helpers\Image;

use EMS\Helpers\Standard\Type;

class SmartCrop
{
    private int $cropWidth = 0;
    private int $cropHeight = 0;
    private array $options = [
        'detailWeight' => 0.2,
        'skinColor' => [
            0.78,
            0.57,
            0.44,
        ],
        'skinBias' => 0.01,
        'skinBrightnessMin' => 0.2,
        'skinBrightnessMax' => 1.0,
        'skinThreshold' => 0.8,
        'skinWeight' => 1.8,
        'saturationBrightnessMin' => 0.05,
        'saturationBrightnessMax' => 0.9,
        'saturationThreshold' => 0.4,
        'saturationBias' => 0.2,
        'saturationWeight' => 0.3,
        'scoreDownSample' => 8,
        'step' => 8,
        'scaleStep' => 0.1,
        'minScale' => 1.0,
        'maxScale' => 1.0,
        'edgeRadius' => 0.4,
        'edgeWeight' => -20.0,
        'outsideImportance' => -0.5,
        'boostWeight' => 100.0,
        'ruleOfThirds' => true,
        'prescale' => true,
        'imageOperations' => null,
        'canvasFactory' => 'defaultCanvasFactory',
        'debug' => false,
    ];
    /** @var \SplFixedArray<int> */
    private \SplFixedArray $od;
    /** @var \SplFixedArray<float> */
    private \SplFixedArray $aSample;
    private int $h = 0;
    private int $w = 0;
    private float $preScale;

    public function __construct(private \GdImage $oImg, private readonly int $width, private readonly int $height)
    {
        $this->canvasImageScale();
    }

    private function canvasImageScale(): self
    {
        $imageOriginalWidth = \imagesx($this->oImg);
        $imageOriginalHeight = \imagesy($this->oImg);

        if ($this->options['debug']) {
            if ($imageOriginalWidth >= $this->width) {
                exit('smartcrop: your set image width is greater than the original image width.');
            }
            if ($imageOriginalHeight >= $this->height) {
                exit('smartcrop: your set image height is greater than the original image height.');
            }
        }

        $scale = \min($imageOriginalWidth / $this->width, $imageOriginalHeight / $this->height);

        $this->cropWidth = (int) \floor($this->width * $scale);
        $this->cropHeight = (int) \floor($this->height * $scale);

        $this->options['minScale'] = \min($this->options['maxScale'], \max(1 / $scale, $this->options['minScale']));

        if (false !== $this->options['prescale']) {
            $this->preScale = 1 / $scale / $this->options['minScale'];
            if ($this->preScale < 1) {
                $this->canvasImageResample((int) \ceil($imageOriginalWidth * $this->preScale), (int) \ceil($imageOriginalHeight * $this->preScale));
                $this->cropWidth = (int) \ceil($this->cropWidth * $this->preScale);
                $this->cropHeight = (int) \ceil($this->cropHeight * $this->preScale);
            } else {
                $this->preScale = 1;
            }
        }

        return $this;
    }

    private function canvasImageResample(int $width, int $height): self
    {
        $canvas = Type::gdImage(\imagecreatetruecolor($width, $height));
        \imagealphablending($canvas, false);
        \imagesavealpha($canvas, true);
        \imagecopyresampled($canvas, $this->oImg, 0, 0, 0, 0, $width, $height, \imagesx($this->oImg), \imagesy($this->oImg));
        $this->oImg = $canvas;

        return $this;
    }

    /**
     * @return array{topCrop: array{x: int, y: int, width: int, height: int}|null}
     */
    public function analyse(): array
    {
        $result = [];
        $w = $this->w = \imagesx($this->oImg);
        $h = $this->h = \imagesy($this->oImg);

        $this->od = new \SplFixedArray($h * $w * 3);
        $this->aSample = new \SplFixedArray($h * $w);
        for ($y = 0; $y < $h; ++$y) {
            for ($x = 0; $x < $w; ++$x) {
                $p = $y * $this->w * 3 + $x * 3;
                $aRgb = $this->getRgbColorAt($x, $y);
                $this->od[$p + 1] = $this->edgeDetect($x, $y, $w, $h);
                $this->od[$p] = $this->skinDetect($aRgb[0], $aRgb[1], $aRgb[2], $this->sample($x, $y));
                $this->od[$p + 2] = $this->saturationDetect($aRgb[0], $aRgb[1], $aRgb[2], $this->sample($x, $y));
            }
        }

        $scoreOutput = $this->downSample($this->options['scoreDownSample']);
        $topScore = -INF;
        $topCrop = null;
        $crops = $this->generateCrops();

        foreach ($crops as &$crop) {
            $crop['score'] = $this->score($scoreOutput, $crop);
            if ($crop['score']['total'] > $topScore) {
                $topCrop = $crop;
                $topScore = $crop['score']['total'];
            }
        }

        $result['topCrop'] = $topCrop;

        if ($this->options['debug'] && $topCrop) {
            $result['crops'] = $crops;
            $result['debugOutput'] = $scoreOutput;
            $result['debugOptions'] = $this->options;
            $result['debugTopCrop'] = $result['topCrop'];
        }

        return $result;
    }

    /**
     * @return \SplFixedArray<float>
     */
    private function downSample(int $factor): \SplFixedArray
    {
        $width = (int) \floor($this->w / $factor);
        $height = (int) \floor($this->h / $factor);

        $ifactor2 = 1 / ($factor * $factor);

        $data = new \SplFixedArray($height * $width * 4);
        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                $r = 0;
                $g = 0;
                $b = 0;
                $a = 0;

                $mr = 0;
                $mg = 0;
                $mb = 0;

                for ($v = 0; $v < $factor; ++$v) {
                    for ($u = 0; $u < $factor; ++$u) {
                        $p = ($y * $factor + $v) * $this->w * 3 + ($x * $factor + $u) * 3;
                        $pR = $this->od[$p];
                        $pG = $this->od[$p + 1];
                        $pB = $this->od[$p + 2];
                        $pA = 0;
                        $r += $pR;
                        $g += $pG;
                        $b += $pB;
                        $a += $pA;
                        $mr = \max($mr, $pR);
                        $mg = \max($mg, $pG);
                        $mb = \max($mb, $pB);
                    }
                }

                $p = $y * $width * 4 + $x * 4;
                $data[$p] = \round($r * $ifactor2 * 0.5 + $mr * 0.5, 0, PHP_ROUND_HALF_EVEN);
                $data[$p + 1] = \round($g * $ifactor2 * 0.7 + $mg * 0.3, 0, PHP_ROUND_HALF_EVEN);
                $data[$p + 2] = \round($b * $ifactor2, 0, PHP_ROUND_HALF_EVEN);
                $data[$p + 3] = \round($a * $ifactor2, 0, PHP_ROUND_HALF_EVEN);
            }
        }

        return $data;
    }

    private function edgeDetect(int $x, int $y, int $w, int $h): int
    {
        if (0 === $x || $x >= $w - 1 || 0 === $y || $y >= $h - 1) {
            $lightness = $this->sample($x, $y);
        } else {
            $leftLightness = $this->sample($x - 1, $y);
            $centerLightness = $this->sample($x, $y);
            $rightLightness = $this->sample($x + 1, $y);
            $topLightness = $this->sample($x, $y - 1);
            $bottomLightness = $this->sample($x, $y + 1);
            $lightness = $centerLightness * 4 - $leftLightness - $rightLightness - $topLightness - $bottomLightness;
        }

        return (int) \round($lightness, 0, PHP_ROUND_HALF_EVEN);
    }

    private function skinDetect(int $r, int $g, int $b, float $lightness): int
    {
        $lightness = $lightness / 255;
        $skin = $this->skinColor($r, $g, $b);
        $isSkinColor = $skin > $this->options['skinThreshold'];
        $isSkinBrightness = $lightness > $this->options['skinBrightnessMin'] && $lightness <= $this->options['skinBrightnessMax'];
        if ($isSkinColor && $isSkinBrightness) {
            return (int) \round(($skin - $this->options['skinThreshold']) * (255 / (1 - $this->options['skinThreshold'])), 0, PHP_ROUND_HALF_EVEN);
        } else {
            return 0;
        }
    }

    private function saturationDetect(int $r, int $g, int $b, float $lightness): int
    {
        $lightness = $lightness / 255;
        $sat = $this->saturation($r, $g, $b);
        $acceptableSaturation = $sat > $this->options['saturationThreshold'];
        $acceptableLightness = $lightness >= $this->options['saturationBrightnessMin'] && $lightness <= $this->options['saturationBrightnessMax'];
        if ($acceptableLightness && $acceptableSaturation) {
            return (int) \round(($sat - $this->options['saturationThreshold']) * (255 / (1 - $this->options['saturationThreshold'])), 0, PHP_ROUND_HALF_EVEN);
        } else {
            return 0;
        }
    }

    /**
     * @return array<array{x: int, y: int, width: int, height: int}>
     */
    private function generateCrops(): array
    {
        $w = \imagesx($this->oImg);
        $h = \imagesy($this->oImg);
        $results = [];
        $minDimension = \min($w, $h);
        $cropWidth = empty($this->cropWidth) ? $minDimension : $this->cropWidth;
        $cropHeight = empty($this->cropHeight) ? $minDimension : $this->cropHeight;
        for ($scale = $this->options['maxScale']; $scale >= $this->options['minScale']; $scale -= $this->options['scaleStep']) {
            for ($y = 0; $y + $cropHeight * $scale <= $h; $y += $this->options['step']) {
                for ($x = 0; $x + $cropWidth * $scale <= $w; $x += $this->options['step']) {
                    $results[] = [
                        'x' => $x,
                        'y' => $y,
                        'width' => (int) ($cropWidth * $scale),
                        'height' => (int) ($cropHeight * $scale),
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * @param  \SplFixedArray<float>                                                        $output
     * @param  array{x: int, y: int, width: int, height: int}                               $crop
     * @return array{detail: int, saturation: float, skin: float, boost: int, total: float}
     */
    private function score(\SplFixedArray $output, array $crop)
    {
        $result = [
            'detail' => 0,
            'saturation' => 0,
            'skin' => 0,
            'boost' => 0,
        ];

        $downSample = $this->options['scoreDownSample'];
        $outputHeightDownSample = \floor($this->h / $downSample) * $downSample;
        $outputWidthDownSample = \floor($this->w / $downSample) * $downSample;
        $outputWidth = \floor($this->w / $downSample);

        for ($y = 0; $y < $outputHeightDownSample; $y += $downSample) {
            for ($x = 0; $x < $outputWidthDownSample; $x += $downSample) {
                $i = $this->importance($crop, $x, $y);
                $p = (int) (\floor($y / $downSample) * $outputWidth * 4 + \floor($x / $downSample) * 4);
                $detail = $output[$p + 1] / 255;

                $result['skin'] += $output[$p] / 255 * ($detail + $this->options['skinBias']) * $i;
                $result['saturation'] += $output[$p + 2] / 255 * ($detail + $this->options['saturationBias']) * $i;
                $result['detail'] = $p;
            }
        }

        $result['total'] = ($result['detail'] * $this->options['detailWeight'] + $result['skin'] * $this->options['skinWeight'] + $result['saturation'] * $this->options['saturationWeight'] + $result['boost'] * $this->options['boostWeight']) / ($crop['width'] * $crop['height']);

        return $result;
    }

    /**
     * @param array{x: int, y: int, width: int, height: int} $crop
     */
    private function importance(array $crop, int $x, int $y): float
    {
        if ($crop['x'] > $x || $x >= $crop['x'] + $crop['width'] || $crop['y'] > $y || $y > $crop['y'] + $crop['height']) {
            return $this->options['outsideImportance'];
        }
        $x = ($x - $crop['x']) / $crop['width'];
        $y = ($y - $crop['y']) / $crop['height'];
        $px = \abs(0.5 - $x) * 2;
        $py = \abs(0.5 - $y) * 2;
        $dx = \max($px - 1.0 + $this->options['edgeRadius'], 0);
        $dy = \max($py - 1.0 + $this->options['edgeRadius'], 0);
        $d = ($dx * $dx + $dy * $dy) * $this->options['edgeWeight'];
        $s = 1.41 - \sqrt($px * $px + $py * $py);
        if ($this->options['ruleOfThirds']) {
            $s += (\max(0, $s + $d + 0.5) * 1.2) * ($this->thirds($px) + $this->thirds($py));
        }

        return $s + $d;
    }

    private function thirds(float $x): float
    {
        $x = (($x - (1 / 3) + 1.0) % 2.0 * 0.5 - 0.5) * 16;

        return \max(1.0 - $x * $x, 0.0);
    }

    private function sample(int $x, int $y): float
    {
        $p = $y * $this->w + $x;
        if (isset($this->aSample[$p])) {
            return $this->aSample[$p];
        }
        $aRgbColor = $this->getRgbColorAt($x, $y);
        $sample = $this->cie($aRgbColor[0], $aRgbColor[1], $aRgbColor[2]);
        $this->aSample[$p] = $sample;

        return $sample;
    }

    /**
     * @return int[]
     */
    private function getRgbColorAt(int $x, int $y): array
    {
        $rgb = \imagecolorat($this->oImg, $x, $y);

        return [
            $rgb >> 16,
            $rgb >> 8 & 255,
            $rgb & 255,
        ];
    }

    private function cie(int $r, int $g, int $b): float
    {
        return 0.5126 * $b + 0.7152 * $g + 0.0722 * $r;
    }

    private function skinColor(int $r, int $g, int $b): float
    {
        $mag = \sqrt($r * $r + $g * $g + $b * $b);
        $mag = $mag > 0 ? $mag : 1;
        $rd = ($r / $mag - $this->options['skinColor'][0]);
        $gd = ($g / $mag - $this->options['skinColor'][1]);
        $bd = ($b / $mag - $this->options['skinColor'][2]);
        $d = \sqrt($rd * $rd + $gd * $gd + $bd * $bd);

        return 1 - $d;
    }

    private function saturation(int $r, int $g, int $b): float
    {
        $maximum = \max($r / 255, $g / 255, $b / 255);
        $minimum = \min($r / 255, $g / 255, $b / 255);

        if ($maximum === $minimum) {
            return 0;
        }

        $l = ($maximum + $minimum) / 2;
        $d = ($maximum - $minimum);

        if ($l > 0.5) {
            if ((2 - $maximum - $minimum) == 0) {
                return 0;
            }
        } else {
            if (($maximum + $minimum) == 0) {
                return 0;
            }
        }

        return $l > 0.5 ? $d / (2 - $maximum - $minimum) : $d / ($maximum + $minimum);
    }

    public function crop(int $x, int $y, int $width, int $height): self
    {
        $oCanvas = Type::gdImage(\imagecreatetruecolor($width, $height));

        \imagealphablending($oCanvas, false);
        \imagesavealpha($oCanvas, true);

        \imagecopyresampled($oCanvas, $this->oImg, 0, 0, $x, $y, $width, $height, $width, $height);
        $this->oImg = $oCanvas;

        return $this;
    }

    public function get(): \GdImage
    {
        return $this->oImg;
    }
}
