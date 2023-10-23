<?php
class PaymentClient
{

    private $publicKey = '5b6492f0-f8f5-11ea-976a-0242c0a85007';

    private $pass = 'd0ec0beca8a3c30652746925d5380cf3';

    private $remotePaymentUri = 'https://dev-api.rafinita.com/post';

    public function __construct()
    {
        echo $this->start();
    }

    public function __destruct()
    {
        echo $this->finish();
    }

    public function executeQuery(array $postParams)
    {
        if ($this->validatePostParams($postParams)) {
            $postParams = $this->addCredentials($postParams);
            echo  '{' . implode(':', $postParams) . '}' . PHP_EOL;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->remotePaymentUri);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);

            echo $output;
        }
    }

    private function addCredentials($postParams)
    {
        $postParams['client_key'] = $this->publicKey;
        $postParams['hash'] =  md5(
            strtoupper(strrev($postParams['payer_email']) .
                $this->pass .
            strrev(substr($postParams['card_number'], 0, 6) .
                substr($postParams['card_number'], -4)))
        );
        echo  $postParams['hash'] . PHP_EOL;

        return $postParams;
    }

    private function validatePostParams($postParams): bool
    {
        if (!$postParams['payer_email']) {
            throw \Exception('email field must not be empty!');
        }
        if (!$postParams['card_number']) {
            throw \Exception('card_number field must not be empty!');
        }

        return true;
    }

    private function start(): string
    {
        return 'started at: ' . date('Y-m-d H:i:s') . PHP_EOL;
    }

    private function finish(): string
    {
        return PHP_EOL . 'finished at: ' . date('Y-m-d H:i:s') . PHP_EOL;
    }
}

$myCard = [
    'action' => 'SALE',
    'order_id' => 'OO-12345',
    'order_amount' => '1.22',
    'order_currency' => 'USD',
    'order_description' => 'test order',
    'card_number' => '4111111111111111',
    'card_exp_month' => '01',
    'card_exp_year' => '2025',
    'card_cvv2' => '000',
    'payer_first_name' => 'NAME1',
    'payer_last_name' => 'NAME2',
    'payer_address' => 'Victory avenue, bdg. n85',
    'payer_country' => 'UA',
    'payer_city' => 'Kyiv',
    'payer_zip' => '10101',
    'payer_email' => 'client@yopmail.com',
    'payer_phone' => '+380991234567',
    'payer_ip' => '192.168.56.1',
    'term_url_3ds' => 'https://example.com/backurl',
];

$paymentClient = new PaymentClient();
$paymentClient->executeQuery($myCard);
