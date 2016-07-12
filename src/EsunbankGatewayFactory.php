<?php

namespace PayumTW\Esunbank;

use Detection\MobileDetect;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use PayumTW\Esunbank\Action\CaptureAction;
use PayumTW\Esunbank\Action\ConvertPaymentAction;
use PayumTW\Esunbank\Action\StatusAction;

class EsunbankGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name'           => 'esunbank',
            'payum.factory_title'          => 'Esunbank',
            'payum.action.capture'         => new CaptureAction(),
            'payum.action.status'          => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'MID'     => '',
                'M'       => '',
                'desktop' => $this->isDesktop(),
                'sandbox' => true,
            ];

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['MID', 'M'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }

    /**
     * isDesktop.
     *
     * @return bool [description]
     */
    protected function isDesktop()
    {
        $detect = new MobileDetect();

        return $detect->isMobile() === false && $detect->isTablet() === false;
    }
}
