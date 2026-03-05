<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit;

use EMS\ClientHelperBundle\Helper\InlineEdit\Dto\RenderDto;
use EMS\ClientHelperBundle\Helper\InlineEdit\Dto\RenderPayloadDto;
use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use EMS\ClientHelperBundle\Routes;
use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\TemplateWrapper;

readonly class InlineEditHelper
{
    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private CoreBridgeInterface $coreBridge
    ) {
    }

    /**
     * @return array{
     *     render: array<string, string>,
     *     elements: string[]
     * }
     */
    public function render(RenderPayloadDto $payload): array
    {
        $emsLinks = $payload->getEmsLinks();
        $info = [] !== $emsLinks ? $this->coreBridge->info()->documents([], ...$emsLinks) : null;

        $dto = new RenderDto($payload, $info);
        $template = $this->twig->load('@EMSClientHelper/inlineEdit/render.html.twig');
        $context = ['render' => $dto];

        return [
            'render' => [
                '.editor-topbar' => $template->renderBlock('header', $context),
                '.editor-sidebar-content' => $template->renderBlock('sidebar', $context),
            ],
            'elements' => $dto->elements,
        ];
    }

    public function renderEditor(EmschRequest $request, ?string $path): string
    {
        return $this->twig->render('@EMSClientHelper/inlineEdit/editor.html.twig', [
            'iframeUrl' => $request->getEmschRoutePrefix().$path,
            'routePrefix' => $request->getEmschRoutePrefix(),
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
