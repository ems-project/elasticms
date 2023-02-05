<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Revision\Wysiwyg;

use EMS\CoreBundle\Core\User\UserManager;
use EMS\CoreBundle\Service\WysiwygStylesSetService;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class WysiwygRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly WysiwygStylesSetService $wysiwygStylesSetService,
        private readonly UserManager $userManager,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function getInfo(): string
    {
        $config = $this->getConfig();
        $config['imageUploadUrl'] = $this->urlGenerator->generate('ems_image_upload_url');
        $config['imageBrowser_listUrl'] = $this->urlGenerator->generate('ems_images_index');
        $config['ems_filesUrl'] = $this->urlGenerator->generate('ems_core_uploaded_file_wysiwyg_index');

        return Json::encode([
            'config' => $config,
            'styles' => $this->getStyles(),
        ]);
    }

    /**
     * @return array<mixed>
     */
    private function getConfig(): array
    {
        $profile = $this->userManager->getUser()?->getWysiwygProfile();

        if (null === $profile || null === $profileConfig = $profile->getConfig()) {
            return [];
        }

        $config = Json::decode($profileConfig);

        if (isset($config['ems']['paste'])) {
            $config['emsAjaxPaste'] = $this->urlGenerator->generate('emsco_wysiwyg_ajax_paste', [
                'wysiwygProfileId' => $profile->getId(),
            ]);
        }

        return $config;
    }

    /**
     * @return array<mixed>
     */
    private function getStyles(): array
    {
        $styles = [];
        $styleSets = $this->wysiwygStylesSetService->getStylesSets();

        foreach ($styleSets as $styleSet) {
            $styles[] = [
                'name' => $styleSet->getName(),
                'config' => Json::decode($styleSet->getConfig()),
            ];
        }

        return $styles;
    }
}
