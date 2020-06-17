<?php


namespace Tests\Mocks;

use App\Payment;
use App\Payment\Contracts\PaymentHandlerContract;
use App\Payment\Handlers\MercadoPagoHandler;
use App\Product;
use App\Repositories\PaymentRepository;
use App\Repositories\ProductRepository;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MercadoPago\Item as MercadoPagoItem;
use MercadoPago\MerchantOrder as MercadoPagoMerchantOrder;
use MercadoPago\Payment as MercadoPagoPayment;
use Symfony\Component\HttpFoundation\Response;

final class MercadoPagoPaymentHandlerMock implements PaymentHandlerContract
{
  private PaymentHandlerContract $originalHandler;
  private Payment $paymentMock;
  private array $productsMock;
  private int $preferencePaymentMockId;

  public function __construct(Payment $paymentMock, int $preferencePaymentMockId, array $productsMock, PaymentRepository $paymentRepository, ProductRepository $productRepository)
  {
    $this->preferencePaymentMockId = $preferencePaymentMockId;
    $this->paymentMock = $paymentMock;
    $this->productsMock = $productsMock;
    $this->originalHandler = new MercadoPagoHandler($paymentRepository, $productRepository);
  }

  public function setupCredentials(): void
  {
    $this->originalHandler->setupCredentials();
  }

  /**
   * @param $id
   * @return MercadoPagoMerchantOrder
   * @throws Exception
   */
  public function findMerchantOrderById($id)
  {
    $itemsWithActualProducts = Collection::make($this->productsMock)->map(function ($item) {
      return [
        'product' => Product::findOrFail($item['product']),
        'amount' => $item['amount']
      ];
    });

    $totalPrice = $itemsWithActualProducts->reduce(function ($item, $totalPrice) {
      return $totalPrice + $item['product']->price * $item['amount'];
    }, 0);

    return new MercadoPagoMerchantOrder([
      'id' => $id,
      'shipments' => [],
      'status' => 'closed',
      'externalReference' => 'default',
      'preferenceId' => $id,
      'marketplace' => 'none',
      'notification_url' => $this->originalHandler->notificationUrl,
      'date_created' => '2019-04-02T14:35:35.000-04:00',
      'sponsor_id' => null,
      'shipping_cost' => 0,
      'site_id' => rand(0, 15),
      'refunded_amount' => 0,
      'cancelled' => false,
      'additional_info' => '',
      'total_amount' => $totalPrice,
      'paid_amount' => $totalPrice,
      'items' => $itemsWithActualProducts->map(function ($item) {
        /** @var Product $product */
        $product = $item['product'];
        $amount = $item['amount'];

        return new MercadoPagoItem([
          'id' => "{$this->paymentMock->id}_{$product->id}",
          'title' => $product->title,
          'quantity' => $amount,
          'unit_price' => $product->price,
          'currency_id' => config('app.payment_currency')
        ]);
      }),
      'payments' => [
        new MercadoPagoPayment([
          'id' => $this->preferencePaymentMockId,
          'transaction_amount' => $totalPrice,
          'total_paid_amount' => $totalPrice,
          'currency_id' => config('app.payment_currency'),
          'status' => 'approved',
          'status_detail' => 'accredited',
          'operation_type' => 'regular_payment',
          'date_approved' => '2019-04-02T14:35:35.000-04:00',
          'date_created' => '2019-04-02T14:35:35.000-04:00',
          'last_modified' => '2019-04-02T14:35:35.000-04:00',
          'amount_refunded' => 0,
        ])
      ]
    ]);
  }

  /**
   * @param $id
   * @return mixed
   * @throws Exception
   */
  public function findItemById($id)
  {
    return $this->findMerchantOrderById($id)->getAttributes()['payments'][0];
  }

  /**
   * @param User $user
   * @param string $userName
   * @param string $originIpAddress
   * @param array $items
   * @return Response
   * @throws Exception
   */
  public function handleCheckout(User $user, string $userName, string $originIpAddress, array $items): Response
  {
    return $this->originalHandler->handleCheckout($user, $userName, $originIpAddress, $items);
  }

  /**
   * @param Request $request
   * @return Response
   */
  public function handleNotification(Request $request): Response
  {
    return $this->originalHandler->handleNotification($request);
  }
}
