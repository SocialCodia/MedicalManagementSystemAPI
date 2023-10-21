<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//use Slim\Factory\AppFactory
require '../vendor/autoload.php';
require_once '../include/DbHandler.php';
require_once '../vendor/autoload.php';
require_once '../include/JWT.php';
require_once __DIR__ . '/../vendor/autoload.php';
$JWT = new JWT;

$app = new \Slim\App;;

$app = new Slim\App([

    'settings' => [
        'displayErrorDetails' => true,
        'debug'               => true,
    ]
]);


$app->post('/register', function(Request $request, Response $response)
{
    if(!checkEmptyParameter(array('name','email','password'),$request,$response))
    {
        $db = new DbHandler();
        $requestParameter = $request->getParsedBody();
        $email = $requestParameter['email'];
        $password = $requestParameter['password'];
        $name = $requestParameter['name'];
        if (strlen($name)>30)
            return returnException(true,NAME_GRETER,$response);
        if (strlen($name)<4)
            return returnException(true,NAME_LOWER,$response);
        $name = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($name))))));
        $result = $db->createUser($name,$email,$password);
        if($result == USER_CREATION_FAILED)
            return returnException(true,USER_CREATION_FAILED,$response);
        else if($result == EMAIL_EXIST)
            return returnException(true,EMAIL_EXIST,$response);
        else if($result == USERNAME_EXIST)
            return returnException(true,USERNAME_EXIST,$response);
        else if($result == USER_CREATED){
            $code = $db->getCode(1);
            if(prepareVerificationMail($name,$email,$code))
               return returnException(false,EMAIL_VERIFICATION_SENT.$email,$response);
            else
               return returnException(true,EMAIL_VERIFICATION_SENT_FAILED,$response);
        }
        else if($result == VERIFICATION_EMAIL_SENT_FAILED)
            return returnException(true,EMAIL_VERIFICATION_SENT_FAILED,$response);
        else if($result == EMAIL_NOT_VALID)
            return returnException(true,EMAIL_NOT_VALID,$response);
    }
});

$app->get('/demo',function(Request $request, Response $response,array $args )
{
    $db = new DbHandler;
    $db->setUserId(819);
    // $users = array();
        $responseG = array();
        $responseG['data'] = $db->getPaymentsByCreditId(1082);
        $response->write(json_encode($responseG));
        return $response->withHeader(CT,AJ)
                ->withStatus(200);
});

$app->get('/pdf',function(Request $request, Response $response,array $args )
{
    include 'invoice.php';
    $mpdf = new \Mpdf\Mpdf(['orientation' => 'L']);
    $stylesheet = file_get_contents('css/b.css'); // external css
    // $stylesheet1 = file_get_contents('css/socialcodia.css'); // external css
    $mpdf->WriteHTML($stylesheet,1);
    // $mpdf->WriteHTML($stylesheet1,2);
    $mpdf->WriteHTML($content,2);
    echo $content;
    $mpdf->Output('doc.pdf','');
});

$app->get('/demo1',function(Request $request, Response $response,array $args )
{
    $db = new DbHandler;
    $db->setUserId(190);
    // $users = array();
        $responseG = array();
        $responseG['success'] = true;
        $responseG[ERROR] = false;
        $responseG[MESSAGE] = "Searching Users By Keywords";
        $responseG['data'] = $db->getNoticeProducts();
        $response->write(json_encode($responseG));
        return $response->withHeader(CT,AJ)
                ->withStatus(200);
});

$app->post('/login', function(Request $request, Response $response)
{
    if(!checkEmptyParameter(array('email','password'),$request,$response))
    {
        $db = new DbHandler;
        $requestParameter = $request->getParsedBody();
        $email = $requestParameter[EMAIL];
        $password = $requestParameter['password'];
        if (!$db->isEmailValid($email)) 
        {
            return returnException(true,EMAIL_NOT_VALID,$response);
        }
        if (!empty($email)) 
        {
            $result = $db->login($email,$password);
            if($result ==LOGIN_SUCCESSFULL)
            {
                $user = $db->getUserByEmail($email);
                $user[TOKEN] = getToken($user['id']);
                $responseUserDetails = array();
                $responseUserDetails[ERROR] = false;
                $responseUserDetails[MESSAGE] = LOGIN_SUCCESSFULL;
                $responseUserDetails[USER] = $user;
                $response->write(json_encode($responseUserDetails));
                return $response->withHeader(CT, AJ)
                         ->withStatus(200);
            }
            else if($result ==USER_NOT_FOUND)
                return returnException(true,USER_NOT_FOUND,$response);
            else if($result ==PASSWORD_WRONG)
                return returnException(true,PASSWORD_WRONG,$response);
            else if($result ==UNVERIFIED_EMAIL)
                return returnException(true,UNVERIFIED_EMAIL,$response);
            else
                return returnException(true,SWW,$response);
        }
        else
            return returnException(true,USER_NOT_FOUND,$response);
    }
});

$app->post('/product/update',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('productId','productName','productBrand','productCategory','productSize','productLocation','productPrice','productQuantity','productManufactureDate','productExpireDate'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $productId = $requestParameter['productId'];
                $productName = $requestParameter['productName'];
                $productBrand = $requestParameter['productBrand'];
                $productCategory = $requestParameter['productCategory'];
                $productSize = $requestParameter['productSize'];
                $productLocation = $requestParameter['productLocation'];
                $productPrice = $requestParameter['productPrice'];
                $productQuantity = $requestParameter['productQuantity'];
                $productManufactureDate = $requestParameter['productManufactureDate'];
                $productExpireDate = $requestParameter['productExpireDate'];
                if($db->updateProduct($productId,$productName,$productBrand,$productCategory,$productSize,$productLocation,$productPrice,$productQuantity,$productManufactureDate,$productExpireDate))
                    return returnException(false,"Product Updated",$response);
                else
                    return returnException(true,"Failed To Update Product",$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/product/add',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('productName','productBrand','productCategory','productSize','productLocation','productPrice','productQuantity','productManufactureDate','productExpireDate'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $productName = $requestParameter['productName'];
                $productBrand = $requestParameter['productBrand'];
                $productCategory = $requestParameter['productCategory'];
                $productSize = $requestParameter['productSize'];
                $productLocation = $requestParameter['productLocation'];
                $productPrice = $requestParameter['productPrice'];
                $productQuantity = $requestParameter['productQuantity'];
                $productManufactureDate = $requestParameter['productManufactureDate'];
                $productExpireDate = $requestParameter['productExpireDate'];
                if($db->addProduct($productName,$productBrand,$productCategory,$productSize,$productLocation,$productPrice,$productQuantity,$productManufactureDate,$productExpireDate))
                    return returnException(false,PRODUCT_ADDED,$response);
                else
                    return returnException(true,PRODUCT_ADDED_FAILED,$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/seller/add',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('sellerFirstName','sellerLastName','sellerContactNumber','sellerAddress'),$request,$response))
            {
                $sellerImage = null;
                $requestParameters = $request->getUploadedFiles();
                $requestParameter = $request->getParsedBody();
                if (!empty($requestParameters['sellerImage']))
                    $sellerImage = $requestParameters['sellerImage'];
                $sellerFirstName = $requestParameter['sellerFirstName'];
                $sellerLastName = $requestParameter['sellerLastName'];
                $sellerEmail = $requestParameter['sellerEmail'];
                $sellerContactNumber = $requestParameter['sellerContactNumber'];
                $sellerContactNumber1 = $requestParameter['sellerContactNumber1'];
                $sellerAddress = $requestParameter['sellerAddress'];
                if($db->addSeller($sellerFirstName,$sellerLastName,$sellerEmail,$sellerContactNumber,$sellerContactNumber1,$sellerImage,$sellerAddress))
                    return returnException(false,SELLER_INFORMATION_ADDED,$response);
                else
                    return returnException(true,SELLER_INFORMATION_ADD_FAILED,$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/sellers',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $sellers = $db->getSellers();
        if(!empty($sellers))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = SELLER_LIST_FOUND;
            $resp['sellers'] = $sellers;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,SELLER_NOT_FOUND,$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/seller/{sellerId}',function(Request $request, Response $response, array $args)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $sellerId = $args['sellerId'];
        if (!empty($sellerId)) 
        {
            if ( $db->isSellerExist($sellerId)) 
            {
                $sellers = $db->getSellerById($sellerId);
                if(!empty($sellers))
                {
                    $resp = array();
                    $resp['error'] = false;
                    $resp['message'] = "Seller Found";
                    $resp['seller'] = $sellers;
                    $response->write(json_encode($resp));
                    return $response->withHeader(CT,AJ)
                                    ->withStatus(200);
                }
                else
                    return returnException(true,SELLER_NOT_FOUND,$response);
            }
            else
                return returnException(true,SELLER_NOT_FOUND,$response);
        }
        else
            return returnException(true,"Required Parameter sellerId is missing",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/seller/{sellerId}/invoice',function(Request $request, Response $response, array $args)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $sellerId = $args['sellerId'];
        if (empty($sellerId))
            return returnException(true,"Required parameter sellerId is missing",$response);
        if (!$db->isSellerExist($sellerId))
            return returnException(true,SELLER_NOT_FOUND,$response);
        $invoices = $db->getInvoicesBySellerId($sellerId);
        if(!empty($invoices))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = INVOICE_LIST_FOUND;
            $resp['invoices'] = $invoices;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,SALES_RECORD_NOT_FOUND,$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/brand/add',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('brandName'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $brandName = $requestParameter['brandName'];
                if($db->addBrand($brandName))
                    return returnException(false,BRAND_ADDED,$response);
                else
                    return returnException(true,BRAND_ADD_FAILED,$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/payment/add',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('sellerId','invoiceNumber','paymentAmount'),$request,$response))
        {
            $requestParameter = $request->getParsedBody();
            $sellerId = $requestParameter['sellerId'];
            $invoiceNumber = $requestParameter['invoiceNumber'];
            $paymentAmount = (int) $requestParameter['paymentAmount'];
            if ($paymentAmount<=0)
                return returnException(true,PAYMENT_AMOUNT_INCREASE,$response);
            if ($db->isSellerExist($sellerId)) 
            {
                if ($db->isInvoiceExist($invoiceNumber)) 
                {
                    if ($db->isPaymentAmountLessThanInvoiceAmount($invoiceNumber,$paymentAmount))
                    {
                        if($db->addPayment($sellerId,$invoiceNumber,$paymentAmount))
                        {
                            $resp = array();
                            $resp['error'] = false;
                            $resp['message'] = 'Payment Success';
                            $response->write(json_encode($resp));
                            return $response->withHeader(CT,AJ)
                                            ->withStatus(200);
                        }
                        else
                            return returnException(true,PAYMENT_FAILED,$response);
                    }
                    else
                        return returnException(true,PAYMENT_AMOUNT_GREATER,$response);
                }
                else
                    return returnException(true,INVOICE_NOT_FOUND,$response);
            }
            else
                return returnException(true,SELLER_NOT_FOUND,$response);
        }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/credit/payment/add',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('creditId','paymentAmount'),$request,$response))
        {
            $requestParameter = $request->getParsedBody();
            $creditId = $requestParameter['creditId'];
            $paymentAmount = (int) $requestParameter['paymentAmount'];
            if ($paymentAmount<=0)
                return returnException(true,PAYMENT_AMOUNT_INCREASE,$response);
            if ($db->isCreditExist($creditId)) 
            {
                if ($db->isPaymentAmountLessThanCreditAmount($creditId,$paymentAmount))
                {
                    if($db->addCreditsPayment($creditId,$paymentAmount))
                    {
                        $resp = array();
                        $resp['error'] = false;
                        $resp['message'] = 'Payment Success';
                        $response->write(json_encode($resp));
                        return $response->withHeader(CT,AJ)
                                        ->withStatus(200);
                    }
                    else
                        return returnException(true,PAYMENT_FAILED,$response);
                }
                else
                    return returnException(true,"You Can't Accept More Than Credited Amount",$response);
            }
            else
                return returnException(true,"No Credit Found",$response);
        }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/credit/{creditId}/payments',function(Request $request, Response $response, array $args)
{
    $db = new DbHandler;
    // if (validateToken($db,$request,$response)) 
    // {
        $creditId = $args['creditId'];
        if ($db->isCreditExist($creditId))
        {
            $payment = $db->getPaymentsByCreditId($creditId);
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = PAYMENT_FOUND;
            $resp['payments'] = $payment;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        // }
        // else
        //     return returnException(true,PAYMENT_NOT_FOUND,$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/invoice/add',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('sellerId'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $sellerId = $requestParameter['sellerId'];
                if ($db->isSellerExist($sellerId)) 
                {
                    if($db->addInvoice($sellerId))
                    {
                        $invoice['invoiceNumber'] = $db->getInvoiceNumber();
                        $resp = array();
                        $resp['error'] = false;
                        $resp['message'] = INVOICE_ADDED;
                        $resp['invoice'] = $invoice;
                        $response->write(json_encode($resp));
                        return $response->withHeader(CT,AJ)
                                        ->withStatus(200);
                    }
                    else
                        return returnException(true,INVOICE_ADD_FAILED,$response);
                }
                else
                    return returnException(true,SELLER_NOT_FOUND,$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/invoices',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $invoices = $db->getInvoices();
        if(!empty($invoices))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = INVOICE_LIST_FOUND;
            $resp['invoices'] = $invoices;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,SALES_RECORD_NOT_FOUND,$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/invoice/{invoiceNumber}/pdf',function(Request $request, Response $response, array $args)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $invoiceNumber = $args['invoiceNumber'];
        if ($db->isInvoiceExist($invoiceNumber))
        {
            $invoice = $db->getInvoiceByInvoiceNumber($invoiceNumber);
            if(!empty($invoice))
            {
                $invoiceUrl = $db->getInvoiceUrlByInvoiceNumber($invoiceNumber);
                if(empty($invoiceUrl))
                {
                    $invoice = makeInvoice($db,$invoice);
                    $mpdf = new \Mpdf\Mpdf(['orientation' => 'L']);
                    $stylesheet = file_get_contents('css/b.css'); // external css
                    // $stylesheet1 = file_get_contents('css/socialcodia.css'); // external css
                    $mpdf->WriteHTML($stylesheet,1);
                    // $mpdf->WriteHTML($stylesheet1,2);
                    $mpdf->WriteHTML($invoice,2);
                    $randNumber = rand(10000000,99999999999);
                    $invoicePDF = 'uploads/invoices/'.$invoiceNumber.$randNumber.'.pdf';
                    $mpdf->Output($invoicePDF,'');
                    if($db->setInvoiceUrlByInvoiceNumber($invoicePDF,$invoiceNumber))
                    {
                        $inv['invoiceUrl'] = WEBSITE_DOMAIN.$invoicePDF;
                        $resp = array();
                        $resp['error'] = false;
                        $resp['message'] = INVOICE_FOUND_NEW;
                        $resp['invoice'] = $inv;
                        $response->write(json_encode($resp));
                        return $response->withHeader(CT,AJ)
                                        ->withStatus(200);
                    }
                }
                else
                {
                    $inv['invoiceUrl'] = WEBSITE_DOMAIN.$invoiceUrl;
                    $resp = array();
                    $resp['error'] = false;
                    $resp['message'] = INVOICE_FOUND;
                    $resp['invoice'] = $inv;
                    $response->write(json_encode($resp));
                    return $response->withHeader(CT,AJ)
                                    ->withStatus(200);
                }
            }
            else
                return returnException(true,INVOICE_NOT_FOUND,$response);
        }
        else
            return returnException(true,INVOICE_NOT_FOUND,$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/invoice/{invoiceNumber}/payments',function(Request $request, Response $response, array $args)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $invoiceNumber = $args['invoiceNumber'];
        if ($db->isInvoiceExist($invoiceNumber))
        {
            $payment = $db->getPaymentsByInvoiceNumber($invoiceNumber);
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = PAYMENT_FOUND;
            $resp['payments'] = $payment;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,PAYMENT_NOT_FOUND,$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/invoice/{invoiceNumber}',function(Request $request, Response $response, array $args)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $invoiceNumber = $args['invoiceNumber'];
        if ($db->isInvoiceExist($invoiceNumber))
        {
            $invoice = $db->getInvoiceByInvoiceNumber($invoiceNumber);
            if(!empty($invoice))
            {
                $resp = array();
                $resp['error'] = false;
                $resp['message'] = "Invoice Found";
                $resp['invoice'] = $invoice;
                $response->write(json_encode($resp));
                return $response->withHeader(CT,AJ)
                                ->withStatus(200);
            }
            else
                return returnException(true,INVOICE_NOT_FOUND,$response);
        }
        else
            return returnException(true,INVOICE_NOT_FOUND,$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/sales/today',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $sales = $db->getTodaysSalesRecord();
        if(!empty($sales))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = SALES_LIST_FOUND;
            $resp['sales'] = $sales;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,SALES_NOT_FOUND,$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/sales/all',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $sales = $db->getAllSalesRecord();
        if(!empty($sales))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = SALES_LIST_FOUND;
            $resp['sales'] = $sales;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,SALES_NOT_FOUND,$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/sales/status/months',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $sales = $db->getSalesStatusOfEveryMonth();
        if(!empty($sales))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Sales Record Found";
            $resp['status'] = $sales;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"Sales Record Not Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/sales/status/days',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $sales = $db->getSalesStatusOfEveryDay();
        if(!empty($sales))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Sales Record Found";
            $resp['status'] = $sales;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"Sales Record Not Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/sales/status/products',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $sales = $db->getTopTenMostSalesProduct();
        if(!empty($sales))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Top 10 Sellings Product Found";
            $resp['products'] = $sales;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Product Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/brands',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $brands = $db->getBrands();
        if(!empty($brands))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = BRAND_LIST_FOUND;
            $resp['brands'] = $brands;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,BRAND_NOT_FOUND,$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/sizes',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $sizes = $db->getSizes();
        if(!empty($sizes))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Size List Found";
            $resp['sizes'] = $sizes;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Size Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/categories',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $categories = $db->getCategories();
        if(!empty($categories))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Categories List Found";
            $resp['categories'] = $categories;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Categories Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/locations',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $locations = $db->getLocations();
        if(!empty($locations))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Locations List Found";
            $resp['locations'] = $locations;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Locations Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/products',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $products = $db->getProducts();
        if(!empty($products))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "products List Found";
            $resp['products'] = $products;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Products Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/product/{productId}',function(Request $request, Response $response, array $args)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $productId = $args['productId'];
        $products = $db->getProductById($productId);
        if(!empty($products))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Product Found";
            $resp['products'] = $products;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"Product Not Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/products/records',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $products = $db->getProductsRecord();
        if(!empty($products))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Products Record List Found";
            $resp['products'] = $products;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Products Record Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/counts/products/notice',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $productsNoticeCount = $db->getNoticeProductsCount();
        if(!empty($productsNoticeCount))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Products Notice Count Found";
            $resp['products'] = $productsNoticeCount;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Products Notice Count Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/counts/products/expiring',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $productsExpiringCount = $db->getExpiringProductsCount();
        if(!empty($productsExpiringCount))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Products Expiring Count Found";
            $resp['products'] = $productsExpiringCount;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Products Expiring Count Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/counts/products/expired',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $productsExpiringCount = $db->getExpiredProductsCount();
        if(!empty($productsExpiringCount))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Products Expired Count Found";
            $resp['products'] = $productsExpiringCount;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Products Expired Count Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/counts/product',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $productsCount = $db->getProductsCount();
        if(!empty($productsCount))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "products Count Found";
            $resp['products'] = $productsCount;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Products Count Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/counts/brands',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $brandsCount = $db->getBrandsCount();
        if(!empty($brandsCount))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Brands Count Found";
            $resp['brands'] = $brandsCount;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Brands Count Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/counts/sales/today',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $salesCount = $db->getTodaysSalesCount();
        if(!empty($salesCount))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Sales Count Found";
            $resp['sales'] = $salesCount;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Sales Count Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/products/array',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $products = $db->getProducts();
        if(!empty($products))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "products List Found";
            $resp['products'] = $products;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Products Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/size/add',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('sizeName'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $sizeName = $requestParameter['sizeName'];
                if($db->addSize($sizeName))
                    return returnException(false,"Size Added",$response);
                else
                    return returnException(true,"Failed To Add Size",$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/product/sell',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('productId'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $productId = $requestParameter['productId'];
                $result = $db->sellProduct($productId);
                if($result==SELL_PRODUCT)
                {
                    $products = $db->getProductById($productId);
                    $resp = array();
                    $resp['error'] = false;
                    $resp['message'] = SELL_PRODUCT;
                    $resp['product'] = $products;
                    $response->write(json_encode($resp));
                    return $response->withHeader(CT,AJ)
                                    ->withStatus(200);
                }
                else if($result==SELL_PRODUCT_FAILED)
                    return returnException(true,SELL_PRODUCT_FAILED,$response);
                else if($result==PRODUCT_QUANTITY_LOW)
                    return returnException(true,PRODUCT_QUANTITY_LOW,$response);
                else
                    return returnException(true,SWW,$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/product/sell/credit',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('salesId','creditorName','creditorAddress'),$request,$response))
        {
            $requestParameter = $request->getParsedBody();
            $salesId = $requestParameter['salesId'];
            $salesId = json_decode($salesId);
            $creditorName = $requestParameter['creditorName'];
            $creditorAddress = $requestParameter['creditorAddress'];
            $creditorMobile = null;
            $paidAmount = null;
            $creditDescription = null;
            if (isset($requestParameter['creditorMobile']) && !empty($requestParameter['creditorMobile'] ))
                $creditorMobile = $requestParameter['creditorMobile'];
            if (isset($requestParameter['paidAmount']) && !empty($requestParameter['paidAmount']))
                $paidAmount = $requestParameter['paidAmount'];
            if (isset($requestParameter['creditDescription']) && !empty($requestParameter['creditDescription']))
                $creditDescription = $requestParameter['creditDescription'];
            $result = $db->addCreditRecord($creditorName,$creditorMobile,$creditorAddress,$creditDescription,$paidAmount,$salesId);
            if($result)
                return returnException(false,'Credit Added',$response);
            else
                return returnException(true,SWW,$response);
        }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/seller/product/sell',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('productId','invoiceNumber'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $productId = $requestParameter['productId'];
                $invoiceNumber = $requestParameter['invoiceNumber'];
                $result = $db->sellProductToSeller($productId,$invoiceNumber);
                if($result==SELL_PRODUCT)
                {
                    $products = $db->getProductById($productId);
                    $resp = array();
                    $resp['error'] = false;
                    $resp['message'] = SELL_PRODUCT;
                    $resp['product'] = $products;
                    $response->write(json_encode($resp));
                    return $response->withHeader(CT,AJ)
                                    ->withStatus(200);
                }
                else if($result==SELL_PRODUCT_FAILED)
                    return returnException(true,SELL_PRODUCT_FAILED,$response);
                else if($result==PRODUCT_QUANTITY_LOW)
                    return returnException(true,PRODUCT_QUANTITY_LOW,$response);
                else
                    return returnException(true,SWW,$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/credits',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $credits = $db->getCredits();
        if(!empty($credits))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Credits Found";
            $resp['credits'] = $credits;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Credits Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/credit/{creditId}',function(Request $request, Response $response, array $args)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $creditId = $args['creditId'];
        if (!$db->isCreditExist($creditId))
            return returnException(true,"No Credit Found",$response);
        $credits = $db->getCreditById($creditId);
        if(!empty($credits))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Credits Found";
            $resp['credit'] = $credits;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Credits Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/products/notice',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $productsNotice = $db->getNoticeProducts();
        if(!empty($productsNotice))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Products Found";
            $resp['products'] = $productsNotice;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Products Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/products/expired',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $productsExpired = $db->getExpiredProducts();
        if(!empty($productsExpired))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Products Found";
            $resp['products'] = $productsExpired;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Products Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->get('/products/expiring',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        $productsExpiring = $db->getExpiringProducts();
        if(!empty($productsExpiring))
        {
            $resp = array();
            $resp['error'] = false;
            $resp['message'] = "Products Found";
            $resp['products'] = $productsExpiring;
            $response->write(json_encode($resp));
            return $response->withHeader(CT,AJ)
                            ->withStatus(200);
        }
        else
            return returnException(true,"No Products Found",$response);
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/product/sell/delete',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('sellId'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $sellId = $requestParameter['sellId'];
                $result = $db->deleteSoldProduct($sellId);
                if($result==SALE_RECORD_DELETED)
                    return returnException(false,SALE_RECORD_DELETED,$response);
                else if($result==SALE_RECORD_DELETE_FAILED)
                    return returnException(true,SALE_RECORD_DELETE_FAILED,$response);
                else if($result==SALE_NOT_EXIST)
                    return returnException(true,SALE_NOT_EXIST,$response);
                else
                    return returnException(true,SWW,$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/seller/product/sell/delete',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('sellId'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $sellId = $requestParameter['sellId'];
                $result = $db->deleteSellerSoldProduct($sellId);
                if($result==SALE_RECORD_DELETED)
                    return returnException(false,SALE_RECORD_DELETED,$response);
                else if($result==SALE_RECORD_DELETE_FAILED)
                    return returnException(true,SALE_RECORD_DELETE_FAILED,$response);
                else if($result==SALE_NOT_EXIST)
                    return returnException(true,SALE_NOT_EXIST,$response);
                else
                    return returnException(true,SWW,$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/product/sell/update',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('saleId','productQuantity','productSellPrice'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $saleId = $requestParameter['saleId'];
                $productQuantity = $requestParameter['productQuantity'];
                $productSellPrice = $requestParameter['productSellPrice'];
                $productSellDiscount = 0;
                if (isset($requestParameter['productSellDiscount']))
                    $productSellDiscount = $requestParameter['productSellDiscount'];
                $result = $db->updateSellProduct($saleId,$productQuantity,$productSellDiscount,$productSellPrice);
                if($result == SALE_UPDATED)
                    return returnException(false,SALE_UPDATED,$response);
                else if($result == SALE_UPDATE_FAILED)
                    return returnException(true,SALE_UPDATE_FAILED,$response);
                else if($result == SALE_NOT_EXIST)
                    return returnException(true,SALE_NOT_EXIST,$response);
                else if($result==PRODUCT_QUANTITY_LOW)
                    return returnException(true,PRODUCT_QUANTITY_LOW,$response);
                else
                    return returnException(true,SWW,$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/seller/product/sell/update',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('saleId','productQuantity','sellDiscount','productSellPrice'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $saleId = $requestParameter['saleId'];
                $productQuantity = $requestParameter['productQuantity'];
                $productSellPrice = $requestParameter['productSellPrice'];
                $sellDiscount = $requestParameter['sellDiscount'];
                $result = $db->updateSellerSellProducts($saleId,$productQuantity,$sellDiscount,$productSellPrice);
                if($result == SALE_UPDATED)
                    return returnException(false,SALE_UPDATED,$response);
                else if($result == SALE_UPDATE_FAILED)
                    return returnException(true,SALE_UPDATE_FAILED,$response);
                else if($result == SALE_NOT_EXIST)
                    return returnException(true,SALE_NOT_EXIST,$response);
                else if($result==PRODUCT_QUANTITY_LOW)
                    return returnException(true,PRODUCT_QUANTITY_LOW,$response);
                else
                    return returnException(true,SWW,$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/category/add',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('categoryName'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $categoryName = $requestParameter['categoryName'];
                if($db->addCategory($categoryName))
                    return returnException(false,"Category Added",$response);
                else
                    return returnException(true,"Failed To Add Category",$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

$app->post('/location/add',function(Request $request, Response $response)
{
    $db = new DbHandler;
    if (validateToken($db,$request,$response)) 
    {
        if(!checkEmptyParameter(array('locationName'),$request,$response))
            {
                $requestParameter = $request->getParsedBody();
                $locationName = $requestParameter['locationName'];
                if($db->addLocation($locationName))
                    return returnException(false,"Location Added",$response);
                else
                    return returnException(true,"Failed To Add Location",$response);
            }
    }
    else
        return returnException(true,UNAUTH_ACCESS,$response);
});

function checkEmptyParameter($requiredParameter,$request,$response)
{
    $result = array();
    $error = false;
    $errorParam = '';
    $requestParameter = $request->getParsedBody();
    foreach($requiredParameter as $param)
    {
        if(!isset($requestParameter[$param]) || strlen($requestParameter[$param])<1)
        {
            $error = true;
            $errorParam .= $param.', ';
        }
    }
    if($error)
        return returnException(true,"Required Parameter ".substr($errorParam,0,-2)." is missing",$response);
    return $error;
}

function makeInvoice($db,$invoiceInfo)
{
    require_once '../include/Constants.php';
    // require_once __DIR__ . '../include/Constants.php';
    $fullInvoiceHTMl = null;
    $count = 0;
    $companyName = COMPANY_NAME;
    $companyEmail = COMPANY_EMAIL;
    $companyNumber = COMPANY_CONTACT_NUMBER;
    $companyAddress = COMPANY_ADDRESS;
    $invoiceNumber = $invoiceInfo['invoiceNumber'];
    $invoiceDate = $invoiceInfo['invoiceDate'];
    $invoiceAmount = $invoiceInfo['invoiceAmount'];
    $sellerName = $invoiceInfo['sellerName'];
    $sellerImage = $invoiceInfo['sellerImage'];
    $sellerNumber = $invoiceInfo['sellerContactNumber'];
    $sellerAddress = $invoiceInfo['sellerAddress'];
    $priceAllTotalAmount = $invoiceInfo['invoiceTotalPrice'];
    $priceAllDiscountAmount = $invoiceInfo['invoiceAmount'];
    $invoiceHeader = getInvoiceHeader($sellerImage,$companyName,$companyNumber,$companyEmail,$companyAddress,$sellerName,$sellerNumber,$sellerAddress,$invoiceNumber);
    $fullInvoiceHTMl .= $invoiceHeader;
    $products = $db->getSellerSellProductsByInvoiceNumber($invoiceInfo['invoiceNumber']);
    
    foreach ($products as $product)
    {
        $productName = $product['productName'];
        $productSize = $product['productSize'];
        $productPrice = $product['productPrice'];
        $sellQuantity = $product['sellQuantity'];
        $productTotalPrice = $product['productPrice']*$product['sellQuantity'];
        $sellDiscount = $product['sellDiscount'].'%';
        $sellPrice = $product['sellPrice'];
        $count++;

        $invoiceBody = <<<HERE
        <tr>
        <td class="col-md-1">$count</td>
        <td class="col-md-9  col-xs-3">$productName</td>
        <td class="col-md-1"><i class="fa fa-inr"></i>$productSize</td>
        <td class="col-md-1"><i class="fa fa-inr"></i>$productPrice</td>
        <td class="col-md-1 "><i class="fa fa-inr"></i>$sellQuantity</td>
        <td class="col-md-1"><i class="fa fa-inr"></i>$productTotalPrice</td>
        <td class="col-md-1"><i class="fa fa-inr"></i>$sellDiscount</td>
        <td class="col-md-1"><i class="fa fa-inr"></i>$sellPrice</td>
    </tr>
    HERE;
    $fullInvoiceHTMl .=$invoiceBody;
    }

    $invoiceFooter = getInvoiceFooter($priceAllTotalAmount,$priceAllDiscountAmount,$invoiceDate);
    $fullInvoiceHTMl .=$invoiceFooter;
    return $fullInvoiceHTMl;
}

function getInvoiceHeader($sellerImage,$companyName,$companyNumber,$companyEmail,$companyAddress,$sellerName,$sellerNumber,$sellerAddress,$invoiceNumber)
{
    $invoiceHeader = <<<HERE
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="../../../css/b.css" rel="stylesheet">
        </head>
        <body>
        <div class="col-md-12">   
        <div class="row">
        <div class="receipt-main">
        <div class="row">
        <div class="receipt-header">
        <div class="col-xs-7 col-sm-7 col-md-7">
        <div class="receipt-left">
        <img class="img-responsive" alt="sellerimage" src="$sellerImage" style="width: 71px; border-radius: 43px; border-radius:50%">
        </div>
        </div>
        <div class="col-xs-4 col-sm-4 col-md-4 text-right">
        <div class="receipt-right">
        <h5>$companyName</h5>
        <p>$companyNumber<i class="fa fa-phone"></i></p>
        <p>$companyEmail<i class="fa fa-envelope-o"></i></p>
        <p>$companyAddress<i class="fa fa-location-arrow"></i></p>
        </div>
        </div>
        </div>
        </div>
        <div class="row">
        <div class="receipt-header receipt-header-mid">
        <div class="col-xs-8 col-sm-8 col-md-8 text-left">
        <div class="receipt-right">
        <h5>$sellerName</h5>
        <p><b>Mobile :</b> +91 $sellerNumber</p>
        <p><b>Address :</b> $sellerAddress</p>
        </div>
        </div>
        <div class="col-xs-3 col-sm-3 col-md-3">
        <div class="receipt-left">
        <h3>INVOICE # $invoiceNumber</h3>
        </div>
        </div>
        </div>
        </div>
        <div>
        <table class="table table-bordered">
        <thead>
        <tr>
        <th>Sr. No</th>
        <th>Products</th>
        <th>Size</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Total</th>
        <th>Discount</th>
        <th>Amount</th>
        </tr>
        </thead>
        <tbody>
        HERE;
return $invoiceHeader;
}

function getInvoiceFooter($priceAllTotalAmount,$priceAllDiscountAmount,$invoiceDate)
{
    $invoiceHeader = <<<HERE
                  <tr>
                  <td></td>
                  <td class="text-right">
                  <h2>
                  <strong>Total: </strong>
                  </h2>
                  </td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td class="text-left text-danger">
                  <h2>
                  <strong><i class="fa fa-inr"></i> $priceAllTotalAmount</strong>
                  </h2>
                  </td>
                  <td></td>
                  <td class="text-left text-danger">
                  <h2>
                  <strong><i class="fa fa-inr"></i> $priceAllDiscountAmount</strong>
                  </h2>
                  </td>
                  </tr>
                  </tbody>
                  </table>
                  </div>
                  <div class="row">
                  <div class="receipt-header receipt-header-mid receipt-footer">
                  <div class="col-xs-8 col-sm-8 col-md-8 text-left">
                  <div class="receipt-right">
                  <p><b>Date :</b> $invoiceDate</p>
                  <h5 style="color: rgb(140, 140, 140);">Thanks for shopping.!</h5>
                  </div>
                  </div>
                  <div class="col-xs-2 col-sm-2 col-md-2">
                  <div class="receipt-left">
                  <h1>Signature</h1>
                  </div>
                  </div>
                  </div>
                  </div>
                  </div>    
                  </div>
                  </div>
                  </script>
                  </body>
                  </html>
        HERE;
        return $invoiceHeader;
}

/*
just parepare a name, email, mail subject and email id to send the mail,
we are not using any mail service in our whole project, you can want to use it
simply pass al these four parameter to send the mail.

The email configuration which you have setup to send the email, open constant.php and add change information

Thanks */

function sendMail($name,$email,$mailSubject,$mailBody)
{
    $websiteEmail = WEBSITE_EMAIL;
    $websiteEmailPassword = WEBSITE_EMAIL_PASSWORD;
    $websiteName = WEBSITE_NAME;
    $websiteOwnerName = WEBSITE_OWNER_NAME;
    $mail = new PHPMailer;
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host=SMTP_HOST;
    $mail->Port=SMTP_PORT;
    $mail->SMPTSecure=SMTP_SECURE;
    $mail->SMTPAuth=true;
    $mail->Username = $websiteEmail;
    $mail->Password = $websiteEmailPassword;
    $mail->addAddress($email,$name);
    $mail->isHTML();
    $mail->Subject=$mailSubject;
    $mail->Body=$mailBody;
    $mail->From=$websiteEmail;
    $mail->FromName=$websiteName;
    if($mail->send())
    {
        return true;
    }
    return false;
}

function encrypt($data)
{
    $email = openssl_encrypt($data,"AES-128-ECB",null);
    $email = str_replace('/','socialcodia',$email);
    $email = str_replace('+','mufazmi',$email);
    return $email; 
}

function decrypt($data)
{
    $mufazmi = str_replace('mufazmi','+',$data);
    $email = str_replace('socialcodia','/',$mufazmi);
    $email = openssl_decrypt($email,"AES-128-ECB",null);
    return $email; 
}

function returnException($error,$message,$response)
{
    $errorDetails = array();
    $errorDetails['error'] = $error;
    $errorDetails['message'] = $message;
    $response->write(json_encode($errorDetails));
    return $response->withHeader('Content-type','Application/json')
                    ->withStatus(200);
}

function returnResponse($error,$message,$response,$data)
{
    $responseDetails = array();
    $responseDetails[ERROR] = $error;
    $responseDetails[MESSAGE] = $message;
    $responseDetails[MESSAGE] = $data;
    $response->write(json_encode($responseDetails));
    return $response->withHeader(CT,AJ)
                    ->withStatus(200);
}

function getToken($userId)
{
    $key = JWT_SECRET_KEY;
    $payload = array(
        "iss" => "socialcodia.com",
        "iat" => time(),
        "user_id" => $userId
    );
    $token =JWT::encode($payload,$key);
    return $token;
}

function validateToken($db,$request,$response)
{
    $error = false;
    $header =$request->getHeaders();
    if (!empty($header['HTTP_TOKEN'][0])) 
    {
        $token = $header['HTTP_TOKEN'][0];
        $result = $db->validateToken($token);
        if (!$result == JWT_TOKEN_FINE)
            $error = true;
        else if($result == JWT_TOKEN_ERROR || $result==JWT_USER_NOT_FOUND)
        {
            $error = true;
        }
    }

    else
    {
        $error = true;
    }
    if ($error)
        return false;
    else
        return true;
}


$app->run();