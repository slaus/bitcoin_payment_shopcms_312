<?php
        /**
         * @connect_module_class_name BitCoin
         *
         */
        class BitCoin extends PaymentModule {

                var $processing_url = 'https://api.cryptonator.com/api/merchant/v1/startpayment';

                function _initVars(){


                        $this->title                 = "Bit�oin";
                        $this->description         = "������ �������� � ������ �������������� ������. ���� ������ ����� ������������ ��� �������������� ������� �������� ������� ����� ��������� https://www.cryptonator.com. ����� ��� ���������� - http(s)://".$_SERVER['HTTP_HOST']."/index.php?bitcoin. �� �������� ������� �������� ������� � ���������� ����� ������.";
                
                        $this->sort_order = 1;

                        $this->Settings = array(
                                        'CONF_BITCOIN_PRODUCT_ID',
                                        'CONF_BITCOIN_CURCODE',
                                );
                }

                function _initSettingFields(){

                        $this->SettingsFields['CONF_BITCOIN_PRODUCT_ID'] = array(
                                'settings_value'                 => '',
                                'settings_title'                         => 'ID-��������',
                                'settings_description'         => '��� ���������� ����� ���� �������� � ����� �������� https://ru.cryptonator.com/.',
                                'settings_html_function'         => 'setting_TEXT_BOX(0,',
                                'sort_order'                         => 1,
                        );
                        $this->SettingsFields['CONF_BITCOIN_CURCODE'] = array(
                                'settings_value'                 => '',
                                'settings_title'                         => '������� ���',
                                'settings_description'         => '����� ������, ������������ � �����������, ����������� � �������� ���. �������� ������ �� ������, ������� ������������ ����� ������� ��� - ��� ���������� ��� ����������� ��������� ����� ������ � �������. ���� ������ �� �������, ����� �� ����� ���������������.',
                                'settings_html_function'         => 'setting_CURRENCY_SELECT(',
                                'sort_order'                         => 1,
                        );
                }

                function after_processing_html( $orderID ){

                        $res = '';

                        $order = ordGetOrder( $orderID );
                        $order_amount = roundf(PaymentModule::_convertCurrency($order['order_amount'],0,$this->_getSettingValue('CONF_BITCOIN_CURCODE')));

                        $currency = currGetCurrencyByID($this->_getSettingValue('CONF_BITCOIN_CURCODE'));

                        $zone_iso2 = $order['billing_state'];

                        $countries = cnGetCountries(array('offset'=>0,'CountRowOnPage'=>1000000), $count_row);

                        foreach ($countries as $country){

                                if($country['country_name'] == $order['billing_country']){

                                        $country_iso3 = $country['country_iso_3'];
                                        $zones = znGetZones($country['countryID']);

                                        foreach ($zones as $zone){

                                                if($zone['zone_name']==$zone_iso2){

                                                        $zone_iso2 = $zone['zone_code'];
                                                        break;
                                                }
                                        }
                                        break;
                                }
                        }

                        $post_1=array(
                                'merchant_id' => $this->_getSettingValue('CONF_BITCOIN_PRODUCT_ID'),
                                'item_name' => CONF_SHOP_NAME,
                                'invoice_currency' => 'usd',
                                'invoice_amount' => $order_amount,
                                
                                //'f_name' => $order['billing_firstname'],
                                //'s_name' => $order['billing_lastname'],
                                //'street' => $order['billing_address'],
                                //'city' => $order['billing_city'],
                                //'state' => $zone_iso2,
                                //'country' => $country_iso3,
                                //'email' => $order['customer_email'],

                                'language' => 'en',
                                //'success_url' => getTransactionResultURL('success'),
                                //'failed_url' => getTransactionResultURL('failure'),
                                'success_url' => 'https://www.big-up.shop/pages/successful-payment.html',
                                'failed_url' => 'https://www.big-up.shop/pages/payment-failed.html',
                        );

      $hidden_fields_html = '';
      reset($post_1);

      while(list($k,$v)=each($post_1)){

                                $hidden_fields_html .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />'."\n";
      }

                        $res = '
                                <form method="get" action="'.xHtmlSpecialChars($this->processing_url).'" style="text-align:center;margin-top:30px">
                                        '.$hidden_fields_html.'
                                        <input type="submit" class="btn btn-danger" value="Pay with cryptocurrency" />
                                </form>
                                ';

                        return $res;
                }
        }
?>