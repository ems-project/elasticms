# Handle Submitted data
When you are using forms, you probably also want to handle the submitted data.
The [SubmissionBundle](https://github.com/ems-project/EMSSubmissionBundle) provides default handlers by implementing the `EMS\FormBundle\Handler\AbstractHandler`.

## Using your own handlers
If the SubmissionBundle is not acting like you need to you can build your own handlers.
Inspire yourself on the implementations found in the [SubmissionBundle](https://github.com/ems-project/EMSSubmissionBundle), email for example:

```php
<?php
//EmailHandler.php    
namespace EMS\SubmissionBundle\Handler;    
//...
class EmailHandler extends AbstractHandler
{
    //... setup removed for simplicity
    public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config, AbstractResponse $previousResponse = null): AbstractResponse
    {
        try {
            //render the email template
        } catch (\Exception $exception) {
            return new FailedResponse(sprintf('Submission failed, contact your admin. %s', $exception->getMessage()));
        }

        // other checks / manipulations

        return new EmailResponse(AbstractResponse::STATUS_SUCCESS);
    }
}
```

```php
<?php
//EmailResponse()
namespace EMS\SubmissionBundle\Submit;

class EmailResponse extends AbstractResponse
{
    public function __construct(string $status)
        {
            parent::__construct($status, 'Submission send by mail.');
        }
}
```

Let the form-bundle find your handler by tagging it:
```xml
<service id="emss.emailhandler" class="EMS\SubmissionBundle\Handler\EmailHandler">
    <!-- ... arguments -->
    <tag name="emsf.handler" />
</service>
```

## Chained Handlers
Handlers are called one-by-one, each handler's response is collected and available for the next Handler.
In the Handlers `handle` function the previous response object is passed.

```php
<?php
//EmailHandler.php
namespace EMS\SubmissionBundle\Handler;
//...
public function handle(SubmissionConfig $submission, FormInterface $form, FormConfig $config, AbstractResponse $previousResponse = null): AbstractResponse
    {
        try {
            //the previous response is passed to the twig rendering engine and can be exploited there:
            $renderedSubmission = $this->renderer->render($submission, $form, $config, $previousResponse);
            $email = new EmailConfig($renderedSubmission);
            $message = (new \Swift_Message($email->getSubject()))
                ->setFrom($email->getFrom())
                ->setTo($email->getEndpoint())
                ->setBody($email->getBody());
        } catch (\Exception $exception) {
            return new FailedResponse(sprintf('Submission failed, contact your admin. %s', $exception->getMessage()));
        }

        // other checks / manipulations

        return new EmailResponse(AbstractResponse::STATUS_SUCCESS);
    }
```
