<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit;

use EMS\ClientHelperBundle\Helper\InlineEdit\Dto\RenderPayload;
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

    /**
     * @return array<string, string>
     */
    public function render(RenderPayload $payload): array
    {
        $template = $this->twig->load('@EMSClientHelper/inlineEdit/render.html.twig');

        return [
            '.editor-topbar' => $template->renderBlock('header'),
            '.editor-sidebar' => $template->renderBlock('sidebar', [
                'payload' => $payload,
            ]),
        ];
    }

    public function renderEditor(EmschRequest $request, string $path): string
    {
        return $this->twig->render('@EMSClientHelper/inlineEdit/editor.html.twig', [
            'iframeUrl' => $request->getEmschRoutePrefix().$path,
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
