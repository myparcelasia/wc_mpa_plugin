<?php 
if ( ! class_exists( 'MPA_Shipping_API' ) ) {
    class MPA_Shipping_API {

        private static $apikey = '';
        private static $apiSecret = '';
        private static $integration_id = '';
        private static $sender_postcode = '';
        private static $sender_state = '';
        private static $sender_country = '';
        private static $api_url = "localhost:8000/Apiv3/check_price"; //local
        // private static $api_url = "https://myparcelasia.com/Apiv3/check_price"; //production

         /**
         * init
         *
         * @access public
         * @return void
         */
        public static function init() {

            $WC_MPA_Shipping_Method = new WC_MPA_Shipping_Method();
            self::$integration_id = $WC_MPA_Shipping_Method->settings['api_key'] ;
            self::$sender_postcode = $WC_MPA_Shipping_Method->settings['sender_postcode'] ;
            self::$sender_state = "" ;

        }

        public static function getShippingRate($destination,$items,$weight)
        {
          $WC_Country = new WC_Countries();
          if($WC_Country->get_base_country() == 'MY'){
 

            if($weight == 0 || $weight ==''){$weight=0.1;}

            $i = 0;
            $length = "";
            $width = "";
            $height = "";
            foreach ($items as $item) {    
                 if (is_numeric($item[$i]['width']) && is_numeric($item[$i]['length']) && is_numeric($item[$i]['height'])) {
                    $length += $items[$i]['length'] ;
                    $width += $items[$i]['width'];
                    $height += $items[$i]['height'];
                 }
                 $i++;
            }
            
            $url = self::$api_url;

            $WC_MPA_Shipping_Method = new WC_MPA_Shipping_Method();

            if($WC_MPA_Shipping_Method->settings['cust_rate'] == 'normal_rate') {
                self::$integration_id = ''; 
            }

            //prevent user select fix Rate but didnt put postcode no result
            if($WC_MPA_Shipping_Method->settings['cust_rate']  == 'fix_rate' && self::$sender_postcode == '')
            { self::$sender_postcode = '50490';}

                
            $f = '{
                    "api_key": "'.self::$integration_id.'",
                    "sender_postcode": "'.self::$sender_postcode.'",
                    "receiver_postcode": "'.$destination["postcode"].'",
                    "declared_weight": '.$weight.'
                }';

            if($WC_Country->get_base_country()=='MY' && $destination["country"] == 'MY'){
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $f);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                ob_start();
                $r = curl_exec($ch);
                ob_end_clean();
                curl_close ($ch);
                $json = json_decode($r);
                if(sizeof($json->data->rates) > 0){
                
                    return $json->data->rates;
                }

            } else {
                return array();
            }
        }

            // should never reach here
            return array(); // return empty array
        }
    }
}