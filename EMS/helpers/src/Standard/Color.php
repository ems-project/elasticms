<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

class Color
{
    final public const array EMS_COLORS = [
        'ems-black' => '000000',
        'ems-black-light' => '000000',
        'ems-blue' => '3C8DBC',
        'ems-blue-light' => '3C8DBC',
        'ems-green' => '008D4C',
        'ems-green-light' => '008D4C',
        'ems-purple' => '555299',
        'ems-purple-light' => '555299',
        'ems-red' => 'D73925',
        'ems-red-light' => 'D73925',
        'ems-yellow' => 'E08E0B',
        'ems-yellow-light' => 'E08E0B',
        'ems-white' => 'FFFFFF',
    ];
    final public const array STANDARD_HTML_COLORS = [
        'aliceblue' => 'F0F8FF',
        'antiquewhite' => 'FAEBD7',
        'aqua' => '00FFFF',
        'aquamarine' => '7FFFD4',
        'azure' => 'F0FFFF',
        'beige' => 'F5F5DC',
        'bisque' => 'FFE4C4',
        'black' => '000000',
        'blanchedalmond ' => 'FFEBCD',
        'blue' => '0000FF',
        'blueviolet' => '8A2BE2',
        'brown' => 'A52A2A',
        'burlywood' => 'DEB887',
        'cadetblue' => '5F9EA0',
        'chartreuse' => '7FFF00',
        'chocolate' => 'D2691E',
        'coral' => 'FF7F50',
        'cornflowerblue' => '6495ED',
        'cornsilk' => 'FFF8DC',
        'crimson' => 'DC143C',
        'cyan' => '00FFFF',
        'darkblue' => '00008B',
        'darkcyan' => '008B8B',
        'darkgoldenrod' => 'B8860B',
        'darkgray' => 'A9A9A9',
        'darkgreen' => '006400',
        'darkgrey' => 'A9A9A9',
        'darkkhaki' => 'BDB76B',
        'darkmagenta' => '8B008B',
        'darkolivegreen' => '556B2F',
        'darkorange' => 'FF8C00',
        'darkorchid' => '9932CC',
        'darkred' => '8B0000',
        'darksalmon' => 'E9967A',
        'darkseagreen' => '8FBC8F',
        'darkslateblue' => '483D8B',
        'darkslategray' => '2F4F4F',
        'darkslategrey' => '2F4F4F',
        'darkturquoise' => '00CED1',
        'darkviolet' => '9400D3',
        'deeppink' => 'FF1493',
        'deepskyblue' => '00BFFF',
        'dimgray' => '696969',
        'dimgrey' => '696969',
        'dodgerblue' => '1E90FF',
        'firebrick' => 'B22222',
        'floralwhite' => 'FFFAF0',
        'forestgreen' => '228B22',
        'fuchsia' => 'FF00FF',
        'gainsboro' => 'DCDCDC',
        'ghostwhite' => 'F8F8FF',
        'gold' => 'FFD700',
        'goldenrod' => 'DAA520',
        'gray' => '808080',
        'green' => '008000',
        'greenyellow' => 'ADFF2F',
        'grey' => '808080',
        'honeydew' => 'F0FFF0',
        'hotpink' => 'FF69B4',
        'indianred' => 'CD5C5C',
        'indigo' => '4B0082',
        'ivory' => 'FFFFF0',
        'khaki' => 'F0E68C',
        'lavender' => 'E6E6FA',
        'lavenderblush' => 'FFF0F5',
        'lawngreen' => '7CFC00',
        'lemonchiffon' => 'FFFACD',
        'lightblue' => 'ADD8E6',
        'lightcoral' => 'F08080',
        'lightcyan' => 'E0FFFF',
        'lightgoldenrodyellow' => 'FAFAD2',
        'lightgray' => 'D3D3D3',
        'lightgreen' => '90EE90',
        'lightgrey' => 'D3D3D3',
        'lightpink' => 'FFB6C1',
        'lightsalmon' => 'FFA07A',
        'lightseagreen' => '20B2AA',
        'lightskyblue' => '87CEFA',
        'lightslategray' => '778899',
        'lightslategrey' => '778899',
        'lightsteelblue' => 'B0C4DE',
        'lightyellow' => 'FFFFE0',
        'lime' => '00FF00',
        'limegreen' => '32CD32',
        'linen' => 'FAF0E6',
        'magenta' => 'FF00FF',
        'maroon' => '800000',
        'mediumaquamarine' => '66CDAA',
        'mediumblue' => '0000CD',
        'mediumorchid' => 'BA55D3',
        'mediumpurple' => '9370D0',
        'mediumseagreen' => '3CB371',
        'mediumslateblue' => '7B68EE',
        'mediumspringgreen' => '00FA9A',
        'mediumturquoise' => '48D1CC',
        'mediumvioletred' => 'C71585',
        'midnightblue' => '191970',
        'mintcream' => 'F5FFFA',
        'mistyrose' => 'FFE4E1',
        'moccasin' => 'FFE4B5',
        'navajowhite' => 'FFDEAD',
        'navy' => '000080',
        'oldlace' => 'FDF5E6',
        'olive' => '808000',
        'olivedrab' => '6B8E23',
        'orange' => 'FFA500',
        'orangered' => 'FF4500',
        'orchid' => 'DA70D6',
        'palegoldenrod' => 'EEE8AA',
        'palegreen' => '98FB98',
        'paleturquoise' => 'AFEEEE',
        'palevioletred' => 'DB7093',
        'papayawhip' => 'FFEFD5',
        'peachpuff' => 'FFDAB9',
        'peru' => 'CD853F',
        'pink' => 'FFC0CB',
        'plum' => 'DDA0DD',
        'powderblue' => 'B0E0E6',
        'purple' => '800080',
        'red' => 'FF0000',
        'rosybrown' => 'BC8F8F',
        'royalblue' => '4169E1',
        'saddlebrown' => '8B4513',
        'salmon' => 'FA8072',
        'sandybrown' => 'F4A460',
        'seagreen' => '2E8B57',
        'seashell' => 'FFF5EE',
        'sienna' => 'A0522D',
        'silver' => 'C0C0C0',
        'skyblue' => '87CEEB',
        'slateblue' => '6A5ACD',
        'slategray' => '708090',
        'slategrey' => '708090',
        'snow' => 'FFFAFA',
        'springgreen' => '00FF7F',
        'steelblue' => '4682B4',
        'tan' => 'D2B48C',
        'teal' => '008080',
        'thistle' => 'D8BFD8',
        'tomato' => 'FF6347',
        'turquoise' => '40E0D0',
        'violet' => 'EE82EE',
        'wheat' => 'F5DEB3',
        'white' => 'FFFFFF',
        'whitesmoke' => 'F5F5F5',
        'yellow' => 'FFFF00',
        'yellowgreen' => '9ACD32'];

    private int $red;
    private int $green;
    private int $blue;
    private int $alpha;

    public function __construct(string $color)
    {
        if (isset(self::STANDARD_HTML_COLORS[$color])) {
            $color = self::STANDARD_HTML_COLORS[$color];
        } elseif (isset(self::EMS_COLORS[$color])) {
            $color = self::EMS_COLORS[$color];
        } else {
            $color = \trim($color, '#');
            if (3 == \strlen($color)) {
                $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
            }
            if (4 == \strlen($color)) {
                $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2].$color[3].$color[3];
            }
        }
        $this->red = (int) \hexdec(\substr($color, 0, 2));
        $this->green = (int) \hexdec(\substr($color, 2, 2));
        $this->blue = (int) \hexdec(\substr($color, 4, 2));
        $this->alpha = \intval(\hexdec(\substr($color, 6, 2)) / 2);
    }

    public function getRed(): int
    {
        return $this->red;
    }

    public function setRed(int $red): void
    {
        $this->red = $red;
    }

    public function getGreen(): int
    {
        return $this->green;
    }

    public function setGreen(int $green): void
    {
        $this->green = $green;
    }

    public function getBlue(): int
    {
        return $this->blue;
    }

    public function setBlue(int $blue): void
    {
        $this->blue = $blue;
    }

    public function getAlpha(): int
    {
        return $this->alpha;
    }

    public function setAlpha(int $alpha): void
    {
        $this->alpha = $alpha;
    }

    public function getColorId(\GdImage $image): int
    {
        $identifier = \imagecolorallocatealpha(
            $image,
            $this->getRed(),
            $this->getGreen(),
            $this->getBlue(),
            $this->getAlpha(),
        );
        if (false === $identifier) {
            throw new \RuntimeException('Unexpected false image color identifier');
        }

        return $identifier;
    }

    public function relativeLuminance(): float
    {
        $components = [
            'r' => $this->getRed() / 255.0,
            'g' => $this->getGreen() / 255.0,
            'b' => $this->getBlue() / 255.0,
        ];
        foreach ($components as $c => $v) {
            if ($v <= 0.03928) {
                $components[$c] = $v / 12.92;
            } else {
                $components[$c] = (($v + 0.055) / 1.055) ** 2.4;
            }
        }

        return ($components['r'] * 0.2126) + ($components['g'] * 0.7152) + ($components['b'] * 0.0722);
    }

    public function contrastRatio(Color $color2): float
    {
        $y1 = $this->relativeLuminance();
        $y2 = $color2->relativeLuminance();
        if ($y1 < $y2) {
            return ($y2 + 0.05) / ($y1 + 0.05);
        }

        return ($y1 + 0.05) / ($y2 + 0.05);
    }

    public function getComplementary(): Color
    {
        $complementary = clone $this;
        $complementary->setRed(255 - $this->red);
        $complementary->setGreen(255 - $this->green);
        $complementary->setBlue(255 - $this->blue);

        return $complementary;
    }

    public function getRGB(): string
    {
        return \sprintf('#%\'.02X%\'.02X%\'.02X', $this->red, $this->green, $this->blue);
    }

    public function getRGBA(): string
    {
        return \sprintf('#%\'.02X%\'.02X%\'.02X%\'.02X', $this->red, $this->green, $this->blue, $this->alpha);
    }
}
