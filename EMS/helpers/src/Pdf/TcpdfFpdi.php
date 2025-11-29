<?php

declare(strict_types=1);

namespace EMS\Helpers\Pdf;

use setasign\Fpdi\Tcpdf\Fpdi as BaseFpdi;

class TcpdfFpdi extends BaseFpdi
{
    public function __construct(
        $orientation = 'P',
        $unit = 'mm',
        $format = 'A4',
        $unicode = true,
        $encoding = 'UTF-8',
        $diskcache = false,
        $pdfa = false
    ) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->tcpdflink = false;
    }
}
