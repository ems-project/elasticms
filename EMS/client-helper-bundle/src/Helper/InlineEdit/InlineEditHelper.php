<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit;

use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use EMS\ClientHelperBundle\Routes;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\TemplateWrapper;

readonly class InlineEditHelper
{
    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function renderEditor(EmschRequest $request, string $path): string
    {
        return $this->twig->render('@EMSClientHelper/inlineEdit/editor.html.twig', [
            'iframeSrc' => $request->getEmschRoutePrefix().$path,
        ]);
    }

    public function renderInjectHead(): string
    {
        return $this->getTemplateInject()->renderBlock('head');
    }

    public function renderInjectBody(EmschRequest $request): string
    {
        $editorUrl = $this->urlGenerator->generate(Routes::INLINE_EDIT_EDITOR, [
            'path' => $request->getEmschPath(),
        ]);

        return $this->getTemplateInject()->renderBlock('body', [
            'editorUrl' => $editorUrl,
        ]);
    }

    private function getTemplateInject(): TemplateWrapper
    {
        return $this->twig->load('@EMSClientHelper/inlineEdit/inject.html.twig');
    }
}
