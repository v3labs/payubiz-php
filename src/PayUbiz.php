<?php

namespace V3labs\PayUbiz;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PayUbiz
{
    const PRODUCTION_URL = 'https://secure.payu.in/_payment.php';
    const TEST_URL       = 'https://test.payu.in/_payment.php';

    /** @var string */
    private $merchantId;

    /** @var string */
    private $secretKey;

    /** @var bool */
    private $testMode;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $resolver = (new OptionsResolver())
            ->setDefaults(['testMode' => true])
            ->setRequired(['merchantId', 'secretKey', 'testMode'])
            ->setAllowedTypes('merchantId', 'string')
            ->setAllowedTypes('secretKey', 'string')
            ->setAllowedTypes('testMode', 'bool');

        $options = $resolver->resolve($options);

        $this->merchantId = $options['merchantId'];
        $this->secretKey  = $options['secretKey'];
        $this->testMode   = $options['testMode'];
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @return bool
     */
    public function getTestMode()
    {
        return $this->testMode;
    }

    /**
     * @return string
     */
    public function getServiceUrl()
    {
        return $this->testMode ? self::TEST_URL : self::PRODUCTION_URL;
    }

    /**
     * @return array
     */
    public function getChecksumParams()
    {
        return array_merge(
            ['txnid', 'amount', 'productinfo', 'firstname', 'email'],
            array_map(function($i) { return "udf{$i}"; }, range(1, 10))
        );
    }

    /**
     * @param array $params
     * @return string
     */
    private function getChecksum(array $params)
    {
        $values = array_map(
            function($field) use ($params) {
                return array_key_exists($field, $params) ? $params[$field] : '';
            },
            $this->getChecksumParams()
        );

        $values = array_merge([$this->getMerchantId()], $values, [$this->getSecretKey()]);

        return hash('sha512', implode('|', $values));
    }

    /**
     * @param array $params
     * @return Response
     */
    public function purchase(array $params)
    {
        $requiredParams = ['txnid', 'amount', 'firstname', 'email', 'phone', 'productinfo', 'surl', 'furl'];

        $params = array_merge(
            $this->sanitizeParams($params),
            ['hash' => $this->getChecksum($params), 'key' => $this->getMerchantId()]
        );

        $params = array_map(
            function($param) { return htmlentities($param, ENT_QUOTES, 'UTF-8', false); },
            $params
        );

        $output = sprintf('<form id="payment_form" method="POST" action="%s">', $this->getServiceUrl());

        foreach ($params as $key => $value) {
            $output .= sprintf('<input type="text" name="%s" value="%s" />', $key, $value);
        }

        $output .= '<input id="payment_form_submit" type="submit" value="Proceed to PayUbiz" />' .
            '</form>' .
            '<script>
                document.getElementById(\'payment_form_submit\').style.display = \'none\';
                document.getElementById(\'payment_form\').submit();
            </script>';

        return new Response($output);
    }

    public function completePurchase(array $params)
    {
        return new CompletePurchaseResponse($this, $params);
    }

    private function sanitizeParams(array $params)
    {
        foreach (['address1', 'address2', 'city', 'state', 'country', 'productinfo', 'email', 'phone'] as $field) {
            if (isset($params[$field])) {
                $params[$field] = preg_replace('/[^a-zA-Z0-9\-_@\/\s.]/', '', $params[$field]);
            }
        }

        return $params;
    }
}