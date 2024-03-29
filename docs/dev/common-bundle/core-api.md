# Core API

The common bundle provides a contract for calling an elasticms (core) API.
This codes lives in common because an elasticms backend can call another backend through this api implementation.

## From Config

By setting the environmnet variable **EMS_BACKEND_URL**, you can inject a CoreApiInterface.
If **EMS_BACKEND_API_KEY** is defined, the coreApi will be authenticated.

## Creating a Core API instance

Create a new service using the [CoreApiFactoryInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/CoreApiFactoryInterface.php) contract.
Your service will be an instance of: [CoreApiInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/CoreApiInterface.php)

```xml
<service id="api_service" class="EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface">
    <factory service="EMS\CommonBundle\Contracts\CoreApi\CoreApiFactoryInterface" method="create"/>
    <argument>%emsch.backend_url%</argument>
</service>
```

## Example

```php
<?php

declare(strict_types=1);

use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiExceptionInterface;

final class Example
{
    private CoreApiInterface $api;

    public function __construct(CoreApiInterface $api)
    {
        $this->api = $api;
    }

    public function testData(string $contentType, array $data): void
    {
        $dataEndpoint = $this->api->data($contentType);

        $draft = $dataEndpoint->create($data);
        try {
            $ouuid = $dataEndpoint->finalize($draft->getRevisionId());
        } catch (CoreApiExceptionInterface $e) {
            $dataEndpoint->discard($draft->getRevisionId());
            throw $e;
        }

        $draftUpdate = $dataEndpoint->update($ouuid, ['test' => 'test']);
        try {
            $dataEndpoint->finalize($draftUpdate->getRevisionId());
        } catch (CoreApiExceptionInterface $e) {
            $dataEndpoint->discard($draftUpdate->getRevisionId());
        }
        
        $dataEndpoint->save($ouuid, ['test' => 'test2']);

        $dataEndpoint->delete($ouuid);
    }
}
```

## CoreApi
### Exceptions
> Each API interaction can throw the following **[CoreApiExceptionInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/CoreApiExceptionInterface.php)**:
* **[BaseUrlNotDefinedExceptionInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Exception/BaseUrlNotDefinedExceptionInterface.php)**
* **[NotAuthenticatedExceptionInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Exception/NotAuthenticatedExceptionInterface.php)**
* **[NotSuccessfulExceptionInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Exception/NotSuccessfulExceptionInterface.php)**

### Authentication
* **authenticate**(string $username, string $password): [CoreApiInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/CoreApiInterface.php)
    > Provide EMS login credentials, and it will return an authenticated Core API instance. Throws [NotAuthenticatedExceptionInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Exception/NotAuthenticatedExceptionInterface.php)
* **isAuthenticated**(): bool
### Endpoints
* **data**(string $contentType): [DataInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/Data/DataInterface.php)
* **user**(): [UserInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/User/UserInterface.php)
* **file**(): [FileInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/File/FileInterface.php)
### Extra
* **getBaseUrl**(): string
    > Throws [BaseUrlNotDefinedExceptionInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Exception/BaseUrlNotDefinedExceptionInterface.php)
* **getToken**(): string
    > Before call isAuthenticated, otherwise you will receive an error.
* **setLogger**(LoggerInterface $logger): void
    > Used for overwriting the logger in CLI.
* **test**: void
    > Test if the api available

## Endpoints
### Data ([DataInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/Data/DataInterface.php))
* **create**(array $rawData, ?string $ouuid = null): [DraftInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/Data/DraftInterface.php)
    > When no ouuid is provided elasticms will generate the ouuid. You can receive the generated ouuid by calling finalize. 
* **delete**(string $ouuid): bool
* **discard**(int $revisionId): bool
* **finalize**(int $revisionId): string
    > Return the ouuid if successfully finalized.
* **get**(string $ouuid): [RevisionInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/Data/RevisionInterface.php)
* **replace**(string $ouuid, array $rawData): [DraftInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/Data/DraftInterface.php)
* **update**(string $ouuid, array $rawData): [DraftInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/Data/DraftInterface.php)
    > Will merge the passed rawData with the current rawData.
* **save**(string $ouuid, array $rawData, int $mode = DataInterface::MODE_UPDATE): int
    > Save (create, update or replace) the raw for the given ouuid. 
### User ([UserInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/User/UserInterface.php))
* **getProfiles**(): array
    > Return an array of [ProfileInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/User/ProfileInterface.php) instances
* **getProfileAuthenticated**(): [ProfileInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/User/ProfileInterface.php)
### File ([FileInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/File/FileInterface.php))
* **hashFile**(string $filename): string
    > Return a hash for a given filename
* **initUpload**(string $hash, int $size, string $filename, string $mimetype): int
    > Resume an upload to the returned byte 
* **addChunk**(string $hash, string $chunk): int
    > Add a chunk to an in progress upload 
* **uploadFile**(string $realPath, string $mimeType = null): ?string
    > Upload a file. If the mimetype is not provided a mimetype will be guessed. It returns the file's hash
* **headFile**(string $realPath): ?string
    > Tests if a given file has been already uploaded
## From ([FormInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/Form/FormInterface.php))
* **createVerification**(string $value): string
    > Create a new form verification value
* **getVerification**(string $value): string
    > Get a created form verification value
* **submit**(array $data): array
  > Submit form data, returns an array with submission_id and submission info
* **getSubmission**(string $submissionId, ?string $property = null): array
  > Pass a property for filtering the response, for example '[expireData]', '[data][firstName]' or '[files][0][filename]'
* **getSubmissionFile**(string $submissionId, ?string $submissionFileId): StreamedResponse
  > Returns a new proxy streamed response [Symfony\Component\HttpFoundation\StreamedResponse]
  > Because the file information is inside the headers (mimeType, size, name)
  > The header 'Content-Disposition' is forced by the core api to 'inline', this for security reason otherwise the file is directly downloaded. 

### Search ([SearchInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/Search/SearchInterface.php))
* **search**([Search](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Search/Search.php) $search): ResponseInterface
    > Perform a remote search based on the [Search](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Search/Search.php) object
* **count**([Search](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Search/Search.php) $search): int
    > Count the document matching the [Search](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Search/Search.php) object
* **scroll**([Search](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Search/Search.php) $search, int $scrollSize = 10, string $expireTime = '3m'): [Scroll](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Common/CoreApi/Search/Scroll.php)
    > Return a scroller looping on all documents match the [Search](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Search/Search.php) object
* **version**(): string
    > Return the cluster version
* **healthStatus**(): string
    > Return the cluster status [gren, yellow or red]
* **refresh**(?string $index = null): bool
    > Refresh the index
* **getIndicesFromAlias**(string $alias): string[]
    > Return the indices containing the $alias
* **getAliasesFromIndex**(string $index): string[]
    > Return the aliases containing the index
* **getDocument**(string $index, ?string $contentType, string $id, string[] $sourceIncludes = [], string[] $sourcesExcludes = []): DocumentInterface
    > Return the aliases containing the index

### DataExtract ([DataExtractInterface](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Contracts/CoreApi/Endpoint/File/DataExtractInterface.php))
* **get**(string $hash): string
    > Return an associative array with all data extracted from an asset/file identified by the hash parameter
