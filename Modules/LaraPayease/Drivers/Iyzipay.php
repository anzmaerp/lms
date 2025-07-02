<?php

namespace Modules\LaraPayease\Drivers;

use App\Services\OrderService;
use Modules\LaraPayease\BasePaymentDriver;
use Modules\LaraPayease\Traits\Currency;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Iyzipay\Model\Address;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\CheckoutForm;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Model\Locale;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Options;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;
use Iyzipay\Request\RetrieveCheckoutFormRequest;
use Symfony\Component\HttpFoundation\Response;

class Iyzipay extends BasePaymentDriver
{

    use Currency;
    protected $options;

    public function __construct()
    {
        $this->options = new Options();
    }

    protected function setOptions()
    {
        $settings = $this->getKeys();
        $this->options->setApiKey($settings['api_key']);
        $this->options->setSecretKey($settings['secret_key']);
        $this->options->setBaseUrl($this->getBaseUrl());
    }

    public function chargeCustomer(array $params){
        try{
            $this->setOptions();
            $order = (new OrderService())->getOrdeWrWithItem($params['order_id'], ['items','countryDetails']);
            $request = new CreateCheckoutFormInitializeRequest();
            $request->setLocale(Locale::EN);
            $request->setConversationId(uniqid());
            $request->setPrice($this->chargeableAmount($order->amount, 1));
            $request->setPaidPrice($this->chargeableAmount($order->amount, 1));
            $request->setCurrency($this->getCurrency());
            $request->setBasketId($order->id);
            $request->setPaymentGroup(PaymentGroup::PRODUCT);
            $request->setCallbackUrl($params['ipn_url']);

            // Buyer Information
            $buyer = new Buyer();
            $buyer->setId($order->user_id);
            $buyer->setName($order->first_name);
            $buyer->setSurname($order->last_name);
            $buyer->setEmail($order->email);
            $buyer->setIdentityNumber($order->countryDetails?->short_code == 'TR' ? '00000000000' : '11111111111');
            $buyer->setRegistrationAddress($order->city . " ". $order->state . " ". $order->countryDetails->name);
            $buyer->setIp(request()->ip());
            $buyer->setCity($order->city);
            $buyer->setCountry($order->countryDetails->name);
            $request->setBuyer($buyer);

            // Shipping Address
            $shippingAddress = new Address();
            $shippingAddress->setContactName($order->first_name . ' ' . $order->last_name);
            $shippingAddress->setCity($order->city);
            $shippingAddress->setCountry($order->countryDetails->name);
            $shippingAddress->setAddress($order->city . " ". $order->state . " ". $order->countryDetails->name);
            $request->setShippingAddress($shippingAddress);

            // Billing Address
            $billingAddress = new Address();
            $billingAddress->setContactName($order->first_name . ' ' . $order->last_name);
            $billingAddress->setCity($order->city);
            $billingAddress->setCountry($order->countryDetails->name);
            $billingAddress->setAddress($order->city . " ". $order->state . " ". $order->countryDetails->name);
            $request->setBillingAddress($billingAddress);

            // Basket Items
            $basketItems = [];
            foreach ($order->items as $item) {
                $firstBasketItem = new BasketItem();
                $firstBasketItem->setId($item->id);
                $firstBasketItem->setName($item->title);
                $firstBasketItem->setCategory1("Education");
                $firstBasketItem->setItemType(BasketItemType::VIRTUAL);
                $firstBasketItem->setPrice($this->chargeableAmount($item->total, 1));
                $basketItems[] = $firstBasketItem;
            }

            $request->setBasketItems($basketItems);

            $checkoutForm =  CheckoutFormInitialize::create($request, $this->options);
            if($checkoutForm->getStatus() == 'success') {
                return redirect()->away($checkoutForm->getPaymentPageUrl());
            }
            return ['status' => Response::HTTP_BAD_REQUEST,'message' =>  $checkoutForm->getErrorMessage() ?? 'Payment initialization failed.'];
        } catch (Exception $ex) {
            Log::info($ex);
            return ['status' => Response::HTTP_BAD_REQUEST,'message' => $ex->getMessage()];
        }
    }

    public function driverName() : string{
        return 'iyzipay';
    }

    protected function getBaseUrl() {
        return $this->getMode() == 'test' ? 'https://sandbox-api.iyzipay.com' : 'https://api.iyzipay.com';
    }

    public function paymentResponse(array $params = [])
    {
        if (empty($params['token'])) {
            return ['status' => Response::HTTP_BAD_REQUEST];
        }
        
        $this->setOptions();
        
        $retrieveRequest = new RetrieveCheckoutFormRequest();
        $retrieveRequest->setLocale(app()->getLocale());
        $retrieveRequest->setConversationId(uniqid());
        $retrieveRequest->setToken($params['token']);
        
        $checkoutForm = CheckoutForm::retrieve($retrieveRequest, $this->options);
        if ($checkoutForm->getStatus() == "success") {
            return [
                'status' => Response::HTTP_OK,
                'data'   => [
                    'transaction_id' => $checkoutForm->getPaymentId(),
                    'order_id'       => $checkoutForm->getBasketId(),
                ]
            ];
        } else {
            return ['status' => Response::HTTP_BAD_REQUEST];
        }

    }
}
