<?php

namespace Gett\MyparcelBE;

class Constant
{
    const POSTNL_DEFAULT_CARRIER = 'MYPARCEL_DEFAULT_CARRIER';

    const MENU_API_SETTINGS = 0;
    const MENU_GENERAL_SETTINGS = 1;
    const MENU_LABEL_SETTINGS = 2;
    const MENU_ORDER_SETTINGS = 3;
    const MENU_CUSTOMS_SETTINGS = 4;
    const MENU_CARRIER_SETTINGS = 5;

    const API_KEY_CONFIGURATION_NAME = 'MY_PARCEL_API_KEY';
    const API_LOGGING_CONFIGURATION_NAME = 'MY_PARCEL_API_LOGGING';

    const PACKAGE_TYPE_CONFIGURATION_NAME = 'MY_PARCEL_PACKAGE_TYPE';
    const ONLY_RECIPIENT_CONFIGURATION_NAME = 'MY_PARCEL_RECIPIENT_ONLY';
    const AGE_CHECK_CONFIGURATION_NAME = 'MY_PARCEL_AGE_CHECK';
    const PACKAGE_FORMAT_CONFIGURATION_NAME = 'MY_PARCEL_PACKAGE_FORMAT';

    const RETURN_PACKAGE_CONFIGURATION_NAME = 'MY_PARCEL_RETURN_PACKAGE';
    const SIGNATURE_REQUIRED_CONFIGURATION_NAME = 'MY_PARCEL_SIGNATURE_REQUIRED';
    const INSURANCE_CONFIGURATION_NAME = 'MY_PARCEL_INSURANCE';
    const CUSTOMS_FORM_CONFIGURATION_NAME = 'MY_PARCEL_CUSTOMS_FORM';
    const CUSTOMS_CODE_CONFIGURATION_NAME = 'MY_PARCEL_CUSTOMS_CODE';
    const DEFAULT_CUSTOMS_CODE_CONFIGURATION_NAME = 'MY_PARCEL_DEFAULT_CUSTOMS_CODE';
    const CUSTOMS_ORIGIN_CONFIGURATION_NAME = 'MY_PARCEL_CUSTOMS_ORIGIN';
    const DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME = 'MY_PARCEL_DEFAULT_CUSTOMS_ORIGIN';
    const CUSTOMS_AGE_CHECK_CONFIGURATION_NAME = 'MY_PARCEL_CUSTOMS_AGE_CHECK';

    const SINGLE_LABEL_CREATION_OPTIONS = [
        self::PACKAGE_TYPE_CONFIGURATION_NAME,
        self::PACKAGE_FORMAT_CONFIGURATION_NAME,
        self::ONLY_RECIPIENT_CONFIGURATION_NAME,
        self::AGE_CHECK_CONFIGURATION_NAME,
        self::RETURN_PACKAGE_CONFIGURATION_NAME,
        self::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
        self::INSURANCE_CONFIGURATION_NAME,
    ];

    const SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME = 'MY_PARCEL_SHARE_CUSTOMER_EMAIL';
    const SHARE_CUSTOMER_PHONE_CONFIGURATION_NAME = 'MY_PARCEL_SHARE_CUSTOMER_PHONE';

    const LABEL_DESCRIPTION_CONFIGURATION_NAME = 'MY_PARCEL_LABEL_DESCRIPTION';
    const LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME = 'MY_PARCEL_LABEL_OPEN_DOWNLOAD';
    const LABEL_SIZE_CONFIGURATION_NAME = 'MY_PARCEL_LABEL_SIZE';
    const LABEL_POSITION_CONFIGURATION_NAME = 'MY_PARCEL_LABEL_POSITION';
    const LABEL_PROMPT_POSITION_CONFIGURATION_NAME = 'MY_PARCEL_LABEL_PROMPT_POSITION';

    const LABEL_CREATED_ORDER_STATUS_CONFIGURATION_NAME = 'MY_PARCEL_LABEL_CREATED_ORDER_STATUS';

    const CARRIER_CONFIGURATION_FIELDS = [
        'deliveryTitle',
        'dropOffDays',
        'cutoffTime',
        'deliverydaysWindow',
        'dropoffDelay',
        'allowMondayDelivery',
        'saturdayCutoffTime',
        'allowMorningDelivery',
        'deliveryMorningTitle',
        'priceMorningDelivery',
        'deliveryStandardTitle',
        'priceStandardDelivery',
        'allowEveningDelivery',
        'deliveryEveningTitle',
        'priceEveningDelivery',
        'allowSignature',
        'signatureTitle',
        'priceSignature',
        'allowOnlyRecipient',
        'onlyRecipientTitle',
        'priceOnlyRecipient',
        'allowPickupPoints',
        'pickupTitle',
        'pricePickup',
        'allowPickupExpress',
        'pricePickupExpress',
        'BEdeliveryTitle',
        'MY_PARCEL_PACKAGE_TYPE',
        'MY_PARCEL_PACKAGE_FORMAT',
        self::AGE_CHECK_CONFIGURATION_NAME,
        self::RETURN_PACKAGE_CONFIGURATION_NAME,
        self::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
        self::INSURANCE_CONFIGURATION_NAME,
        self::ONLY_RECIPIENT_CONFIGURATION_NAME,
        'return_' . self::PACKAGE_TYPE_CONFIGURATION_NAME,
        'return_' . self::ONLY_RECIPIENT_CONFIGURATION_NAME,
        'return_' . self::AGE_CHECK_CONFIGURATION_NAME,
        'return_' . self::PACKAGE_FORMAT_CONFIGURATION_NAME,
        'return_' . self::RETURN_PACKAGE_CONFIGURATION_NAME,
        'return_' . self::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
        'return_' . self::INSURANCE_CONFIGURATION_NAME,
    ];

    const STATUS_CHANGE_MAIL_CONFIGURATION_NAME = 'MY_PARCEL_STATUS_CHANGE_MAIL';
    const SENT_ORDER_STATE_FOR_DIGITAL_STAMPS_CONFIGURATION_NAME = 'MY_PARCEL_SENT_ORDER_STATE_FOR_DIGITAL_STAMPS';
    const LABEL_SCANNED_ORDER_STATUS_CONFIGURATION_NAME = 'MY_PARCEL_LABEL_SCANNED_ORDER_STATUS';
    const DELIVERED_ORDER_STATUS_CONFIGURATION_NAME = 'MY_PARCEL_DELIVERED_ORDER_STATUS';
    const ORDER_NOTIFICATION_AFTER_CONFIGURATION_NAME = 'MY_PARCEL_ORDER_NOTIFICATION_AFTER';

    const IGNORE_ORDER_STATUS_CONFIGURATION_NAME = 'MY_PARCEL_IGNORE_ORDER_STATUS';
    const WEBHOOK_ID_CONFIGURATION_NAME = 'MY_PARCEL_WEBHOOK_ID';

    const POSTNL_CONFIGURATION_NAME = 'MYPARCEL_POSTNL';
    const BPOST_CONFIGURATION_NAME = 'MYPARCEL_BPOST';
    const DPD_CONFIGURATION_NAME = 'MYPARCEL_DPD';

    const SCANNED_STATUS = 3;
    const DELIVERED_STATUS = 7;
    const RETURN_PICKED_STATUS = 11;

    const EXCLUSIVE_FIELDS_NL = [
        self::AGE_CHECK_CONFIGURATION_NAME,
        self::RETURN_PACKAGE_CONFIGURATION_NAME,
        'return_' . self::AGE_CHECK_CONFIGURATION_NAME,
        'return_' . self::RETURN_PACKAGE_CONFIGURATION_NAME,
        self::SENT_ORDER_STATE_FOR_DIGITAL_STAMPS_CONFIGURATION_NAME,
    ];
}
