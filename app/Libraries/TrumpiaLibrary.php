<?php

namespace App\Libraries;

use App\Trumpia;
use \GuzzleHttp\Client as Guzzle;

class TrumpiaLibrary
{
    static private function request($uri, $type, $data = [], $method = 'put')
    {
        $client = new Guzzle(['base_uri' => config('services.trumpia.url')]);
        $response = $client->request(strtoupper($method), $uri, [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Apikey' => config('services.trumpia.key'),
            ],
            'http_errors' => false,
            'json' => $data,
        ]);

        $json = json_decode($response->getBody(), true);
        return $method == 'get' ? $json : self::response($type, $data, $json, $response->getStatusCode());
    }

    static private function response($type, $data, $response, $code)
    {
        if ( ! empty($response['status_code'])) {
            $error = self::message($response['status_code']);
        }

        /*if ( ! empty($response['request_id'])) {*/
            $data = [
                'token_id' => config('token.id'),
                'request_id' => ! empty($response['request_id']) ? $response['request_id'] : '',
                'type' => $type,
                'message' => $error,
                'data' => $data,
                'response' => $response,
                'push' => [],
            ];
            $trumpia = Trumpia::create($data);
        /*}*/
        
        return [
            'code' => $code,
            'message' => $error,
            'data' => $response,
        ];
    }

    static public function allCompanies()
    {
        return self::request('orgname', 'company/all', [], 'get');
    }

    static public function saveCompany($name)
    {
        return self::request('orgname', 'company/save', [
            'name' => $name,
            'description' => 'New Organization name to the new group of users',
        ]);
    }

    static public function removeCompany($code)
    {
        return self::request('orgname/'.$code, 'company/remove', [], 'delete');
    }

    static public function sendText($phone, $company, $text, $attachment = false, $landline = false)
    {
        $country_code = 1;
        if ($phone == '2222222222') {
            $phone = '+380981745686';
            $country_code = 0;
        }

        if ($phone == '3333333333') {
            $phone = '+380508617135';
            $country_code = 0;
        }
        $text = str_replace(["‘", "’"], "'", $text);
        $data = [
            'country_code' => $country_code,
            'mobile_number' => $phone,
            'org_name_id' => $company,
            'message' => [
                'text' => $text,
            ],
        ];

        if ( ! empty($attachment)) {
            $data['message']['media'] = $attachment;
        }

        if ( ! empty($landline)) {
            $data['sender'] = config('services.trumpia.landline');
        }

        return self::request('mobilemessage', 'message/send', $data);
    }

    static public function allKeywords()
    {
        return self::request('keyword', 'keyword/all', [], 'get');
    }

    static public function saveKeyword($data)
    {
        $keyword = [
            'keyword' => $data['keyword'],
            'lists' => '2171951',
            'org_name_id' => $data['code'],
            'optin_type' => 1,
        ];

        if ( ! empty($data['response'])) {
            $keyword['allow_message'] = 'true';
            $keyword['auto_response'] = [
                'message' => $data['response'],
                'frequency' => 2,
            ];
        }

        if ( ! empty($data['email'])) {
            $keyword['notify']['email'] = $data['email'];
            $keyword['notify']['subscription'] = 'new';
        }

        if ( ! empty($data['phone'])) {
            $keyword['notify']['mobile'] = $data['phone'];
            $keyword['notify']['subscription'] = 'new';
        }

        return self::request('keyword', 'keyword/save', $keyword);
    }

    static public function message($code)
    {
        $message = '';
        switch ($code) {
            // Common
            case 'MPCE0000': $message = 'The request was successful.'; break;
            case 'MPCE0101': $message = 'The request failed to be authenticated.'; break;
            case 'MPCE0301': $message = 'The request failed due to a temporary issue. Please retry in a few moments.'; break;
            case 'MPCE0302': $message = 'API Call is temporarily disabled due to an internal issue.'; break;
            case 'MPCE0801': $message = 'You must enter at least one proper parameter.'; break;
            case 'MPCE3101': $message = 'Your account is past due.'; break;
            case 'MPCE3102': $message = 'Your trial period has expired.'; break;
            case 'MPCE3201': $message = 'You do not have enough Domestic Text Credits to process this request.'; break;
            case 'MPCE3202': $message = 'You do not have enough International Text Credits to process this request.'; break;
            case 'MPCE3203': $message = 'There was a credit error.'; break;
            case 'MPCE3301': $message = 'The number of mobile keywords you are trying to create exceeds your current plan\'s limit.'; break;
            case 'MPCE3302': $message = 'The number of subscriptions to which you are trying to send a message exceeds your current plan\'s limit.'; break;
            case 'MPCE3303': $message = 'The total number of emails exceeds your current plant\'s limit.'; break;
            case 'MPCE4002': $message = 'Unexpected error. Please contact your support team.'; break;

            // List
            case 'MPLE1101': $message = 'The list name exceeds the maximum length allowed.'; break;
            case 'MPLE1102': $message = 'The display name exceeds the maximum length allowed.'; break;
            case 'MPLE1103': $message = 'The frequency exceeds the maximum length allowed.'; break;
            case 'MPLE1104': $message = 'The description exceeds the maximum length allowed.'; break;
            case 'MPLE1201': $message = 'The list name includes special characters or spaces, which are not allowed.'; break;
            case 'MPLE1202': $message = 'The display name includes special characters or spaces, which are not allowed.'; break;
            case 'MPLE1203': $message = 'The frequency includes a non-numeric value, which is not allowed.'; break;
            case 'MPLE1204': $message = 'The description includes special characters, which are not allowed.'; break;
            case 'MPLE2101': $message = '"list_name" cannot be empty.'; break;
            case 'MPLE2102': $message = '"display_name" cannot be empty.'; break;
            case 'MPLE2103': $message = '"frequency" cannot be empty.'; break;
            case 'MPLE2104': $message = '"description" cannot be empty.'; break;
            case 'MPLE2401': $message = 'The list name is already registered.'; break;
            case 'MPLE1205': $message = 'The list ID can include only numerical values; no other characters are allowed.'; break;
            case 'MPLE2105': $message = 'A list ID has not been entered.'; break;
            case 'MPLE2301': $message = 'The list ID you queried does not exist.'; break;
            case 'MPLE2001': $message = 'The list cannot be deleted because it is in use by another feature you are using. For example, you cannot delete a list for a mobile keyword if that same list has been assigned to mobile coupons.'; break;

            // Subscription
            case 'MPSE0401': $message = 'PUT Subscription error message with no API contract.'; break;
            case 'MPSE0501': $message = 'The tool being added is blocked. Note: Tools include - email, mobile phone, landline phone, and AOL IM.'; break;
            case 'MPSE1001': $message = 'The allow-message parameter only accepts only LANDLINE or MOBILE as values.'; break;
            case 'MPSE1002': $message = 'Valid values are fixed by the options of the custom data field. You can get the valid values via GET Custom Data Field.'; break;
            case 'MPSE1101': $message = 'Each list name can be only 1-30 characters in length.'; break;
            case 'MPSE1102': $message = 'The first name of the subscription exceeds the maximum length allowed.'; break;
            case 'MPSE1103': $message = 'The last name of the subscription exceeds the maximum length allowed.'; break;
            case 'MPSE1105': $message = 'Max length is fixed by the options of the custom data field. You can get the valid values via GET Custom Data Field.'; break;
            case 'MPSE1106': $message = 'Mobile number must be 10 characters when the country_code is 1, and 1-20 characters when the country_code is not 1.'; break;
            case 'MPSE1107': $message = 'Landline number must be 10 characters.'; break;
            case 'MPSE1201': $message = 'The list name includes special characters, which are not allowed.'; break;
            case 'MPSE1204': $message = 'The custom data ID can include only numerical values; no other characters are allowed.'; break;
            case 'MPSE1205': $message = 'The custom data value includes special characters, which are not allowed.'; break;
            case 'MPSE1208': $message = 'The mobile number is only made up of numerical values; no other characters are allowed.'; break;
            case 'MPSE1209': $message = 'The landline number is only made up of numerical values; no other characters are allowed.'; break;
            case 'MPSE2001': $message = 'An invalid phone number was entered. Supported countries for voice are U.S., Canada, Guam and Puerto Rico.'; break;
            case 'MPSE2004': $message = 'Country_code should be 1 only for landline number. For now, we don’t allow other country codes.'; break;
            case 'MPSE2101': $message = 'The list name is empty.'; break;
            case 'MPSE2102': $message = 'The subscriptions are empty.'; break;
            case 'MPSE2103': $message = 'Tool information is missing for the subscription being added.'; break;
            case 'MPSE2109': $message = 'A custom data ID has not been entered.'; break;
            case 'MPSE2110': $message = 'A value has not been entered.'; break;
            case 'MPSE2201': $message = 'The mobile number is incorrectly formatted.'; break;
            case 'MPSE2202': $message = 'The landline number is incorrectly formatted.'; break;
            case 'MPSE2203': $message = 'The email address is incorrectly formatted.'; break;
            case 'MPSE2207': $message = 'The format of the date’s value is incorrect.'; break;
            case 'MPSE2208': $message = 'Mobile number may not start with 0 or 1 when the country_code is 1, and may not start with 0 when the country_code is not 1.'; break;
            case 'MPSE2209': $message = 'Landline number may not start with 0 or 1.'; break;
            case 'MPSE2302': $message = 'The list name being added was not found.'; break;
            case 'MPSE2306': $message = 'The custom data ID you entered does not exist.'; break;
            case 'MPSE2401': $message = 'The tool being added has already been registered.'; break;
            case 'MPSE2501': $message = 'Value should be in a fixed number range. You can get the fixed number range via GET Custom Data Field.'; break;
            case 'MPSE2502': $message = 'Valid value is decided by the default and interval values. You can get these values via GET Custom Data Field.'; break;
            case 'MPSE1202': $message = 'The subscription ID can include only numerical values; no other characters are allowed.'; break;
            case 'MPSE2303': $message = 'The requested subscription is invalid.'; break;
            case 'MPSE1003': $message = 'Row_size must have a value from 100 to 10000.'; break;
            case 'MPSE1206': $message = 'The row_size is made up of only numerical values; no other characters are allowed.'; break;
            case 'MPSE1207': $message = 'The page is made up of only numerical values; no other characters are allowed.'; break;
            case 'MPSE4001': $message = 'Selected page does not have any subscriptions.'; break;
            case 'MPSE1104': $message = 'Length of search data exceeded the character length limit. Please limit the search_data to 1-90 characters.'; break;
            case 'MPSE1203': $message = 'The list ID can include only numerical values; no other characters are allowed.'; break;
            case 'MPSE2002': $message = 'Search type value is unsupported type. Supported search type values are 1~4 and 102~105.'; break;
            case 'MPSE2106': $message = 'Search type is not specified.'; break;
            case 'MPSE2107': $message = 'Search data is not specified.'; break;
            case 'MPSE2108': $message = 'A list ID has not been entered.'; break;
            case 'MPSE2205': $message = 'Unsupported date format. Dates have to be in the format of “YYYY-MM-DD hh:mm:ss”.'; break;
            case 'MPSE2206': $message = 'Unsupported date range format. Dates have to be in the format of “YYYY-MM-DD hh:mm:ss~YYYY-MM-DD hh:mm:ss”.'; break;
            case 'MPSE2304': $message = 'The list ID you queried does not exist.'; break;
            case 'MPSE2305': $message = 'No subscription was found with given criteria - search type and search data.'; break;

            // Message
            case 'MPME0401': $message = 'The account does not have permission to use MMS.'; break;
            case 'MPME0601': $message = 'Message content contains inappropriate phrase(s), and is caught by spam filter.'; break;
            case 'MPME0602': $message = 'Organization name contains inappropriate phrase(s), and is caught by spam filter.'; break;
            case 'MPME0603': $message = 'Email subject contains inappropriate phrase(s), and is caught by spam filter.'; break;
            case 'MPME1001': $message = 'The recipient type you have entered is not a valid subscription or list.'; break;
            case 'MPME1002': $message = 'The file type of your MMS attachment is not supported.'; break;
            case 'MPME1003': $message = 'An invalid resource URL was entered.'; break;
            case 'MPME1101': $message = 'The body of your SMS message exceeds the maximum length allowed.'; break;
            case 'MPME1102': $message = 'The body of your MMS message exceeds the maximum length allowed.'; break;
            case 'MPME1103': $message = 'The subject line of your email message exceeds the maximum length allowed.'; break;
            case 'MPME1105': $message = 'The first name from the mail merge exceeds the maximum length allowed.'; break;
            case 'MPME1106': $message = 'The last name from the mail merge exceeds the maximum length allowed.'; break;
            case 'MPME1107': $message = 'The email content exceeds the maximum length allowed.'; break;
            case 'MPME1108': $message = 'The description exceeds the maximum length allowed.'; break;
            case 'MPME1109': $message = 'The MMS subject exceeds the maximum length allowed.'; break;
            case 'MPME1110': $message = 'The body of your SMS message exceeds the maximum length allowed.'; break;
            case 'MPME1201': $message = 'Your list ID includes special characters or spaces, which are not allowed.'; break;
            case 'MPME1202': $message = 'Your subscription ID includes special characters or spaces, which are not allowed.'; break;
            case 'MPME1204': $message = 'The SMS message includes special characters, which are not allowed.'; break;
            case 'MPME1205': $message = 'The MMS message includes special characters, which are not allowed.'; break;
            case 'MPME1206': $message = 'The first name includes special characters, which are not allowed.'; break;
            case 'MPME1207': $message = 'The last name includes special characters, which are not allowed.'; break;
            case 'MPME1208': $message = 'The organization name ID can only include positive numerical values.'; break;
            case 'MPME2001': $message = 'Both SMS and MMS cannot be selected for the same mobile text campaign.'; break;
            case 'MPME2002': $message = 'There is an insufficient supply of coupons.'; break;
            case 'MPME2003': $message = 'The coupon included in your message expires in less than 24 hours. Please set a longer redemption period.'; break;
            case 'MPME2005': $message = 'Only one coupon can be included in your email message.'; break;
            case 'MPME2007': $message = 'Only one coupon can be included in your mobile text message.'; break;
            case 'MPME2008': $message = 'The coupon included in your email and mobile text message is different. They must be the same.'; break;
            case 'MPME2009': $message = 'There are no recipients in your selected list(s).'; break;
            case 'MPME2010': $message = 'The entered schedule date is in the past.'; break;
            case 'MPME2011': $message = 'The coupon included in your message is invalid.'; break;
            case 'MPME2012': $message = 'The coupon included in your message is expired.'; break;
            case 'MPME2013': $message = 'The file size of your MMS attachment is too large.'; break;
            case 'MPME2015': $message = 'You must enter 1 to 100 list IDs for the recipients value.'; break;
            case 'MPME2016': $message = 'You must enter 1 to 500 subscription IDs for the recipients value.'; break;
            case 'MPME2017': $message = 'The organization name ID is not verified. Organization name IDs must be verified before it can be used.'; break;
            case 'MPME2020': $message = 'Selected mobile numbers are registered with carriers that do not support MMS. Here is a list of MMS supported carriers: AT&T, Verizon, T-Mobile, Sprint, Alltel, US Cellular, Cricket, Boost-CDMA, Boost Mobile.'; break;
            case 'MPME2021': $message = 'Invalid mail data entered in the SMS message. You can get information about the error via the error_data parameter.'; break;
            case 'MPME2022': $message = 'Invalid mail data entered in the MMS message. You can get information about the error via the error_data parameter.'; break;
            case 'MPME2023': $message = 'Invalid mail data entered in the email. You can get information about the error via the error_data parameter.'; break;
            case 'MPME2024': $message = 'You can add up to 10 custom data fields for the SMS message.'; break;
            case 'MPME2025': $message = 'You can add up to 10 custom data fields for the MMS message.'; break;
            case 'MPME2026': $message = 'You can add up to 10 custom data fields for the email.'; break;
            case 'MPME2102': $message = 'The body of your SMS message is empty.'; break;
            case 'MPME2103': $message = 'The body of your MMS message is empty.'; break;
            case 'MPME2104': $message = 'The reply to email address of your message is empty.'; break;
            case 'MPME2105': $message = '"description" cannot be empty.'; break;
            case 'MPME2106': $message = 'No messaged are entered in your request such as email, SMS/MMS, or IM.'; break;
            case 'MPME2107': $message = 'A recipients has not been entered.'; break;
            case 'MPME2108': $message = 'A recipients type has not been entered.'; break;
            case 'MPME2109': $message = 'A recipients value has not been entered.'; break;
            case 'MPME2110': $message = 'A email subject has not been entered.'; break;
            case 'MPME2111': $message = 'A email content has not been entered.'; break;
            case 'MPME2113': $message = 'A MMS subject has not been entered.'; break;
            case 'MPME2115': $message = 'A resource has not been entered.'; break;
            case 'MPME2116': $message = 'A first name has not been entered.'; break;
            case 'MPME2117': $message = 'A last name has not been entered.'; break;
            case 'MPME2118': $message = 'A send date has not been entered.'; break;
            case 'MPME2201': $message = 'The format of the send date and time is incorrect.'; break;
            case 'MPME2202': $message = 'The resource must include http://.'; break;
            case 'MPME2301': $message = 'You have entered an invalid subscription ID.'; break;
            case 'MPME2302': $message = 'An invalid list ID was inputted.'; break;
            case 'MPME2303': $message = 'The resource you entered does not exist.'; break;
            case 'MPME2305': $message = 'The organization name ID you queried does not exist.'; break;
            case 'MPME1203': $message = 'The message ID is only made up of numerical values; no other characters are allowed.'; break;
            case 'MPME2014': $message = 'Your message was not sent due to insufficient supply of coupons.'; break;
            case 'MPME2304': $message = 'You have entered an invalid message ID.'; break;
            case 'MPME3001': $message = 'Your request has failed due to inadequate conditions.'; break;
            case 'MPME1209': $message = 'The offset is a positive number.'; break;
            case 'MPME2018': $message = 'The limit must have a value between 1 and 1000.'; break;
            case 'MPME2019': $message = 'The order parameter only accepts scheduled_at or -scheduled_at as values.'; break;

            // Direct SMS
            case 'MRCE0101': $message = 'Unauthorized access.'; break;
            case 'MRCE0301': $message = 'The request failed due to a temporary issue. Please retry in a few moments.'; break;
            case 'MRCE0401': $message = 'The account does not have permission to use the API resource.'; break;
            case 'MRCE0801': $message = 'You must enter at least one proper parameter.'; break;
            case 'MRCE3101': $message = 'Your account is past due.'; break;
            case 'MRCE3102': $message = 'Your account is suspended.'; break;
            case 'MRCE3103': $message = 'Your trial period has expired.'; break;
            case 'MRCE3201': $message = 'You do not have enough Domestic Text Credits to process this request.'; break;
            case 'MRCE3202': $message = 'You do not have enough International Text Credits to process this request.'; break;
            case 'MRCE3203': $message = 'There was some other type of credit error.'; break;
            case 'MRCE3301': $message = 'The number of mobile keywords you are trying to create exceeds your current plan\'s limit.'; break;
            case 'MRCE3302': $message = 'The number of subscriptions you are trying to send a message to exceeds your current plan\'s limit.'; break;
            case 'MRCE3303': $message = 'The total number of emails exceeds your current plant\'s limit.'; break;
            case 'MRME0551': $message = 'The tool being added is blocked.'; break;
            case 'MRME0651': $message = 'Message content contains inappropriate phrase(s), and is caught by spam filter.'; break;
            case 'MRME0652': $message = 'Organization name contains inappropriate phrase(s), and is caught by spam filter.'; break;
            case 'MRME1051': $message = 'Mobile number must be 10 characters when the country_code is 1, and 1-20 characters when the country_code is not 1.'; break;
            case 'MRME1052': $message = 'Only numerical values are valid.'; break;
            case 'MRME1053': $message = 'Mobile number may not start with 0 or 1 when the country_code is 1, and may not start with 0 when the country_code is not 1.'; break;
            case 'MRME1054': $message = 'The body of your SMS message exceeds the maximum length allowed.'; break;
            case 'MRME1055': $message = 'The body of your SMS message exceeds the maximum length allowed.'; break;
            case 'MRME1056': $message = 'Only numerical values are valid.'; break;
            case 'MRME1251': $message = 'The SMS message includes special characters, which are not allowed.'; break;
            case 'MRME2001': $message = 'The mobile number is incorrectly formatted.'; break;
            case 'MRME2151': $message = 'A message has not been inputted.'; break;
            case 'MRME2152': $message = 'The mobile number us empty.'; break;
            case 'MRME2251': $message = 'An invalid country code was entered.'; break;
            case 'MRME2551': $message = 'US(1) and CA(1) are the valid values for the intended sender.'; break;
            case 'MRME2552': $message = 'Entered sender is invalid for your account.'; break;
            case 'MRME1252': $message = 'The SMS ID is only made up of numerical values; no other characters are allowed.'; break;
            case 'MRME2351': $message = 'You have entered an invalid SMS ID.'; break;
            case 'MRME3051': $message = 'Your request has failed due to inadequate conditions.'; break;

            //Direct Mobile Message
            case 'MRME0402': $message = 'You must complete the API Certification to be able to message contacts that have not opted in.'; break;
            case 'MRME0552': $message = 'The tool you are trying to add is blocked.'; break;
            case 'MRME0653': $message = 'Text content contains inappropriate phrase(s) and was caught by our spam filter.'; break;
            case 'MRME0654': $message = 'Organization name contains inappropriate phrase(s) and was caught by our spam filter.'; break;
            case 'MRME1057': $message = 'Invalid mobile number: Only numerical values are valid.'; break;
            case 'MRME1058': $message = 'Mobile number must be 10 characters when the country_code is 1, and 1-20 characters when the country_code is not 1.'; break;
            case 'MRME1059': $message = 'Mobile number may not start with 0 or 1 when the country_code is 1, and may not start with 0 when the country_code is not 1.'; break;
            case 'MRME1060': $message = 'Only numerical values are valid.'; break;
            case 'MRME1061': $message = 'An invalid media URL was entered.'; break;
            case 'MRME1062': $message = 'The file type of your MMS attachment is not supported.'; break;
            case 'MRME1063': $message = 'If the sender is registered a landline number, you cannot send video file.'; break;
            case 'MRME1101': $message = 'The text exceeds the maximum length allowed.'; break;
            case 'MRME1102': $message = 'The MMS subject exceeds the maximum length allowed.'; break;
            case 'MRME1255': $message = 'The text includes special characters, which are not allowed.'; break;
            case 'MRME1256': $message = 'The MMS subject includes special characters, which are not allowed.'; break;
            case 'MRME1257': $message = 'The organization name ID can only include positive numerical values.'; break;
            case 'MRME2002': $message = 'The mobile number is incorrectly formatted.'; break;
            case 'MRME2003': $message = 'Every message must include text or media.'; break;
            case 'MRME2004': $message = 'The organization name ID provided has not been verified. Organization names must be verified before it can be used.'; break;
            case 'MRME2005': $message = 'Invalid mobile_number : Unable to find a valid country code from the mobile number.'; break;
            case 'MRME2006': $message = 'The file size of your MMS attachment is too large.'; break;
            case 'MRME2153': $message = 'The mobile number parameter must not be empty.'; break;
            case 'MRME2154': $message = 'A message has not been inputted.'; break;
            case 'MRME2155': $message = 'Text has not been inputted.'; break;
            case 'MRME2156': $message = 'A media URL has not been entered.'; break;
            case 'MRME2157': $message = 'A MMS subject has not been entered.'; break;
            case 'MRME2201': $message = 'The media URL must include http:// or https://.'; break;
            case 'MRME2353': $message = 'The media URL you entered does not exist.'; break;
            case 'MRME2354': $message = 'The organization name ID queried does not exist.'; break;
            case 'MRME2554': $message = 'US(1) and CA(1) are the valid values for the intended sender.'; break;
            case 'MRME2555': $message = 'Invalid sender value. Verify sender numbers in your account under Manage -> Utilities.'; break;
            case 'MRME2556': $message = 'The carrier for this mobile number does not support Free to End User.'; break;
            case 'MRME2557': $message = 'LMS/MMS is only supported in the US.'; break;
            case 'MRME2558': $message = 'The mobile numbers selected are registered with carriers that do not support MMS. The following is a list of carriers that support MMS: AT&T, Verizon, T-Mobile, Sprint, Alltel, US Cellular, Cricket, Boost-CDMA, Boost Mobile.'; break;
            case 'MRME1254': $message = 'The task ID is only made up of numerical values; no other characters are allowed.'; break;
            case 'MRME2355': $message = 'You have entered an invalid task ID.'; break;
            case 'MRME3052': $message = 'Your request has failed due to inadequate conditions.'; break;

            // Organization Name
            case 'MPGE0601': $message = 'Name contains inappropriate phrase(s), and is caught by spam filter.'; break;
            case 'MPGE1101': $message = 'Invalid name. Name must be 1-32 characters in length.'; break;
            case 'MPGE1102': $message = 'Invalid description. Description must be 1-500 characters in length.'; break;
            case 'MPGE1201': $message = 'The name includes special characters, which are not allowed.'; break;
            case 'MPGE1202': $message = 'The description includes special characters, which are not allowed.'; break;
            case 'MPGE2101': $message = 'Name is empty.'; break;
            case 'MPGE2102': $message = 'Description is empty.'; break;
            case 'MPGE2401': $message = 'Entered name has been registered already.'; break;
            case 'MPGE1203': $message = 'The organization name ID can only include numerical values; no other characters are allowed.'; break;
            case 'MPGE2001': $message = 'The organization name is not verified. The default organization name must be verified.'; break;
            case 'MPGE2301': $message = 'Org_name_id was not found.'; break;
            case 'MPGE2002': $message = 'Default organization name cannot be deleted.'; break;

            // Status Report
            case 'MPRE2301': $message = 'The request_id provided is incorrect.'; break;
            case 'MPCE4001': $message = 'The request is still in progress. Please wait a few seconds.'; break;
        }

        return $message;
    }

    static public function report($code)
    {
        $message = '';
        switch ($code) {
            case 'DR000': $message = 'Message successfully delivered to the carrier.'; break;
            case 'DR001': $message = 'The mobile number has opted-out of future messages.'; break;
            case 'DR002': $message = 'The gateway is not routing messages to this network prefix.'; break;
            case 'DR003': $message = 'The mobile number has been deactivated by carrier due to cancellation or migration to another carrier.'; break;
            case 'DR004': $message = 'There was an error attempting to deliver the message to this subscriber\'s carrier.'; break;
            case 'DR005': $message = 'The messaging gateway failed to deliver the message to the phone provided.'; break;
            case 'DR006': $message = 'The mobile number is invalid and cannot receive messages. Do not retry.'; break;
            case 'DR007': $message = 'The SMS component of the message is greater than the allowed 160 characters or 70 Unicode characters.'; break;
            case 'DR008': $message = 'The message expired and was not delivered to the subscriber.'; break;
            case 'DR009': $message = 'The carrier rejected the message. This can be due to short code messaging blocked, anti-spam policies, or their wireless plan.'; break;
            case 'DR010': $message = 'The message was not sent due to a temporary system error. If it persists, please contact us.'; break;
            case 'DR011': $message = 'The message was accepted by the carrier, but has failed due to unknown reasons.'; break;
            case 'DR012': $message = 'An unknown carrier error has prevented the message from being delivered successfully.'; break;
            case 'DR013': $message = 'The mobile number does not support text messages.'; break;
            case 'DR014': $message = 'Due to TCPA regulations, subscribers may not be contacted after 9PM and before 8AM, local time.'; break;
            case 'DR015': $message = 'SMS could not be delivered due to a phone error. For example, the phone does not support SMS or is illegally registered on the network.'; break;
            case 'DR016': $message = 'SMS could not be delivered due to a temporary phone issue. For example, the phone is low on memory or is turned off.'; break;
            case 'DR017': $message = 'Unknown reason.'; break;
        }

        return $message;
    }
}
