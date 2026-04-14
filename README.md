# BJB QRIS MPM PHP SDK

PHP SDK for integrating with Bank BJB Open API (SNAP BI standard) for QRIS Merchant Presented Mode (MPM) payments via IBM API Connect.

## Installation

```bash
composer require bjb/qrismpm-sdk
```

## Quick Start

```php
use Bjb\QrisMpm\Client\QrisClient;

$client = new QrisClient([
    'baseUrl'      => 'https://devapi.bankbjb.co.id',
    'clientId'     => 'your_client_id',
    'clientSecret' => 'your_client_secret',
    'channelId'    => '95221',
    'privateKey'   => file_get_contents('./keys/private-key.pem'),
    'logger'       => function ($msg) { error_log($msg); }, // optional
]);

// Generate QR
$result = $client->qris()->generate([
    'partnerReferenceNo' => 'QRIS' . randomNumber(),
    'amount' => ['value' => '50000', 'currency' => 'IDR'],
    'feeAmount' => ['value' => '0.00', 'currency' => 'IDR'],
    'merchantId' => '825670157598976',
    'subMerchantId' => '-',
    'storeId' => '-',
    'terminalId' => '825670157598976',
    'validityPeriod' => '2026-12-31T23:59:59+07:00',
    'additionalInfo' => [
        'paymentId' => 1,
        'merchantCode' => '6282120596296',
        'backendUrl' => 'https://your-domain.com/callback',
        'channelId' => '3',
        'transactionPurpose' => '3rd party merchant payment',
    ],
]);

echo $result['qrContent']; // QR string

// Check Status
$status = $client->qris()->checkStatus([
    'originalReferenceNo' => $result['referenceNo'],
    'originalPartnerReferenceNo' => $result['partnerReferenceNo'],
    'serviceCode' => '50',
    'merchantId' => '825670157598976',
    'additionalInfo' => [
        'PaymentId' => 1,
        'MerchantCode' => '6282120596296',
        'Currency' => 'IDR',
    ],
]);

echo $status['latestTransactionStatus']; // "00" = success
```

## Callback Verification

```php
use Bjb\QrisMpm\Crypto\CallbackVerifier;

$verifier = new CallbackVerifier('your_client_secret');

$isValid = $verifier->verify(
    method: 'POST',
    path: '/api/v1/callback/qris-mpm',
    accessToken: str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? ''),
    body: file_get_contents('php://input'),
    timestamp: $_SERVER['HTTP_X_TIMESTAMP'] ?? '',
    signature: $_SERVER['HTTP_X_SIGNATURE'] ?? '',
);

if (!$isValid) {
    http_response_code(401);
    echo json_encode(['ResponseCode' => '4015200', 'ResponseMessage' => 'Unauthorized']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
// Process payment...

echo json_encode(['ResponseCode' => '2005200', 'ResponseMessage' => 'Request has been processed successfully']);
```

## Laravel Integration

```php
// app/Http/Controllers/QrisController.php
namespace App\Http\Controllers;

use Bjb\QrisMpm\Client\QrisClient;
use Bjb\QrisMpm\Crypto\CallbackVerifier;
use Bjb\QrisMpm\Exception\ApiException;
use Illuminate\Http\Request;

class QrisController extends Controller
{
    private QrisClient $client;

    public function __construct()
    {
        $this->client = new QrisClient([
            'baseUrl'      => config('services.bjb.base_url'),
            'clientId'     => config('services.bjb.client_id'),
            'clientSecret' => config('services.bjb.client_secret'),
            'channelId'    => config('services.bjb.channel_id'),
            'privateKey'   => file_get_contents(config('services.bjb.private_key_path')),
        ]);
    }

    public function generate(Request $request)
    {
        try {
            $result = $this->client->qris()->generate($request->all());
            return response()->json($result);
        } catch (ApiException $e) {
            return response()->json($e->toArray(), 400);
        }
    }

    public function checkStatus(Request $request)
    {
        try {
            $result = $this->client->qris()->checkStatus($request->all());
            return response()->json($result);
        } catch (ApiException $e) {
            return response()->json($e->toArray(), 400);
        }
    }

    public function callback(Request $request)
    {
        $verifier = new CallbackVerifier(config('services.bjb.client_secret'));

        $isValid = $verifier->verify(
            method: 'POST',
            path: '/api/v1/callback/qris-mpm',
            accessToken: str_replace('Bearer ', '', $request->header('Authorization', '')),
            body: $request->getContent(),
            timestamp: $request->header('X-TIMESTAMP', ''),
            signature: $request->header('X-SIGNATURE', ''),
        );

        if (!$isValid) {
            return response()->json(['ResponseCode' => '4015200', 'ResponseMessage' => 'Unauthorized'], 401);
        }

        // Process payment...

        return response()->json(['ResponseCode' => '2005200', 'ResponseMessage' => 'Request has been processed successfully']);
    }
}
```

Laravel config (`config/services.php`):

```php
'bjb' => [
    'base_url' => env('BJB_BASE_URL', 'https://devapi.bankbjb.co.id'),
    'client_id' => env('BJB_CLIENT_ID'),
    'client_secret' => env('BJB_CLIENT_SECRET'),
    'channel_id' => env('BJB_CHANNEL_ID', '95221'),
    'private_key_path' => env('BJB_PRIVATE_KEY_PATH', base_path('keys/private-key.pem')),
],
```

## Error Handling

```php
use Bjb\QrisMpm\Exception\ApiException;

try {
    $result = $client->qris()->generate($payload);
} catch (ApiException $e) {
    echo $e->responseCode;    // "4014700"
    echo $e->responseMessage; // "Unauthorized. Signature mismatch"
}
```

## Error Codes

| Code | Description |
|------|-------------|
| `2007300` | Access token granted |
| `2004700` | QR generated successfully |
| `2005100` | Check status successful |
| `4014700` | Unauthorized / Signature mismatch |
| `4044700` | Transaction not found |
| `4044708` | Invalid merchant |
| `5004700` | General error |

## Environments

| Environment | Base URL |
|-------------|----------|
<!-- | Development | `https://devapi.bankbjb.co.id` | -->
<!-- | Production  | `https://api.bankbjb.co.id` | -->

Dev environment uses a self-signed certificate — the SDK automatically skips SSL verification when the base URL contains `devapi`.

## Publish to Packagist

1. Push to GitHub
2. Go to https://packagist.org/packages/submit
3. Enter your GitHub repo URL
4. Submit

## License

MIT
