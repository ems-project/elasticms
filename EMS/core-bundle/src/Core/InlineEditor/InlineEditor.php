<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\InlineEditor;

use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Common\EMSLinkCollection;
use EMS\CoreBundle\Core\InlineEditor\Dto\ElementDto;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\Channel\ChannelRegistrar;
use EMS\CoreBundle\Service\Revision\RevisionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\TemplateWrapper;

readonly class InlineEditor
{
    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private RevisionService $revisionService,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function createDraft(): array
    {
        //        if (null === $element = $payload->element) {
        //            throw new BadRequestHttpException('element not found');
        //        }

        return [];

        //        $draft = $this->coreBridge->data($element->emsLink->getContentType())->initDraft($element->emsLink->getOuuid());
        //        /** @var array{revisionId: int} $response */
        //        $response = $draft->response();
        //
        //        return [
        //            'draftId' => $response['revisionId'],
        //            'render' => [
        //                '.editor-actions' => $this->getTemplateRender()->renderBlock('actions', [
        //                    'draftId' => $response['revisionId'],
        //                ]),
        //                '.editor-sidebar-content' => $this->getTemplateRender()->renderBlock('sidebarDraft', [
        //                    'element' => $element,
        //                ]),
        //            ],
        //        ];
    }

    /**
     * @param ElementDto[] $elements
     *
     * @return array{
     *     render: array<string, string>,
     *     elements: string[]
     * }
     */
    public function apiInit(array $elements): array
    {
        $emsIds = \array_values(\array_map(fn (ElementDto $element) => $element->emsId, $elements));
        $infos = $this->revisionService->getInfos(EMSLinkCollection::fromEmsIds($emsIds));
        $validSelectors = [];
        $title = 'Inline Editor';

        foreach ($elements as $element) {
            $elementInfo = $infos[$element->emsLink->getOuuid()] ?? null;
            if (null === $elementInfo) {
                continue;
            }

            if ('h1' === $element->tag) {
                $title = $elementInfo['label'];
            }

            $infos[$element->emsLink->getOuuid()]['elements'][] = $element;
            $validSelectors[] = $element->selector;
        }

        return [
            'render' => [
                '.editor-title' => $title,
                '.editor-sidebar-content' => $this->getTemplateRender()->renderBlock('elements', [
                    'infos' => $infos,
                ]),
            ],
            'elements' => $validSelectors,
        ];
    }

    public function renderEditor(string $channel, ?string $path): string
    {
        $prefix = \sprintf('/channel/%s', $channel);

        return $this->twig->render('@EMSAdminUI/inline-editor/editor.html.twig', [
            'baseUrl' => $this->urlGenerator->generate(Routes::INLINE_EDIT_EDITOR, ['channel' => $channel]),
            'iframeUrl' => $prefix.$path,
            'routePrefix' => $prefix,
        ]);
    }

    public function renderInjectHead(): string
    {
        return $this->getTemplateInject()->renderBlock('head');
    }

    public function renderInjectBody(Request $request): string
    {
        $channel = $request->attributes->getString(ChannelRegistrar::ATTRIBUTE_CHANNEL_NAME);
        $routePrefix = \sprintf('/channel/%s', $channel);

        $editorUrl = $this->urlGenerator->generate(Routes::INLINE_EDIT_EDITOR, [
            'path' => \substr($request->getPathInfo(), \strlen($routePrefix)),
            'channel' => $channel,
        ]);

        return $this->getTemplateInject()->renderBlock('body', [
            'editorUrl' => $editorUrl,
        ]);
    }

    private function getTemplateInject(): TemplateWrapper
    {
        return $this->twig->load('@EMSAdminUI/inline-editor/inject.html.twig');
    }

    private function getTemplateRender(): TemplateWrapper
    {
        return $this->twig->load('@EMSAdminUI/inline-editor/render.html.twig');
    }
}
