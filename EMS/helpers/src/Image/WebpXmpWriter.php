<?php
declare(strict_types=1);
namespace EMS\Helpers\Image;

final class WebpXmpWriter
{
    /**
     * Bit flags in VP8X first byte.
     * Layout: Rsv|I|L|E|X|A|R|Reserved...
     * In byte form, XMP = 0x04, EXIF = 0x08, ALPHA = 0x10, ICCP = 0x20, ANIM = 0x02.
     */
    private const VP8X_FLAG_XMP = 0x04;

    public function writeFile(string $inputPath, string $outputPath, string $xmpXml): void
    {
        $data = @file_get_contents($inputPath);
        if ($data === false) {
            throw new \RuntimeException("Impossible de lire le fichier: {$inputPath}");
        }

        $result = $this->upsertXmp($data, $xmpXml);

        if (@file_put_contents($outputPath, $result) === false) {
            throw new \RuntimeException("Impossible d'écrire le fichier: {$outputPath}");
        }
    }

    public function upsertXmp(string $webpBinary, string $xmpXml): string
    {
        $this->assertWebp($webpBinary);

        $chunks = $this->parseChunks($webpBinary);

        $hasVp8x = false;
        $hasXmp = false;
        $newChunks = [];

        foreach ($chunks as $chunk) {
            if ($chunk['fourcc'] === 'VP8X') {
                $hasVp8x = true;
                $chunk['data'] = $this->setVp8xXmpFlag($chunk['data']);
                $chunk['size'] = strlen($chunk['data']);
                $newChunks[] = $chunk;
                continue;
            }

            if ($chunk['fourcc'] === 'XMP ') {
                if (!$hasXmp) {
                    $chunk['data'] = $xmpXml;
                    $chunk['size'] = strlen($xmpXml);
                    $newChunks[] = $chunk;
                    $hasXmp = true;
                }
                // Si plusieurs XMP existent, on ignore les suivants.
                continue;
            }

            $newChunks[] = $chunk;
        }

        if (!$hasVp8x) {
            $vp8xChunk = $this->buildMinimalVp8xChunkFromImageChunk($chunks);
            array_unshift($newChunks, $vp8xChunk);
        }

        if (!$hasXmp) {
            $newChunks[] = [
                'fourcc' => 'XMP ',
                'size' => strlen($xmpXml),
                'data' => $xmpXml,
            ];
        }

        return $this->buildWebp($newChunks);
    }

    /**
     * @return array<int, array{fourcc:string,size:int,data:string}>
     */
    private function parseChunks(string $data): array
    {
        $length = strlen($data);
        $offset = 12; // RIFF + size + WEBP
        $chunks = [];

        while ($offset + 8 <= $length) {
            $fourcc = substr($data, $offset, 4);
            $size = $this->unpackUint32LE(substr($data, $offset + 4, 4));
            $payloadOffset = $offset + 8;
            $payload = substr($data, $payloadOffset, $size);

            if (strlen($payload) !== $size) {
                throw new \RuntimeException("Chunk tronqué: {$fourcc}");
            }

            $chunks[] = [
                'fourcc' => $fourcc,
                'size' => $size,
                'data' => $payload,
            ];

            $offset = $payloadOffset + $size + ($size % 2);
        }

        return $chunks;
    }

    /**
     * Rebuild full RIFF/WEBP file.
     *
     * @param array<int, array{fourcc:string,size:int,data:string}> $chunks
     */
    private function buildWebp(array $chunks): string
    {
        $body = 'WEBP';

        foreach ($chunks as $chunk) {
            $payload = $chunk['data'];
            $size = strlen($payload);

            $body .= $chunk['fourcc'];
            $body .= pack('V', $size);
            $body .= $payload;

            if (($size % 2) === 1) {
                $body .= "\x00";
            }
        }

        return 'RIFF' . pack('V', strlen($body)) . $body;
    }

    private function assertWebp(string $data): void
    {
        if (strlen($data) < 12) {
            throw new \RuntimeException('Fichier trop court.');
        }

        if (substr($data, 0, 4) !== 'RIFF' || substr($data, 8, 4) !== 'WEBP') {
            throw new \RuntimeException('Le fichier n’est pas un WebP RIFF valide.');
        }
    }

    private function unpackUint32LE(string $bytes): int
    {
        $value = unpack('V', $bytes);
        if (!is_array($value) || !isset($value[1])) {
            throw new \RuntimeException('Impossible de lire un uint32 little-endian.');
        }

        return (int)$value[1];
    }

    private function setVp8xXmpFlag(string $vp8xData): string
    {
        if (strlen($vp8xData) < 10) {
            throw new \RuntimeException('Chunk VP8X invalide: payload < 10 octets.');
        }

        $firstByte = ord($vp8xData[0]);
        $firstByte |= self::VP8X_FLAG_XMP;

        return chr($firstByte) . substr($vp8xData, 1);
    }

    /**
     * Builds a minimal VP8X chunk from VP8 / VP8L dimensions.
     * Limited to still images.
     *
     * @param array<int, array{fourcc:string,size:int,data:string}> $chunks
     * @return array{fourcc:string,size:int,data:string}
     */
    private function buildMinimalVp8xChunkFromImageChunk(array $chunks): array
    {
        foreach ($chunks as $chunk) {
            if ($chunk['fourcc'] === 'VP8 ') {
                [$width, $height] = $this->extractDimensionsFromVp8($chunk['data']);
                return $this->makeVp8xChunk($width, $height, self::VP8X_FLAG_XMP);
            }

            if ($chunk['fourcc'] === 'VP8L') {
                [$width, $height] = $this->extractDimensionsFromVp8l($chunk['data']);
                return $this->makeVp8xChunk($width, $height, self::VP8X_FLAG_XMP);
            }
        }

        throw new \RuntimeException('Impossible de créer VP8X: chunk VP8/VP8L introuvable.');
    }

    /**
     * VP8 frame header extraction.
     * We look for the key frame start code 0x9d 0x01 0x2a and read width/height next.
     *
     * @return array{0:int,1:int}
     */
    private function extractDimensionsFromVp8(string $data): array
    {
        if (strlen($data) < 10) {
            throw new \RuntimeException('Chunk VP8 trop court.');
        }

        $pos = strpos($data, "\x9d\x01\x2a");
        if ($pos === false || ($pos + 7) > strlen($data)) {
            throw new \RuntimeException('En-tête VP8 non reconnu.');
        }

        $widthRaw = unpack('v', substr($data, $pos + 3, 2))[1];
        $heightRaw = unpack('v', substr($data, $pos + 5, 2))[1];

        $width = $widthRaw & 0x3FFF;
        $height = $heightRaw & 0x3FFF;

        if ($width < 1 || $height < 1) {
            throw new \RuntimeException('Dimensions VP8 invalides.');
        }

        return [$width, $height];
    }

    /**
     * VP8L dimensions extraction from first 5 bytes:
     * signature 0x2f + packed 14-bit width-1 and 14-bit height-1.
     *
     * @return array{0:int,1:int}
     */
    private function extractDimensionsFromVp8l(string $data): array
    {
        if (strlen($data) < 5) {
            throw new \RuntimeException('Chunk VP8L trop court.');
        }

        if (ord($data[0]) !== 0x2f) {
            throw new \RuntimeException('Signature VP8L invalide.');
        }

        $b1 = ord($data[1]);
        $b2 = ord($data[2]);
        $b3 = ord($data[3]);
        $b4 = ord($data[4]);

        $widthMinus1 = $b1 | (($b2 & 0x3F) << 8);
        $heightMinus1 = (($b2 >> 6) & 0x03) | ($b3 << 2) | (($b4 & 0x0F) << 10);

        $width = $widthMinus1 + 1;
        $height = $heightMinus1 + 1;

        if ($width < 1 || $height < 1) {
            throw new \RuntimeException('Dimensions VP8L invalides.');
        }

        return [$width, $height];
    }

    /**
     * VP8X payload is 10 bytes:
     * 1 byte flags, 3 bytes reserved, 3 bytes width-1, 3 bytes height-1.
     *
     * @return array{fourcc:string,size:int,data:string}
     */
    private function makeVp8xChunk(int $width, int $height, int $flags): array
    {
        if ($width < 1 || $height < 1) {
            throw new \InvalidArgumentException('Dimensions invalides pour VP8X.');
        }

        $payload =
            chr($flags) .
            "\x00\x00\x00" .
            $this->packUint24LE($width - 1) .
            $this->packUint24LE($height - 1);

        return [
            'fourcc' => 'VP8X',
            'size' => 10,
            'data' => $payload,
        ];
    }

    private function packUint24LE(int $value): string
    {
        if ($value < 0 || $value > 0xFFFFFF) {
            throw new \InvalidArgumentException('Valeur uint24 hors limite.');
        }

        return chr($value & 0xFF)
            . chr(($value >> 8) & 0xFF)
            . chr(($value >> 16) & 0xFF);
    }
}