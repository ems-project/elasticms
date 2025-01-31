<?php

declare(strict_types=1);

namespace App\CLI\Client\Photos;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @template photo_array of array{Z_PK: int, ZUUID: string, ZORIGINALFILENAME: string, ZANALYSISSTATEMODIFICATIONDATE: float|null, ZADDEDDATE: float, ZDATECREATED: float|null, ZLATITUDE: float|null, ZLONGITUDE: float|null}
 */
class ApplePhotosLibrary implements PhotosLibraryInterface
{
    private readonly \SQLite3 $photosDatabase;

    public function __construct(private readonly string $libraryPath)
    {
        $this->photosDatabase = new \SQLite3($this->libraryPath.'/database/Photos.sqlite', SQLITE3_OPEN_READONLY);
    }

    #[\Override]
    public function photosCount(): int
    {
        return (int) $this->photosDatabase->querySingle('select count(*) from ZASSET');
    }

    /**
     * @return iterable<Photo>
     */
    #[\Override]
    public function getPhotos(): iterable
    {
        $results = $this->photosDatabase->query('SELECT ASSET.Z_PK, ASSET.ZUUID, ASSET.ZANALYSISSTATEMODIFICATIONDATE, ASSET.ZADDEDDATE, ASSET.ZDATECREATED, ASSET.ZLATITUDE, ASSET.ZLONGITUDE, ATTR.ZORIGINALFILENAME FROM ZASSET ASSET, ZADDITIONALASSETATTRIBUTES ATTR WHERE ATTR.ZASSET = ASSET.Z_PK ORDER BY COALESCE(ASSET.ZANALYSISSTATEMODIFICATIONDATE, ASSET.ZADDEDDATE)  ASC');
        if (false === $results) {
            throw new \RuntimeException('Unexpected false result');
        }
        while ($tmpRow = $results->fetchArray()) {
            /** @var photo_array $row */
            $row = $tmpRow;
            yield $this->generatePhoto($row);
        }
    }

    /**
     * @param photo_array $row
     */
    private function generatePhoto(array $row): Photo
    {
        $photo = new Photo('ApplePhotos', $this->libraryPath, \strtolower($row['ZUUID']), $row['ZORIGINALFILENAME']);

        $photo->setModificationDate($this->cocoaToDate($row['ZANALYSISSTATEMODIFICATIONDATE'] ?? $row['ZADDEDDATE']));
        $photo->setAddedDate($this->cocoaToDate($row['ZADDEDDATE']));
        $photo->addMemberOf($this->getAlbums($row['Z_PK']));
        if (-180.0 !== ($row['ZLATITUDE'] ?? -180.0) || -180.0 !== ($row['ZLONGITUDE'] ?? -180.0)) {
            $photo->setLocationPoint($row['ZLATITUDE'] ?? -180.0, $row['ZLONGITUDE'] ?? -180.0);
        }

        return $photo;
    }

    #[\Override]
    public function getPreviewFile(Photo $photo): ?SplFileInfo
    {
        $zuuid = \strtoupper($photo->getOuuid());
        $firstChar = \substr($zuuid, 0, 1);
        $finder = new Finder();
        $finder->name("$zuuid*");
        foreach ($finder->in("$this->libraryPath/resources/derivatives/$firstChar") as $file) {
            return $file;
        }

        return null;
    }

    #[\Override]
    public function getOriginalFile(Photo $photo): ?SplFileInfo
    {
        $zuuid = \strtoupper($photo->getOuuid());
        $firstChar = \substr($zuuid, 0, 1);
        $finder = new Finder();
        $finder->name("$zuuid*");
        foreach ($finder->in("$this->libraryPath/originals/$firstChar") as $file) {
            return $file;
        }

        return null;
    }

    /**
     * @return mixed[][]
     */
    private function getAlbums(int $assetId): array
    {
        $results = $this->photosDatabase->query("SELECT * FROM Z_28ASSETS, ZGENERICALBUM WHERE Z_3ASSETS = $assetId AND Z_PK = Z_28ALBUMS");
        if (false === $results) {
            throw new \RuntimeException('Unexpected false result');
        }
        $albums = [];
        while ($row = $results->fetchArray()) {
            if (!isset($row['ZTITLE'])) {
                continue;
            }
            $albums[] = [
                'type' => 'album',
                'name' => $row['ZTITLE'],
                'parent' => 'photo_album:'.\strtolower((string) $row['ZUUID']),
                'order' => $row['Z_FOK_3ASSETS'],
            ];
        }

        return $albums;
    }

    private function cocoaToDate(float $cocoaDate): \DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat('U.u', \sprintf('%F', $cocoaDate + 978_307_200));
        if (false === $date) {
            throw new \RuntimeException("Unexpected false result: $cocoaDate");
        }

        return $date;
    }
}
