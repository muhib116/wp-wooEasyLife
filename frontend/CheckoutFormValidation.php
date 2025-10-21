<?php
namespace WooEasyLife\Frontend;

class CheckoutFormValidation {
    
    // --- Rate Limiting Configuration ---
    private const RATE_LIMIT_ATTEMPTS = 3; 
    private const RATE_LIMIT_SECONDS = 600; // 10 minutes
    // ------------------------------------

    public function __construct()
    {   
        add_action('woocommerce_checkout_create_order', [$this, 'modify_order_phone'], 10, 2);
        add_action('woocommerce_checkout_create_order', [$this, 'save_device_token_to_order'], 10, 2);

        add_action('woocommerce_after_checkout_validation', [$this, 'form_validation'], 10, 2);
    }

    /**
     * Validates all checkout form data including rate limiting, duplicates, and field formats.
     */
    public function form_validation($data, $errors) {
        global $config_data;

        if (!is_wel_license_valid()) {
            return; // Exit the *current* function if the license is not valid.
        }


        $admin_phone = $config_data['admin_phone'] ?? '';

        // // Step 1: Handle Rate Limiting first to prevent abuse.
        // $this->handle_rate_limiting($errors);

        // if (in_array('rate_limit_exceeded', $errors->get_error_codes())) {
        //     $error_message = 'আপনি খুব দ্রুত অর্ডার করার চেষ্টা করছেন। অনুগ্রহ করে ১০ মিনিট পর আবার চেষ্টা করুন।';
        //     if ($admin_phone) {
        //         $error_message .= ' জরুরি প্রয়োজনে আমাদের সাথে যোগাযোগ করুন: ';
        //         $error_message .= '<a href="tel:'.$admin_phone.'">'.$admin_phone.'</a>.';
        //     }
        //     throw new \Exception(__($error_message, 'woo-easy-life'));
        // }

        if ($config_data['only_bangladeshi_ip'] ?? false) {
            if(!is_bangladeshi_ip(get_customer_ip())) {
                $error_message = 'দুঃখিত, এই সাইটটি শুধুমাত্র বাংলাদেশ থেকে অ্যাক্সেসযোগ্য।';
                if ($admin_phone) {
                    $error_message .= ' যদি কোনো সহায়তার প্রয়োজন হয়, তাহলে আমাদের সাথে যোগাযোগ করুন: ';
                    $error_message .= '<a href="tel:'.$admin_phone.'">'.$admin_phone.'</a>.';
                }
                throw new \Exception(__($error_message, 'woo-easy-life'));
            }
        }

        // Step 2: Get all necessary POST data.
        $billing_phone      = isset($data['billing_phone']) ? normalize_phone_number(sanitize_text_field($data['billing_phone'])) : '';
        $billing_email      = isset($data['billing_email']) ? sanitize_email($data['billing_email']) : '';
        $customer_ip        = get_customer_ip();
        $billing_address_1  = isset($data['billing_address_1']) ? sanitize_text_field($data['billing_address_1']) : '';
        $billing_address_2  = isset($data['billing_address_2']) ? sanitize_text_field($data['billing_address_2']) : '';
        $first_name         = isset($data['billing_first_name']) ? sanitize_text_field($data['billing_first_name']) : '';
        $last_name          = isset($data['billing_last_name']) ? sanitize_text_field($data['billing_last_name']) : '';

        // Step 3: Perform Duplicate Order Check (if enabled).
        if (!empty($config_data['validate_duplicate_order'])) {
            $duplicate_order_id = $this->has_identical_product_order($billing_phone, $billing_email, $customer_ip);
            if ($duplicate_order_id) {
                $error_message = 'আপনি ইতিমধ্যেই এই পণ্যগুলো দিয়ে একটি অর্ডার করেছেন যা এখনও প্রক্রিয়াধীন আছে।';
                $error_message .= ' আপনার পূর্ববর্তী অর্ডার আইডি: ' . '<strong>#' . $duplicate_order_id . '</strong>.';
                if ($admin_phone) {
                    $error_message .= ' যদি কোনো সহায়তার প্রয়োজন হয়, তাহলে আমাদের সাথে যোগাযোগ করুন: ';
                    $error_message .= '<a href="tel:'.$admin_phone.'">'.$admin_phone.'</a>.';
                }
                throw new \Exception(__($error_message, 'woo-easy-life'));
            }
        }

        // Step 4: Perform General Form Validation (if enabled).
        if (empty($config_data['validate_checkout_form'])) {
            return;
        }

        $billing_address    = trim($billing_address_1 . ' ' . $billing_address_2);
        $name               = trim($first_name . ' ' . $last_name);

        if (!$this->validate_address($billing_address)) throw new \Exception(__('আপনার দেওয়া ঠিকানাটি সঠিক বলে মনে হচ্ছে না। অনুগ্রহ করে সম্পূণর্ন ঠিকানাটি সঠিকভাবে লিখুন।', 'woo-easy-life'));
        if (!$this->validate_name($name)) throw new \Exception(__('আপনার নামটি সঠিক বলে মনে হচ্ছে না। অনুগ্রহ করে আপনার সঠিক নাম প্রদান করুন।', 'woo-easy-life'));
        if (!validate_BD_phoneNumber($billing_phone)) throw new \Exception(__('আপনার বিলিং ফোন নম্বরটি সঠিক নয়। অনুগ্রহ করে একটি সঠিক নম্বর প্রদান করুন।', 'woo-easy-life'));
    }
    
    /**
     * A stronger function to check if an identical set of products has been ordered before
     * by a customer identified by phone, email, OR IP address.
     */
    public function has_identical_product_order($billing_phone = null, $billing_email = null, $customer_ip = null)
    {

        if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
            return false;
        }

        $statuses_to_check = ['wc-processing', 'wc-confirmed', 'wc-on-hold', 'wc-pending', 'wc-cancelled'];
        
        $base_args = [
            'limit'       => -1, 'orderby' => 'date', 'order' => 'DESC',
            'status'      => $statuses_to_check, 'type' => 'shop_order',
            'date_query'  => [['after' => date('Y-m-d H:i:s', strtotime("-24 hours")), 'inclusive' => true]],
        ];

        $past_orders = [];

        // Find by Phone Number (independent check)
        if ($billing_phone) {
            $past_orders = array_merge($past_orders, wc_get_orders(array_merge($base_args, ['billing_phone' => $billing_phone])));
        }
        
        // Find by Email Address (independent check)
        if ($billing_email) {
            $past_orders = array_merge($past_orders, wc_get_orders(array_merge($base_args, ['customer' => $billing_email])));
        }
        
        // Find by IP Address (independent check)
        if ($customer_ip) {
            $ip_args = $base_args;
            $ip_args['meta_query'] = [['key' => '_customer_ip_address', 'value' => $customer_ip, 'compare' => '=']];
            $past_orders = array_merge($past_orders, wc_get_orders($ip_args));
        }

        if (empty($past_orders)) {
            return false;
        }
        
        $unique_past_orders = [];
        foreach ($past_orders as $order) {
            $unique_past_orders[$order->get_id()] = $order;
        }

        $current_cart_signature = $this->_get_product_ids_signature(WC()->cart->get_cart());

        foreach ($unique_past_orders as $order) {
            $previous_order_signature = $this->_get_product_ids_signature($order->get_items());
            if ($current_cart_signature === $previous_order_signature) {
                return $order->get_id();
            }
        }
    
        return false;
    }

    private function _get_product_ids_signature($items) {
        if (empty($items)) return '';
        $product_ids = [];
        foreach ($items as $item) {
            $product_ids[] = $item instanceof \WC_Order_Item_Product ? $item->get_product_id() : $item['product_id'];
        }
        sort($product_ids);
        return implode(',', $product_ids);
    }
    
    public function modify_order_phone($order, $data) {
        if (isset($data['billing_phone'])) $order->set_billing_phone(normalize_phone_number($data['billing_phone']));
        if (isset($data['shipping_phone'])) $order->set_shipping_phone(normalize_phone_number($data['shipping_phone']));
    }

    private function handle_rate_limiting(&$errors) {
        $ip = $this->get_customer_ip();
        if (!$ip) return;
        $transient_key = __PREFIX . 'rate_limit_' . md5($ip);
        $timestamps = get_transient($transient_key) ?: [];
        $current_time = time();
        $recent_timestamps = array_filter($timestamps, function($ts) use ($current_time) {
            return ($current_time - $ts) < self::RATE_LIMIT_SECONDS;
        });
        if (count($recent_timestamps) >= self::RATE_LIMIT_ATTEMPTS) {
            $errors->add('rate_limit_exceeded', 'Rate limit exceeded.');
            return;
        }
        $recent_timestamps[] = $current_time;
        set_transient($transient_key, $recent_timestamps, self::RATE_LIMIT_SECONDS);
    }

    private function get_customer_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP']));
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        elseif (!empty($_SERVER['REMOTE_ADDR'])) $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        $ip_array = explode(',', $ip);
        $ip = trim($ip_array[0]);
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : false;
    }

    private $blacklist = [
        // English sexual/obscene
        'fuck','fuk','f*ck','f@ck','phuck','fukkk','motherfucker','mofo',
        'shit','sh1t','sht','bullshit','holyshit',
        'ass','arse','a55','asshole','butthole','buttface',
        'dick','dik','d1ck','cock','c0ck','kawk','k0k','prick',
        'pussy','pusy','pussee','pussee','pussi','pusycat',
        'vagina','vag','vaj','vajina','cunt','cnt',
        'boobs','tits','tit','t1ts','boobies','hooters',
        'bastard','b1tch','biatch','bitch','btch','slut','s1ut','slag',
        'whore','h0e','hoe','prostitute','escort',
        'jerk','wanker','tosser','bugger',
        'faggot','f@g','fag','gaylord','homo',
        'porn','pr0n','pron','xxx','sex','s3x','sexx','sx','horny',
        'rape','rapist','molest','molester','incest',
        'anal','anul','anus','rimming','rimjob','blowjob','bj','handjob',
        'cum','jizz','spunk','orgasm','squirt','masturbate','jerkoff','wank',
        'dildo','vibrator','buttplug','strapon','bdsm',

        // General insults
        'idiot','stupid','dumb','moron','loser','lame','jerk',
        'retard','r3tard','retarded',
        'ugly','freak','weirdo','clown',

        // Racist/ethnic/religious (common filter sets)
        'nigger','n1gger','nigga','nigg','negro','coon',
        'chink','gook','spic','wetback','beaner',
        'paki','terrorist','jihad','isis',
        'hitler','nazi','heil','kkk','slave',

        // Bangla obscenities (common transliterations + originals)
        'পুটকি', 'গান্ড', 'মাগী', 'চোদ', 'চুদ', 'চোদা', 'চোদন', 'বেশ্যা', 'বাটপার', 'হারামজাদা',
        'হারামি', 'শালা', 'শালী', 'খানকি', 'ধোন', 'লৌড়া', 'লাওড়া', 'ল্যাঠ', 'ভোদা', 'গুদ',
        'মাদারচোদ', 'বাপচোদ', 'ভাইচোদ', 'চুতমারান', 'চুতিয়া', 'চুতি', 'চুত', 'চুদার', 'পোলা', 'চুদ',
        'পোলাচুদা', 'ছালার', 'মাদারচুদি', 'চোদনিয়া', 'গুন্ডা', 'ছোটি', 'ফুদি', 'ফুদ', 'ছাতি', 'পোঁদ',
        'পোঁদা', 'চোদনা', 'চুদি', 'পুটু', 'চুদখোর', 'গুন্ডামি', 'লেংট', 'লুরা', 'চোদবাজ', 'ভোদার', 'চোদপো',
        'মাগীর', 'গুদচোদ', 'চোদগু', 'পোলা', 'চুদবাজ', 'চুদখোর', 'চুদপো', 'মাদারচুদি', 'বাপচোদি', 'ছালার', 'গুন্ডাচোদ',
        'চুদবাজ', 'চুদগু', 'মাগীর', 'চুদপো', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা',
        'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ',
        'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ',
        'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ',
        'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর',
        'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর',
        'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ',
        'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু',
        'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ',
        'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা',
        'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ',
        'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ',
        'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ',
        'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর',
        'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর',
        'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ',
        'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু',
        'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ',
        'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা',
        'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ',
        'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ',
        'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ',
        'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর',
        'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর',
        'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ',
        'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু',
        'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ',
        'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা',
        'মাগীর', 'চুদবাজ', 'ভোদাচোদ', 'চুদচুদ', 'চুদগু', 'চুদবাজ', 'গুন্ডাচোদ', 'চুদখোর', 'পোলা', 'মাগীর', 'চুদবাজ', 'আব্বা', 'খানকীর',
        'জারজ', '    ',

        // Obfuscated Bangla/English hybrids
        'chod','chud','chuda','chodar','chodon','chudna','chudaa','chudiyo','chodaa','chodina', 'abba',
        'madarchod','madarchut','madarchudi','madarchuda','madarchod','madarchud',
        'gandu','g@ndu','gaandu','gaand','bhodachoda','bhoda','bhudi','bhodir','voda','bhodachudi',
        'raand','rand','raandi','randee','lau','l0ra','lorar','lund','l0nd','lnd','bokachoda','bokachudi',
        'pagolchoda','p@tki','pootki','putki','p00tki','p*utki','ch@ud','ch0d','ch0d0','ch0di','c0d',
        'ch0da','chod@','chud@','madarch0d','m@darchut','bh0d@','ra@nd','l@u','ch0d@r','chud@r',
        'putk1','p0tki','p0tkii','putk1i','putk!','ch0di','ch0da','ch0du','ch0dii','ch0diii',
        'mad@rchod','g@ndu123','bh0d@ch0d','ra@nd1','l@u2','ch0d@r2','chud@r2','putk!2','ch0di2','ch0da2',
        'ch0du2','ch0dii2','ch0diii2','madarch0d1','g@ndu2','bh0d@2','ra@nd2','l@u3','ch0d@r3','chud@r3',
        'putk!3','ch0di3','ch0da3','ch0du3','ch0dii3','ch0diii3','madarch0d4','g@ndu3','bh0d@3','ra@nd3',
        'l@u4','ch0d@r4','chud@r4','putk!4','ch0di4','ch0da4','ch0du4','ch0dii4','ch0diii4','madarch0d5',
        'g@ndu4','bh0d@4','ra@nd4','l@u5','ch0d@r5','chud@r5','putk!5','ch0di5','ch0da5','ch0du5',
        'ch0dii5','ch0diii5','m@darch0d6','g@ndu5','bh0d@5','ra@nd5','l@u6','ch0d@r6','chud@r6','putk!6',
        'ch0di6','ch0da6','ch0du6','ch0dii6','ch0diii6','madarch0d7','g@ndu6','bh0d@6','ra@nd6','l@u7',
        'ch0d@r7','chud@r7','putk!7','ch0di7','ch0da7','ch0du7','ch0dii7','ch0diii7','madarch0d8','g@ndu7',
        'bh0d@7','ra@nd7','l@u8','ch0d@r8','chud@r8','putk!8','ch0di8','ch0da8','ch0du8','ch0dii8','ch0diii8',
        'madarch0d9','g@ndu8','bh0d@8','ra@nd8','l@u9','ch0d@r9','chud@r9','putk!9','ch0di9','ch0da9',
        'ch0du9','ch0dii9','ch0diii9','madarch0d0','g@ndu9','bh0d@9','ra@nd9','l@u0','ch0d@r0','chud@r0',
        'putk!0','ch0di0','ch0da0','ch0du0','ch0dii0','ch0diii0','mad@rch0d','g@ndu0','bh0d@0','ra@nd0',
        'l@u01','ch0d@r01','chud@r01','putk!01','ch0di01','ch0da01','ch0du01','ch0dii01','ch0diii01','madarch0d01',

        // Leetspeak / obfuscations for filtering
        'f.u.c.k','s.h.i.t','c.u.n.t','p.u.s.s.y',
        'f u c k','s h i t','c u n t','p u s s y',

        // Extra slang/insults
        'dogfucker','goatfucker','pigfucker',
        'dumbass','smartass','jackass',
        'sonofabitch','sob','mf','lmfao','lmao','wtf',

        // Spam/sexual spam keywords
        'camgirl','nudes','nude','sendnudes','onlyfans','ofans',
        'xxxvideos','xvideos','xnxx','pornhub','redtube','brazzers',

        // Numbers often used to mask obscene words
        's3x','5ex','fux','fuxk','fukk','fuq','fuqq',

        // Catch-all obscene phrases
        'go to hell','die bitch','kill yourself','kms','kys'
    ];

    private function validate_address($address) {
        $stringLength = mb_strlen(trim($address), 'UTF-8');

        if($stringLength  <= 10) {
            return false;
        }
        
        if ($this->containsBlacklisted($address)) {
            return false;
        }

        return true;
    }


    private function validate_name($name) {
        $stringLength = mb_strlen(trim($name), 'UTF-8');

        if($stringLength < 3 || $stringLength > 25) {
            return false;
        }
        
        if ($this->containsBlacklisted($name)) {
            return false;
        }

        return true;
    }


    private function containsBlacklisted(string $text): bool {
        $norm = mb_strtolower($text, 'UTF-8');

        foreach ($this->blacklist as $bad) {
            $badNorm = mb_strtolower($bad, 'UTF-8');

            // Use word boundaries \b to match exact words only
            if (preg_match('/\b' . preg_quote($badNorm, '/') . '\b/u', $norm)) {
                return true; // offensive word found
            }
        }

        return false; // no offensive word found
    }

    public function save_device_token_to_order($order, $data) {
        if (!is_wel_license_valid()) {
            return;
        }
        
        if (isset($_POST['wel_device_token'])) {
            $device_token = sanitize_text_field(wp_unslash($_POST['wel_device_token']));
            if (!empty($device_token)) {
                // Save to order meta
                $order->update_meta_data('_wel_device_token', $device_token);
            }
        }
    }
}