<?php

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Factory\Consignment\ConsignmentFactory;
use Gett\MyparcelBE\Label\LabelOptionsResolver;
use Gett\MyparcelBE\Logger\Logger;
use Gett\MyparcelBE\OrderLabel;
use Gett\MyparcelBE\Service\Consignment\Download;
use Gett\MyparcelBE\Service\MyparcelStatusProvider;
use MyParcelNL\Sdk\src\Factory\ConsignmentFactory as ConsignmentFactorySdk;
use MyParcelNL\Sdk\src\Factory\DeliveryOptionsAdapterFactory;
use MyParcelNL\Sdk\src\Helper\MyParcelCollection;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\DPDConsignment;

if (file_exists(_PS_MODULE_DIR_ . 'myparcelbe/vendor/autoload.php')) {
    require_once _PS_MODULE_DIR_ . 'myparcelbe/vendor/autoload.php';
}

class AdminLabelController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();
        switch (Tools::getValue('action')) {
            case 'return':
                $this->processReturn();

                break;
        }

        die();
        //Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders'));
    }

    public function processReturn()
    {
        $order = new Order(Tools::getValue('id_order'));
        if (Validate::isLoadedObject($order)) {
            $address = new Address($order->id_address_delivery);
            $customer = new Customer($order->id_customer);

            try {
                $consignment = (ConsignmentFactorySdk::createByCarrierId(PostNLConsignment::CARRIER_ID))
                    ->setApiKey(Configuration::get(Constant::API_KEY_CONFIGURATION_NAME))
                    ->setReferenceId($order->id)
                    ->setCountry(CountryCore::getIsoById($address->id_country))
                    ->setPerson($address->firstname . ' ' . $address->lastname)
                    ->setFullStreet($address->address1)
                    ->setPostalCode($address->postcode)
                    ->setCity($address->city)
                    ->setEmail($customer->email)
                    ->setContents(1)
                ;

                $myParcelCollection = (new MyParcelCollection())
                    ->addConsignment($consignment)
                    ->setPdfOfLabels()->sendReturnLabelMails();
                Logger::addLog($myParcelCollection->toJson());
            } catch (Exception $e) {
                Logger::addLog($e->getMessage(), true);
                header('HTTP/1.1 500 Internal Server Error', true, 500);
                die($this->module->l('A error occurred in the MyParcel module, please try again.', 'adminlabelcontroller'));
            }

            $consignment = $myParcelCollection->first();
            $orderLabel = new OrderLabel();
            $orderLabel->id_label = $consignment->getConsignmentId();
            $orderLabel->id_order = $consignment->getReferenceId();
            $orderLabel->barcode = $consignment->getBarcode();
            $orderLabel->track_link = $consignment->getBarcodeUrl(
                $consignment->getBarcode(),
                $consignment->getPostalCode(),
                $consignment->getCountry()
            );
            $status_provider = new MyparcelStatusProvider();
            $orderLabel->new_order_state = $consignment->getStatus();
            $orderLabel->status = $status_provider->getStatus($consignment->getStatus());
            $orderLabel->add();
        }
    }

    public function ajaxProcessCreate()
    {
        $factory = new ConsignmentFactory(
            \Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
            Tools::getAllValues(),
            new Configuration(),
            $this->module
        );
        $createLabelIds = Tools::getValue('create_label');
        if (empty($createLabelIds['order_ids'])) {
            die($this->module->l('No order ID found', 'adminlabelcontroller'));
        }
        $orders = OrderLabel::getDataForLabelsCreate($createLabelIds['order_ids']);
        if (empty($orders)) {
            $this->errors[] = $this->module->l('No order found.', 'adminlabelcontroller');
            die(json_encode(['hasError' => true, 'errors' => $this->errors]));
        }
        $order = reset($orders);

        try {
            $collection = $factory->fromOrder($order);
            $consignments = $collection->getConsignments();
            if (!empty($consignments)) {
                foreach ($consignments as &$consignment) {
                    $consignment->delivery_date = $this->fixPastDeliveryDate($consignment->delivery_date);
                    $this->fixSignature($consignment);
                    $this->sanitizeDeliveryType($consignment);
                    $this->sanitizePackageType($consignment);
                }
            }
            Logger::addLog($collection->toJson());
            $collection->setLinkOfLabels();
            if ($this->module->isNL()
                && Tools::getValue(Constant::RETURN_PACKAGE_CONFIGURATION_NAME)) {
                $collection->sendReturnLabelMails();
            }
        } catch (Exception $e) {
            Logger::addLog($e->getMessage(), true);
            Logger::addLog($e->getFile(), true);
            Logger::addLog($e->getLine(), true);
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($this->module->l('A error occurred in the MyParcel module, please try again.', 'adminlabelcontroller'));
        }

        $status_provider = new MyparcelStatusProvider();
        foreach ($collection as $consignment) {
            $orderLabel = new OrderLabel();
            $orderLabel->id_label = $consignment->getConsignmentId();
            $orderLabel->id_order = $consignment->getReferenceId();
            $orderLabel->barcode = $consignment->getBarcode();
            $orderLabel->track_link = $consignment->getBarcodeUrl(
                $consignment->getBarcode(),
                $consignment->getPostalCode(),
                $consignment->getCountry()
            );
            $orderLabel->new_order_state = $consignment->getStatus();
            $orderLabel->status = $status_provider->getStatus($consignment->getStatus());
            $orderLabel->add();
            //$paymentUrl = $myParcelCollection->setPdfOfLabels()->getLabelPdf()['data']['payment_instructions']['0']['payment_url'];
        }

        die(json_encode(['hasError' => false]));
    }

    public function processCreateb()
    {
        $postValues = Tools::getAllValues();
        $printPosition = false;
        if (!empty($postValues['format']) && $postValues['format'] == 'a4') {
            $printPosition = $postValues['position'];
        }
        $factory = new ConsignmentFactory(
            \Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
            $postValues,
            new Configuration(),
            $this->module
        );
        $orderIds = Tools::getValue('order_ids');
        if (empty($orderIds)) {
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($this->module->l('Can\'t create label for these orders', 'adminlabelcontroller'));
        }
        $orders = OrderLabel::getDataForLabelsCreate($orderIds);
        if (empty($orders)) {
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($this->module->l('Can\'t create label for these orders.', 'adminlabelcontroller'));
        }
        try {
            $collection = $factory->fromOrders($orders);
            $label_options_resolver = new LabelOptionsResolver();
            foreach ($orderIds as $orderId) {
                $orderLabelParams = [
                    'id_order' => (int) $orderId,
                    'id_carrier' => 0
                ];
                foreach ($orders as $orderRow) {
                    if ((int) $orderRow['id_order'] === (int) $orderId) {
                        $orderLabelParams['id_carrier'] = (int) $orderRow['id_carrier'];
                        break;
                    }
                }
                $labelOptions = $label_options_resolver->getLabelOptions($orderLabelParams);
                $options = json_decode($labelOptions);
                $consignment = $collection->getConsignmentsByReferenceId($orderId)->getOneConsignment();
                if ($options->package_type && count($consignment->getItems()) == 0) {
                    $consignment->setPackageType($options->package_type);
                } else {
                    $consignment->setPackageType(1);
                }
                if ($options->only_to_recepient == 1 && $consignment instanceof PostNLConsignment) {
                    $consignment->setOnlyRecipient(true);
                } else {
                    $consignment->setOnlyRecipient(false);
                }
                if ($options->age_check == 1 && count($consignment->getItems()) == 0) {
                    $consignment->setAgeCheck(true);
                } else {
                    $consignment->setAgeCheck(false);
                }
                if ($options->signature == 1 && !($consignment instanceof DPDConsignment)) {
                    $consignment->setSignature(true);
                } else {
                    $consignment->setSignature(false);
                }
                if ($options->insurance) {
                    $consignment->setInsurance(2500);
                }
                $consignment->delivery_date = $this->fixPastDeliveryDate($consignment->delivery_date);
                $this->fixSignature($consignment);
                $this->sanitizeDeliveryType($consignment);
                $this->sanitizePackageType($consignment);
            }
            $collection->setPdfOfLabels($printPosition);
            Logger::addLog($collection->toJson());
        } catch (Exception $e) {
            Logger::addLog($e->getMessage(), true);
            Logger::addLog($e->getFile(), true);
            Logger::addLog($e->getLine(), true);
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($this->module->l('A error occurred in the MyParcel module, please try again.', 'adminlabelcontroller'));
        }

        $status_provider = new MyparcelStatusProvider();
        foreach ($collection as $consignment) {
            $orderLabel = new OrderLabel();
            $orderLabel->id_label = $consignment->getConsignmentId();
            $orderLabel->id_order = $consignment->getReferenceId();
            $orderLabel->barcode = $consignment->getBarcode();
            $orderLabel->track_link = $consignment->getBarcodeUrl(
                $consignment->getBarcode(),
                $consignment->getPostalCode(),
                $consignment->getCountry()
            );
            $orderLabel->new_order_state = $consignment->getStatus();
            $orderLabel->status = $status_provider->getStatus($consignment->getStatus());
            $orderLabel->add();
            //$paymentUrl = $myParcelCollection->setPdfOfLabels()->getLabelPdf()['data']['payment_instructions']['0']['payment_url'];
        }

        return $collection;
    }

    public function processRefresh()
    {

        $id_labels = OrderLabel::getOrdersLabels(Tools::getValue('order_ids'));
        if (empty($id_labels)) {
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($this->module->l('No created labels found', 'adminlabelcontroller'));
        }
        try {
            $collection = MyParcelCollection::findMany($id_labels, \Configuration::get(Constant::API_KEY_CONFIGURATION_NAME));

            $collection->setLinkOfLabels();
            Logger::addLog($collection->toJson());
        } catch (Exception $e) {
            Logger::addLog($e->getMessage(), true);
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($this->module->l('A error occurred in the MyParcel module, please try again.', 'adminlabelcontroller'));
        }

        $status_provider = new MyparcelStatusProvider();

        foreach ($collection as $consignment) {
            $order_label = OrderLabel::findByLabelId($consignment->getConsignmentId());
            $order_label->status = $status_provider->getStatus($consignment->getStatus());
            $order_label->save();
        }

        die();
    }

    public function processPrint()
    {
        $labels = OrderLabel::getOrdersLabels(Tools::getValue('order_ids'));
        if (empty($labels)) {
            if (Tools::getIsset('id_order')) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders', true, [], [
                    'id_order' => (int) Tools::getValue('id_order'),
                    'vieworder' => '',
                ]));
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders'));
        }
        $service = new Download(
            \Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
            Tools::getAllValues(),
            new Configuration()
        );
        $service->downloadLabel($labels);
    }

    public function processUpdateLabel()
    {
        try {
            $collection = MyParcelCollection::find(Tools::getValue('labelId'), \Configuration::get(Constant::API_KEY_CONFIGURATION_NAME));
            $collection->setLinkOfLabels();
            Logger::addLog($collection->toJson());
        } catch (Exception $e) {
            Logger::addLog($e->getMessage(), true);
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders'));
        }

        $status_provider = new MyparcelStatusProvider();

        if (!empty($collection)) {
            foreach ($collection as $consignment) {
                $order_label = OrderLabel::findByLabelId($consignment->getConsignmentId());
                $order_label->status = $status_provider->getStatus($consignment->getStatus());
                $order_label->save();
            }
        }

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders'));
    }

    public function fixPastDeliveryDate(?string $deliveryDate): ?string
    {
        if (!$deliveryDate) {
            return $deliveryDate;
        }
        $tomorrow = new DateTime('tomorrow');
        try {
            $deliveryDateObj = new DateTime($deliveryDate);
        } catch (Exception $e) {
            return $tomorrow->format('Y-m-d H:i:s');
        }
        $oldDate = clone $deliveryDateObj;
        $tomorrow->setTime(0, 0, 0, 0);
        $oldDate->setTime(0, 0, 0, 0);
        if ($tomorrow > $oldDate) {
            do {
                $deliveryDateObj->add(new DateInterval('P1D'));
            } while ($tomorrow > $deliveryDateObj || $deliveryDateObj->format('w') == 0);
            $deliveryDate = $deliveryDateObj->format('Y-m-d H:i:s');
        }

        return $deliveryDate;
    }

    public function processDownloadLabel()
    {
        $service = new Download(
            Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
            Tools::getAllValues(),
            new Configuration()
        );

        $service->downloadLabel([Tools::getValue('label_id')]);
    }

    public function fixSignature(AbstractConsignment $consignment)
    {
        if ($consignment->getCountry() === 'NL' && $this->module->isBE()) {
            $consignment->signature = 0;
        }
        if ($consignment->getCountry() === 'BE' && $this->module->isNL()) {
            $consignment->signature = 0;
        }
    }

    public function sanitizeDeliveryType(AbstractConsignment $consignment)
    {
        if ($this->module->isBE()) {
            if ($consignment->delivery_type < 4) {
                $consignment->delivery_type = 2;
            }
            if ($consignment->delivery_type > 4) {
                $consignment->delivery_type = 4;
            }
        }
    }

    public function sanitizePackageType(AbstractConsignment $consignment)
    {
        if ($this->module->isBE()) {
            if ($consignment->package_type !== 1) {
                $consignment->package_type = 1;
            }
        }
    }

    public function processExportPrint()
    {
        $collection = $this->processCreateb();
        setcookie('downloadPdfLabel', 1);
        $collection->downloadPdfOfLabels(Configuration::get(
            Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME,
            false
        ));
    }

    public function ajaxProcessSaveConcept()
    {
        $postValues = Tools::getAllValues();
        if (!isset($postValues['id_order'])) {
            $this->errors[] = $this->module->l('Order not found by ID', 'adminlabelcontroller');
        }
        if (!empty($this->errors)) {
            $this->returnAjaxResponse();
        }
        $currency = Currency::getDefaultCurrency();
        $deliveryOptions = OrderLabel::getOrderDeliveryOptions((int) $postValues['id_order']);
        try {
            $order = new Order((int) $postValues['id_order']);
            $deliveryOptions->date = $postValues['deliveryDate'] . 'T00:00:00.000Z';
            $deliveryOptions->shipmentOptions = new StdClass(); // Reset shipment options
            foreach (Constant::SINGLE_LABEL_CREATION_OPTIONS as $key => $name) {
                if (isset($postValues[$key])) {
                    switch ($key) {
                        case 'packageType':
                            $deliveryOptions->shipmentOptions->package_type = $postValues[$key];
                            break;
                        case 'packageFormat':
                            if (Constant::PACKAGE_FORMATS[$postValues[$key]] == 'large') {
                                $deliveryOptions->shipmentOptions->large_format = true;
                            }
                            break;
                        case 'onlyRecipient':
                            $deliveryOptions->shipmentOptions->only_recipient = true;
                            break;
                        case 'ageCheck':
                            $deliveryOptions->shipmentOptions->age_check = true;
                            break;
                        case 'returnUndelivered':
                            $deliveryOptions->shipmentOptions->return = true;
                            break;
                        case 'signatureRequired':
                            $deliveryOptions->shipmentOptions->signature = true;
                            break;
                        case 'insurance':
                            $deliveryOptions->shipmentOptions->insurance = new StdClass();
                            if (isset($postValues['insuranceAmount'])) {
                                if (strpos($postValues['insuranceAmount'], 'amount') !== false) {
                                    $insuranceValue = (int) str_replace(
                                        'amount',
                                        '',
                                        $postValues['insuranceAmount']
                                    );
                                } else {
                                    $insuranceValue = (int) $postValues['insurance-amount-custom-value'] ?? 0;
                                }
                                $deliveryOptions->shipmentOptions->insurance->amount = $insuranceValue * 100;// cents
                                $deliveryOptions->shipmentOptions->insurance->currency = $currency->iso_code;
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
            Db::getInstance(_PS_USE_SQL_SLAVE_)->update(
                'myparcel_delivery_settings',
                ['delivery_settings' => json_encode($deliveryOptions)],
                'id_cart = ' . (int) $order->id_cart
            );
        } catch (Exception $e) {
            $this->errors[] = $this->module->l('Error loading the delivery options.', 'adminlabelcontroller');
            $this->errors[] = $e->getMessage();
        }

        $this->returnAjaxResponse();
    }

    public function returnAjaxResponse($response = [])
    {
        $results = ['hasError' => false];
        if (!empty($this->errors)) {
            $results['hasError'] = true;
            $results['errors'] = $this->errors;
        }

        $results = array_merge($results, $response);

        die(json_encode($results));
    }

    public function ajaxProcessCreateLabel()
    {
        $postValues = Tools::getAllValues();
        $factory = new ConsignmentFactory(
            \Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
            $postValues,
            new Configuration(),
            $this->module
        );
        $idOrder = $postValues['id_order'] ?? 0;
        if (!$idOrder) {
            $this->errors[] = $this->module->l('No order ID found.', 'adminlabelcontroller');
            $this->returnAjaxResponse();
        }
        $orders = OrderLabel::getDataForLabelsCreate([(int) $idOrder]);
        if (empty($orders)) {
            $this->errors[] = $this->module->l('No order found.', 'adminlabelcontroller');
            $this->returnAjaxResponse();
        }
        $order = reset($orders);

        try {
            $collection = $factory->fromOrder($order);
            $consignments = $collection->getConsignments();
            if (!empty($consignments)) {
                foreach ($consignments as &$consignment) {
                    $consignment->delivery_date = $this->fixPastDeliveryDate($consignment->delivery_date);
                    $this->fixSignature($consignment);
                    $this->sanitizeDeliveryType($consignment);
                    $this->sanitizePackageType($consignment);
                }
            }
            Logger::addLog($collection->toJson());
            $collection->setLinkOfLabels();
            if ($this->module->isNL()
                && $postValues[Constant::RETURN_PACKAGE_CONFIGURATION_NAME]) {
                $collection->sendReturnLabelMails();
            }
        } catch (Exception $e) {
            Logger::addLog($e->getMessage(), true);
            Logger::addLog($e->getFile(), true);
            Logger::addLog($e->getLine(), true);
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            $this->errors[] = $this->module->l(
                'An error occurred in MyParcel module, please try again.',
                'adminlabelcontroller'
            );
            $this->returnAjaxResponse();
        }

        $labelIds = [];
        $status_provider = new MyparcelStatusProvider();
        foreach ($collection as $consignment) {
            $orderLabel = new OrderLabel();
            $orderLabel->id_label = $consignment->getConsignmentId();
            $orderLabel->id_order = $consignment->getReferenceId();
            $orderLabel->barcode = $consignment->getBarcode();
            $orderLabel->track_link = $consignment->getBarcodeUrl(
                $consignment->getBarcode(),
                $consignment->getPostalCode(),
                $consignment->getCountry()
            );
            $orderLabel->new_order_state = $consignment->getStatus();
            $orderLabel->status = $status_provider->getStatus($consignment->getStatus());
            $orderLabel->add();
            if (!empty($orderLabel->id)) {
                $labelIds[] = $orderLabel->id_label;
            }
        }

        $labelList = OrderLabel::getOrderLabels((int) $idOrder, []);
        $labelListHtml = $this->context->smarty->createData(
            $this->context->smarty
        );
        $labelListHtml->assign([
            'labelList' => $labelList,
        ]);

        $labelListHtmlTpl = $this->context->smarty->createTemplate(
            $this->module->getTemplatePath('views/templates/admin/hook/label-list.tpl'),
            $labelListHtml
        );

        $this->returnAjaxResponse(['labelIds' => $labelIds, 'labelsHtml' => $labelListHtmlTpl->fetch()]);
    }

    public function processPrintOrderLabel()
    {
        $labels = OrderLabel::getOrderLabels(Tools::getValue('id_order'), Tools::getValue('label_id'));
        if (empty($labels)) {
            Tools::redirectAdmin($this->context->link->getAdminLink(
                'AdminOrders',
                true,
                [],
                [
                    'vieworder' => '',
                    'id_order' => (int) Tools::getValue('id_order'),
                ]
            ));
        }
        $labelIds = [];
        foreach ($labels as $label) {
            $labelIds[] = (int) $label['id_label'];
        }
        $service = new Download(
            \Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
            Tools::getAllValues(),
            new Configuration()
        );
        $service->downloadLabel($labelIds);
    }

    public function ajaxProcessDeleteLabel()
    {
        $postValues = Tools::getAllValues();
        $result = true;
        if (!empty($postValues['id_order_label'])) {
            $orderLabel = new OrderLabel((int) $postValues['id_order_label']);
            $result &= $orderLabel->delete();
        }
        if (!$result) {
            $this->errors[] = $this->module->l(
                'Error deleting the label.',
                'adminlabelcontroller'
            );
        }

        $this->returnAjaxResponse();
    }

    public function ajaxProcessUpdateDeliveryOptions()
    {
        $postValues = Tools::getAllValues();
        $options = $postValues['myparcel-delivery-options'] ?? null;
        $action = $postValues['action'] ?? null;
        $order = new Order($postValues['id_order'] ?? 0);
        if ($action === 'updateDeliveryOptions' && !empty($options) && !empty($order->id_cart)) {
            Db::getInstance(_PS_USE_SQL_SLAVE_)->insert(
                'myparcel_delivery_settings',
                ['id_cart' => $order->id_cart, 'delivery_settings' => $options],
                false,
                true,
                Db::REPLACE
            );
        } else {
            $this->errors[] = $this->module->l(
                'Error updating the delivery options.',
                'adminlabelcontroller'
            );
        }

        $this->returnAjaxResponse();
    }
}
