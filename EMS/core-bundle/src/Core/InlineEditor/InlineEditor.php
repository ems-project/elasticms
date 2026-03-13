<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\InlineEditor;

use EMS\CommonBundle\Common\EMSLinkCollection;
use EMS\CoreBundle\Core\InlineEditor\Dto\ElementDto;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\Channel\ChannelRegistrar;
use EMS\CoreBundle\Service\DataService;
use EMS\CoreBundle\Service\Revision\RevisionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\TemplateWrapper;

readonly class InlineEditor
{
    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private RevisionService $revisionService,
        private DataService $dataService,
    ) {
    }

    public function apiSave(int $draftId, ElementDto $element, string $content): bool
    {
        if (null === $revision = $this->revisionService->find($draftId)) {
            throw new \RuntimeException('Revision not found');
        }

        $autoSave = $revision->getAutoSave() ?? [];
        $propertyAccess = PropertyAccess::createPropertyAccessor();
        $propertyAccess->setValue($autoSave, $element->path, $content);
        $this->revisionService->autoSave($revision, $autoSave);

        return true;
    }

    public function apiDiscard(int $draftId): bool
    {
        if (null === $revision = $this->revisionService->find($draftId)) {
            throw new \RuntimeException('Revision not found');
        }

        $this->dataService->discardDraft($revision);

        return true;
    }

    public function apiEdit(ElementDto $element): InlineEditorResponse
    {
        $draft = $this->dataService->initNewDraft($element->emsLink->getContentType(), $element->emsLink->getOuuid());
        $infos = $this->revisionService->getInfos(EMSLinkCollection::fromEmsIds([$draft->getEmsLink()]));
        $info = $infos[$draft->giveOuuid()];

        return new InlineEditorResponse(['draftId' => $draft->getId()])
            ->render('.editor-actions', $this->getTemplateRender()->renderBlock('actions', [
                'draftId' => $draft->getId(),
            ]))
            ->render('.editor-sidebar-content', $this->getTemplateRender()->renderBlock('edit', [
                'element' => $element,
                'info' => $info,
            ]))
        ;
    }

    /**
     * @param ElementDto[] $elements
     */
    public function apiInit(array $elements): InlineEditorResponse
    {
        $emsIds = \array_values(\array_map(fn (ElementDto $element) => $element->emsId, $elements));
        $infos = [] !== $emsIds ? $this->revisionService->getInfos(EMSLinkCollection::fromEmsIds($emsIds)) : [];
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

        return new InlineEditorResponse(['elements' => $validSelectors])
            ->render('.editor-title', $title)
            ->render('.editor-sidebar-content', $this->getTemplateRender()->renderBlock('elements', [
                'infos' => $infos,
            ]));
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
