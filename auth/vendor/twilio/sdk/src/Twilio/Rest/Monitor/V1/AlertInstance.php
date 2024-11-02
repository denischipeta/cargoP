<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Monitor
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Monitor\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Values;
use Twilio\Version;
use Twilio\Deserialize;


/**
 * @property string|null $accountSid
 * @property string|null $alertText
 * @property string|null $apiVersion
 * @property \DateTime|null $dateCreated
 * @property \DateTime|null $dateGenerated
 * @property \DateTime|null $dateUpdated
 * @property string|null $errorCode
 * @property string|null $logLevel
 * @property string|null $moreInfo
 * @property string|null $requestMethod
 * @property string|null $requestUrl
 * @property string|null $requestVariables
 * @property string|null $resourceSid
 * @property string|null $responseBody
 * @property string|null $responseHeaders
 * @property string|null $sid
 * @property string|null $url
 * @property string|null $requestHeaders
 * @property string|null $serviceSid
 */
class AlertInstance extends InstanceResource
{
    /**
     * Initialize the AlertInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     * @param string $sid The SID of the Alert resource to fetch.
     */
    public function __construct(Version $version, array $payload, string $sid = null)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'accountSid' => Values::array_get($payload, 'account_sid'),
            'alertText' => Values::array_get($payload, 'alert_text'),
            'apiVersion' => Values::array_get($payload, 'api_version'),
            'dateCreated' => Deserialize::dateTime(Values::array_get($payload, 'date_created')),
            'dateGenerated' => Deserialize::dateTime(Values::array_get($payload, 'date_generated')),
            'dateUpdated' => Deserialize::dateTime(Values::array_get($payload, 'date_updated')),
            'errorCode' => Values::array_get($payload, 'error_code'),
            'logLevel' => Values::array_get($payload, 'log_level'),
            'moreInfo' => Values::array_get($payload, 'more_info'),
            'requestMethod' => Values::array_get($payload, 'request_method'),
            'requestUrl' => Values::array_get($payload, 'request_url'),
            'requestVariables' => Values::array_get($payload, 'request_variables'),
            'resourceSid' => Values::array_get($payload, 'resource_sid'),
            'responseBody' => Values::array_get($payload, 'response_body'),
            'responseHeaders' => Values::array_get($payload, 'response_headers'),
            'sid' => Values::array_get($payload, 'sid'),
            'url' => Values::array_get($payload, 'url'),
            'requestHeaders' => Values::array_get($payload, 'request_headers'),
            'serviceSid' => Values::array_get($payload, 'service_sid'),
        ];

        $this->solution = ['sid' => $sid ?: $this->properties['sid'], ];
    }

    /**
     * Generate an instance context for the instance, the context is capable of
     * performing various actions.  All instance actions are proxied to the context
     *
     * @return AlertContext Context for this AlertInstance
     */
    protected function proxy(): AlertContext
    {
        if (!$this->context) {
            $this->context = new AlertContext(
                $this->version,
                $this->solution['sid']
            );
        }

        return $this->context;
    }

    /**
     * Fetch the AlertInstance
     *
     * @return AlertInstance Fetched AlertInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): AlertInstance
    {

        return $this->proxy()->fetch();
    }

    /**
     * Magic getter to access properties
     *
     * @param string $name Property to access
     * @return mixed The requested property
     * @throws TwilioException For unknown properties
     */
    public function __get(string $name)
    {
        if (\array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        if (\property_exists($this, '_' . $name)) {
            $method = 'get' . \ucfirst($name);
            return $this->$method();
        }

        throw new TwilioException('Unknown property: ' . $name);
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string
    {
        $context = [];
        foreach ($this->solution as $key => $value) {
            $context[] = "$key=$value";
        }
        return '[Twilio.Monitor.V1.AlertInstance ' . \implode(' ', $context) . ']';
    }
}

