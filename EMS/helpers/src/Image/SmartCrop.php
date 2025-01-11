<?php

declare(strict_types=1);

namespace EMS\Helpers\Image;

use EMS\Helpers\Standard\Type;

class SmartCrop
{
    private int $cropWidth = 0;
    private int $cropHeight = 0;
    private float $detailWeight = 0.2;
    /** @var float[] */
    private array $skinColor = [
        0.78,
        0.57,
        0.44,
    ];
    private float $skinBias = 0.01;
    private float $skinBrightnessMin = 0.2;
    private float $skinBrightnessMax = 1.0;
    private float $skinThreshold = 0.8;
    private float $skinWeight = 1.8;
    private float $saturationBrightnessMin = 0.05;
    private float $saturationBrightnessMax = 0.9;
    private float $saturationThreshold = 0.4;
    private float $saturationBias = 0.2;
    private float $saturationWeight = 0.3;
    private int $scoreDownSample = 8;
    private int $step = 8;
    private float $scaleStep = 0.1;
    private float $minScale = 1.0;
    private float $maxScale = 1.0;
    private float $edgeRadius = 0.4;
    private float $edgeWeight = -20.0;
    private float $outsideImportance = -0.5;
    private float $boostWeight = 100.0;
    private bool $ruleOfThirds = true;
    private bool $prescale = true;
    private bool $debug = false;
    /** @var \SplFixedArray<int> */
    private \SplFixedArray $od;
    /** @var \SplFixedArray<float> */
    private \SplFixedArray $aSample;
    private int $h = 0;
    private int $w = 0;

    public function __construct(private \GdImage $image, private readonly int $width, private readonly int $height)
    {
        $this->canvasImageScale();
    }

    private function canvasImageScale(): void
    {
        $imageOriginalWidth = \imagesx($this->image);
        $imageOriginalHeight = \imagesy($this->image);

        if ($this->debug) {
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

        $this->minScale = \min($this->maxScale, \max(1 / $scale, $this->minScale));

        if ($this->prescale) {
            $preScale = 1 / $scale / $this->minScale;
            if ($preScale < 1) {
                $this->canvasImageResample((int) \ceil($imageOriginalWidth * $preScale), (int) \ceil($imageOriginalHeight * $preScale));
                $this->cropWidth = (int) \ceil($this->cropWidth * $preScale);
                $this->cropHeight = (int) \ceil($this->cropHeight * $preScale);
            }
        }
    }

    private function canvasImageResample(int $width, int $height): void
    {
        $canvas = Type::gdImage(\imagecreatetruecolor($width, $height));
        \imagealphablending($canvas, false);
        \imagesavealpha($canvas, true);
        \imagecopyresampled($canvas, $this->image, 0, 0, 0, 0, $width, $height, \imagesx($this->image), \imagesy($this->image));
        $this->image = $canvas;
    }

    /**
     * @return array{topCrop: array{x: int, y: int, width: int, height: int}|null}
     */
    public function analyse(): array
    {
        $result = [];
        $w = $this->w = \imagesx($this->image);
        $h = $this->h = \imagesy($this->image);

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

        $scoreOutput = $this->downSample($this->scoreDownSample);
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

        if ($this->debug && $topCrop) {
            $result['crops'] = $crops;
            $result['debugOutput'] = $scoreOutput;
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
                $data[$p] = \round($r * $ifactor2 * 0.5 + $mr * 0.5, 0, \RoundingMode::HalfEven);
                $data[$p + 1] = \round($g * $ifactor2 * 0.7 + $mg * 0.3, 0, \RoundingMode::HalfEven);
                $data[$p + 2] = \round($b * $ifactor2, 0, \RoundingMode::HalfEven);
                $data[$p + 3] = \round($a * $ifactor2, 0, \RoundingMode::HalfEven);
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

        return (int) \round($lightness, 0, \RoundingMode::HalfEven);
    }

    private function skinDetect(int $r, int $g, int $b, float $lightness): int
    {
        $lightness = $lightness / 255;
        $skin = $this->skinColor($r, $g, $b);
        $isSkinColor = $skin > $this->skinThreshold;
        $isSkinBrightness = $lightness > $this->skinBrightnessMin && $lightness <= $this->skinBrightnessMax;
        if ($isSkinColor && $isSkinBrightness) {
            return (int) \round(($skin - $this->skinThreshold) * (255 / (1 - $this->skinThreshold)), 0, \RoundingMode::HalfEven);
        } else {
            return 0;
        }
    }

    private function saturationDetect(int $r, int $g, int $b, float $lightness): int
    {
        $lightness = $lightness / 255;
        $sat = $this->saturation($r, $g, $b);
        $acceptableSaturation = $sat > $this->saturationThreshold;
        $acceptableLightness = $lightness >= $this->saturationBrightnessMin && $lightness <= $this->saturationBrightnessMax;
        if ($acceptableLightness && $acceptableSaturation) {
            return (int) \round(($sat - $this->saturationThreshold) * (255 / (1 - $this->saturationThreshold)), 0, \RoundingMode::HalfEven);
        } else {
            return 0;
        }
    }

    /**
     * @return array<array{x: int, y: int, width: int, height: int}>
     */
    private function generateCrops(): array
    {
        $w = \imagesx($this->image);
        $h = \imagesy($this->image);
        $results = [];
        $minDimension = \min($w, $h);
        $cropWidth = empty($this->cropWidth) ? $minDimension : $this->cropWidth;
        $cropHeight = empty($this->cropHeight) ? $minDimension : $this->cropHeight;
        for ($scale = $this->maxScale; $scale >= $this->minScale; $scale -= $this->scaleStep) {
            for ($y = 0; $y + $cropHeight * $scale <= $h; $y += $this->step) {
                for ($x = 0; $x + $cropWidth * $scale <= $w; $x += $this->step) {
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
    private function score(\SplFixedArray $output, array $crop): array
    {
        $result = [
            'detail' => 0,
            'saturation' => 0,
            'skin' => 0,
            'boost' => 0,
        ];

        $downSample = $this->scoreDownSample;
        $outputHeightDownSample = \floor($this->h / $downSample) * $downSample;
        $outputWidthDownSample = \floor($this->w / $downSample) * $downSample;
        $outputWidth = \floor($this->w / $downSample);

        for ($y = 0; $y < $outputHeightDownSample; $y += $downSample) {
            for ($x = 0; $x < $outputWidthDownSample; $x += $downSample) {
                $i = $this->importance($crop, $x, $y);
                $p = (int) (\floor($y / $downSample) * $outputWidth * 4 + \floor($x / $downSample) * 4);
                $detail = $output[$p + 1] / 255;

                $result['skin'] += $output[$p] / 255 * ($detail + $this->skinBias) * $i;
                $result['saturation'] += $output[$p + 2] / 255 * ($detail + $this->saturationBias) * $i;
                $result['detail'] = $p;
            }
        }

        $result['total'] = ($result['detail'] * $this->detailWeight + $result['skin'] * $this->skinWeight + $result['saturation'] * $this->saturationWeight + $result['boost'] * $this->boostWeight) / ($crop['width'] * $crop['height']);

        return $result;
    }

    /**
     * @param array{x: int, y: int, width: int, height: int} $crop
     */
    private function importance(array $crop, int $x, int $y): float
    {
        if ($crop['x'] > $x || $x >= $crop['x'] + $crop['width'] || $crop['y'] > $y || $y > $crop['y'] + $crop['height']) {
            return $this->outsideImportance;
        }
        $x = ($x - $crop['x']) / $crop['width'];
        $y = ($y - $crop['y']) / $crop['height'];
        $px = \abs(0.5 - $x) * 2;
        $py = \abs(0.5 - $y) * 2;
        $dx = \max($px - 1.0 + $this->edgeRadius, 0);
        $dy = \max($py - 1.0 + $this->edgeRadius, 0);
        $d = ($dx * $dx + $dy * $dy) * $this->edgeWeight;
        $s = 1.41 - \sqrt($px * $px + $py * $py);
        if ($this->ruleOfThirds) {
            $s += (\max(0, $s + $d + 0.5) * 1.2) * ($this->thirds($px) + $this->thirds($py));
        }

        return $s + $d;
    }

    private function thirds(float $x): float
    {
        $x = ((int) ($x - (1 / 3) + 1.0) % 2.0 * 0.5 - 0.5) * 16;

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
        $rgb = \imagecolorat($this->image, $x, $y);

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
        $rd = ($r / $mag - $this->skinColor[0]);
        $gd = ($g / $mag - $this->skinColor[1]);
        $bd = ($b / $mag - $this->skinColor[2]);
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
        $canvas = Type::gdImage(\imagecreatetruecolor($width, $height));
        \imagealphablending($canvas, false);
        \imagesavealpha($canvas, true);
        \imagecopyresampled($canvas, $this->image, 0, 0, $x, $y, $width, $height, $width, $height);
        $this->image = $canvas;

        return $this;
    }

    public function get(): \GdImage
    {
        return $this->image;
    }
}
