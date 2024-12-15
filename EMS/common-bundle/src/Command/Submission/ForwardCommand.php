<?php

namespace EMS\CommonBundle\Command\Submission;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Helper\Url;
use EMS\Helpers\Html\Headers;
use EMS\Helpers\Html\MimeTypes;
use EMS\Helpers\Security\HashcashToken;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\Response\StreamableInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class ForwardCommand extends AbstractCommand
{
    protected static $defaultName = Commands::SUBMISSION_FORWARD;

    public const ARG_FORM_UUID_FROM = 'form-uuid';
    public const ARG_FORM_URL_TO = 'post-url';
    private string $fromUuid;
    private Url $toUrl;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Forward a form submission form the admin to a form\'s url')
            ->addArgument(
                self::ARG_FORM_UUID_FROM,
                InputArgument::REQUIRED,
                'Source form\'s UUID'
            )->addArgument(
                self::ARG_FORM_URL_TO,
                InputArgument::REQUIRED,
                'Init form POST URL'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->fromUuid = $this->getArgumentString(self::ARG_FORM_UUID_FROM);
        $this->toUrl = new Url($this->getArgumentString(self::ARG_FORM_URL_TO));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->section(\sprintf('Forward the form %s to %s', $this->fromUuid, $this->toUrl->getUrl()));
        $submission = $this->adminHelper->getCoreApi()->form()->getSubmission($this->fromUuid);

        if (null !== ($submission['processId'] ?? null) || null !== ($submission['processBy'] ?? null)) {
            $this->io->error(\sprintf('Submission %s has been already processed by %s', $this->fromUuid, $submission['processBy'] ?? $submission['processId'] ?? 'N/A'));

            return self::EXECUTE_ERROR;
        }

        $data = $submission['data'] ?? [];
        $data = \array_filter($data, fn ($field) => !\is_array($field) || !empty($field));
        $locale = Type::string($submission['locale']);
        $client = new CurlHttpClient();
        $request = $client->request('POST', $this->toUrl->getUrl($locale), [
            'headers' => [
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
            ],
            'body' => Json::encode($data),
        ]);
        $response = Json::decode($request->getContent());
        $submitUrl = \str_replace('init-form/', 'form/', $this->toUrl->getUrl($locale));
        $crawler = new Crawler($response['response'], $submitUrl);
        $form = $crawler->filter('form')->form();
        $data = $form->getValues();
        foreach ($submission['files'] ?? [] as $file) {
            $fileResponse = $this->adminHelper->getCoreApi()->form()->getSubmissionFile($this->fromUuid, $file['id']);
            if (!$fileResponse instanceof StreamableInterface) {
                throw new \RuntimeException('no stream response');
            }
            $data[\sprintf('form[%s]', $file['formField'])] = new DataPart($fileResponse->toStream(), $file['filename'] ?? 'filename', $file['mimeType'] ?? MimeTypes::APPLICATION_OCTET_STREAM);
        }
        $formData = new FormDataPart($data);

        $headers = $formData->getPreparedHeaders();
        foreach (($request->getHeaders()[Headers::SET_COOKIE] ?? []) as $setCookie) {
            $cookie = Cookie::fromString(Type::string($setCookie));
            $headers->addHeader(Headers::COOKIE, \sprintf('%s=%s', $cookie->getName(), \rawurlencode($cookie->getValue() ?? '')));
        }
        $headers->addHeader(Headers::X_HASHCASH, HashcashToken::generate(Type::string($form->getValues()['form[_token]'] ?? null), Type::integer($response['difficulty']))->getHeader());
        $httpResponse = $client->request('POST', $form->getUri(), [
            'headers' => $headers->toArray(),
            'body' => $formData->bodyToString(),
        ]);
        if (200 !== $httpResponse->getStatusCode()) {
            $this->io->error(\sprintf('Unexpected %d return code', $httpResponse->getStatusCode()));

            return self::EXECUTE_ERROR;
        }
        $results = Json::decode($httpResponse->getContent());
        foreach (($results['summaries'] ?? []) as $result) {
            $this->io->writeln(\sprintf('%s with %s %s', $result['data'] ?? '', $result['status'] ?? '', $result['uid'] ?? ''));
        }

        return self::EXECUTE_SUCCESS;
    }
}
