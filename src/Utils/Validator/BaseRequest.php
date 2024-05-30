<?php

namespace ISerranoDev\CrudGenerator\Utils\Validator;


use App\Utils\Tools\APIJsonResponse;
use App\Utils\Tools\Util;
use Symfony\Contracts\Translation\TranslatorInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseRequest
{

    protected ?string $appVersion = null;
    protected string $userOS = 'unknown';

    public function __construct(
        protected ValidatorInterface $validator
    )
    {

        $this->populate();

        if ($this->autoValidateRequest()) {
            $this->validate();
        }
    }

    public function validate()
    {
        $errors = $this->validator->validate($this);

        $messages = ['errors' => []];

        /** @var ConstraintViolation  */
        foreach ($errors as $message) {
            $messages['errors'][] = [
                'property' => $message->getPropertyPath(),
                'value' => $message->getInvalidValue(),
                'message' => $message->getMessage(),
            ];
        }

        if (count($messages['errors']) > 0) {
            $response = new APIJsonResponse($messages, false, "Error de la solicitud", 201);
            $response->headers->add([
                'Access-Control-Allow-Origin' => '*'
            ]);
            $response->send();
            exit();
        }
    }

    public function getRequest(): Request
    {
        return Request::createFromGlobals();
    }

    protected function populate(): void
    {

        try {
            $requestParams = $this->getRequest()->toArray();
        } catch (\Exception $e) {
            $requestParams = $this->getRequest()->request->all();
        }

        if($this->includeQueryParams()){
            $requestParams = array_merge($requestParams, $this->getRequest()->query->all());
        }
        if($this->includeFiles()){
            $requestParams = array_merge($requestParams, $this->getRequest()->files->all());
        }

        // Get files from request
        /*
        $requestFiles = $this->getRequest()->files->all();
        if($requestFiles) {
            $requestParams = array_merge($requestParams, $requestFiles);
        }
        */

        $typesMap = $this->detectValidTypes();


        foreach ($requestParams as $property => $value) {
            if (array_key_exists('version', $requestParams)) {
                $this->appVersion = $requestParams['version'];
            }

            if (array_key_exists('os', $requestParams)) {
                $this->userOS = $requestParams['os'];
            }else{
                $this->userOS = 'unknown';
            }


            if (property_exists($this, $property)) {



                $this->{$property} = $value;

                /*
                if(@$typesMap[$property] and $typesMap[$property] == "date") {
                    $datetimeValue = DateTime::createFromFormat('Y-m-d', $value) ?: null;
                    $this->{$property} = $datetimeValue ? date($datetimeValue->format('Y-m-d')) : null;
                }
                */

            }


        }


    }


    public function detectValidTypes(): array {
        // Obtain property attributes for constraints defined in child request class
        $reflectionClass = new ReflectionClass(get_called_class());
        $typesMap = []; // to save types and check during value population

        foreach ($reflectionClass->getProperties() as $property) {
            $attributes = $property->getAttributes();
            $propertyName = $property->getName();
            if(count($attributes) > 0) {
                foreach ($attributes as $attribute) {
                    if($attribute->getName() == Type::class) {
                        $typeValue = $attribute->getArguments()[0];
                        if($typeValue) {
                            $typesMap[$propertyName] = $typeValue;
                        }
                    }
                }
            }
        }

        return $typesMap;
    }

    public function get(string $fieldName)
    {
        return @$this->{$fieldName};
    }

    protected function autoValidateRequest(): bool
    {
        return true;
    }

    protected function includeQueryParams(): bool
    {
        return false;
    }

    protected function includeFiles(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->getRequest()->headers->get('User-Agent');
    }


    public function getOs(): string
    {
        return $this->userOS;
    }

    public function getAppVersionNumber(): ?string
    {

        return $this->appVersion;
    }

    public function getIp(): string
    {
        return $this->getRequest()->getClientIp();
    }

    public function getUri()
    {
        return $this->getRequest()->getUri();
    }
}