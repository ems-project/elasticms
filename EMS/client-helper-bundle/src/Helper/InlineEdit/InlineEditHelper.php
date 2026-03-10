<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit;

use EMS\ClientHelperBundle\Helper\InlineEdit\Dto\PayloadDto;
use EMS\ClientHelperBundle\Helper\InlineEdit\Dto\RenderDto;
use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use EMS\ClientHelperBundle\Routes;
use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
     * @return array<mixed>
     */
    public function createDraft(PayloadDto $payload): array
    {
        if (null === $element = $payload->element) {
            throw new BadRequestHttpException('element not found');
        }

        $draft = $this->coreBridge->data($element->emsLink->getContentType())->initDraft($element->emsLink->getOuuid());
        /** @var array{revisionId: int} $response */
        $response = $draft->response();

        return [
            'draftId' => $response['revisionId'],
            'render' => [
                '.editor-actions' => $this->getTemplateRender()->renderBlock('actions', [
                    'draftId' => $response['revisionId'],
                ]),
                '.editor-sidebar-content' => $this->getTemplateRender()->renderBlock('sidebarDraft', [
                    'element' => $element,
                ]),
            ],
        ];
    }

    /**
     * @return array{
     *     render: array<string, string>,
     *     elements: string[]
     * }
     */
    public function render(PayloadDto $payload): array
    {
        $emsLinks = $payload->getEmsLinks();
        $info = [] !== $emsLinks ? $this->coreBridge->info()->documents([], ...$emsLinks) : null;

        $dto = new RenderDto($payload, $info);

        return [
            'render' => [
                '.editor-title' => $this->getTemplateRender()->renderBlock('title', ['render' => $dto]),
                '.editor-sidebar-content' => $this->getTemplateRender()->renderBlock('sidebar', ['render' => $dto]),
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

    private function getTemplateRender(): TemplateWrapper
    {
        return $this->twig->load('@EMSClientHelper/inlineEdit/render.html.twig');
    }
}
