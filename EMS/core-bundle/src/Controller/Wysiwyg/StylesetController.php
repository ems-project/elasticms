<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Wysiwyg;

use EMS\ClientHelperBundle\Helper\Asset\AssetHelperRuntime;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CoreBundle\Service\WysiwygStylesSetService;
use EMS\Helpers\Html\Headers;
use ScssPhp\ScssPhp\Compiler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StylesetController extends AbstractController
{
    public function __construct(private readonly WysiwygStylesSetService $wysiwygStylesSetService, private readonly AssetHelperRuntime $assetHelperRuntime, private readonly string $templateNamespace)
    {
    }

    public function iframe(string $name, string $language): Response
    {
        $splitLanguage = \explode('_', $language);

        return $this->render("@$this->templateNamespace/wysiwyg_styles_set/iframe.html.twig", [
            'styleSet' => $this->wysiwygStylesSetService->getByName($name),
            'language' => \array_shift($splitLanguage),
        ]);
    }

    public function prefixedCSS(string $name): Response
    {
        $styleSet = $this->wysiwygStylesSetService->getByName($name);
        if (null === $styleSet) {
            throw new NotFoundHttpException(\sprintf('Style Set %s not found', $name));
        }
        $css = $styleSet->getContentCss();
        if (null === $css) {
            throw new NotFoundHttpException(\sprintf('CSS not specified for %s', $name));
        }
        $sha1 = isset($styleSet->getAssets()[EmsFields::CONTENT_FILE_HASH_FIELD]) ? $styleSet->getAssets()[EmsFields::CONTENT_FILE_HASH_FIELD] : null;
        if (!\is_string($sha1)) {
            throw new NotFoundHttpException(\sprintf('Assets archive not specified for %s', $name));
        }
        $directory = $this->assetHelperRuntime->setVersion($sha1);
        $filename = \implode(DIRECTORY_SEPARATOR, [$directory, $css]);
        if (!\file_exists($filename)) {
            throw new NotFoundHttpException(\sprintf('File %s not found', $css));
        }
        $css = \file_get_contents($filename);
        $compiler = new Compiler();
        $response = new Response($compiler->compileString(".ems-styleset-$name {
            all: initial;
            $css
        }", $directory)->getCss());
        $response->headers->set(Headers::CONTENT_TYPE, 'text/css');

        return $response;
    }
}
