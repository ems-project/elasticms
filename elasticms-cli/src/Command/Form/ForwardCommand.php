<?php

namespace App\CLI\Command\Form;

use App\CLI\Client\WebToElasticms\Helper\Url;
use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\Helpers\Html\Headers;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class ForwardCommand extends AbstractCommand
{
    protected static $defaultName = Commands::FORM_FORWARD;

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
        if (!$this->adminHelper->getCoreApi()->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $this->io->section(\sprintf('Forward the form %s to %s', $this->fromUuid, $this->toUrl->getUrl()));
        $submission = $this->adminHelper->getCoreApi()->form()->getSubmission($this->fromUuid);
        $locale = Type::string($submission['locale'] ?? null);
        $client = new CurlHttpClient();
        $request = $client->request('POST', $this->toUrl->getUrl($locale), [
            'headers' => [
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
            ],
            'body' => Json::encode($submission['data'] ?? []),
        ]);
        $response = Json::decode($request->getContent());
        $submitUrl = \str_replace('init-form/', 'form/', $this->toUrl->getUrl($locale));
        $crawler = new Crawler($response['response'], $submitUrl);
        $form = $crawler->filter('form')->form();
        $formData = new FormDataPart($form->getValues());
        $headers = $formData->getPreparedHeaders();
        foreach (($request->getHeaders()[Headers::SET_COOKIE] ?? []) as $setCookie) {
            $cookie = Cookie::fromString(Type::string($setCookie));
            $headers->addHeader(Headers::COOKIE, \sprintf('%s=%s', $cookie->getName(), \rawurlencode($cookie->getValue() ?? '')));
        }
        $headers->addHeader(Headers::X_HASHCASH, $this->computeHashcash(Type::string($form->getValues()['form[_token]'] ?? null), Type::integer($response['difficulty'])));
        $httpResponse = $client->request('POST', $form->getUri(), [
            'headers' => $headers->toArray(),
            'body' => $formData->bodyToString(),
        ]);
        if (200 !== $httpResponse->getStatusCode()) {
            $this->io->error(\sprintf('Unexpected %d return code', $httpResponse->getStatusCode()));

            return self::EXECUTE_ERROR;
        }

        return self::EXECUTE_SUCCESS;
    }

    private function computeHashcash(string $token, int $difficulty): string
    {
        $hashcashLevel = \intval(\floor(\log($difficulty, 2) / 4.0));
        $regex = \sprintf('/^0{%d}/', $hashcashLevel);

        do {
            $random = $this->generateRandomString();
            $hash = \hash('sha256', \implode('|', [$difficulty, $token, $random]));
        } while (!\preg_match($regex, $hash));

        return \implode('|', [$hash, $random, $token]);
    }

    private function generateRandomString(): string
    {
        $characters = '0123456789';
        $charactersLength = \strlen($characters);
        $randomString = '';

        for ($i = 0; $i < 13; ++$i) {
            $randomString .= $characters[\random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
