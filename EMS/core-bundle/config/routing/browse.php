<?php

declare(strict_types=1);

use EMS\CoreBundle\Controller\BrowseController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('emsco_browse_uploaded_files', '/uploaded-files')
        ->controller([BrowseController::class, 'modalUploadedFiles'])
        ->methods(['GET'])
        ->format('json')
    ;
};
