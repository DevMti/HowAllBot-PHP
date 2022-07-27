<?php

    define('BOT_TOKEN', 'Your Ticket');
    define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

    function exec_curl_request($handle) {
        $response = curl_exec($handle);
    
        if ($response === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl returned error $errno: $error\n");
            curl_close($handle);
            return false;
        }
    
        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);
    
        if ($http_code >= 500) {
            // do not wat to DDOS server if something goes wrong
            sleep(10);
            return false;
        } else if ($http_code != 200) {
            $response = json_decode($response, true);
            error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
            if ($http_code == 401) {
                throw new Exception('Invalid access token provided');
            }
            return false;
        } else {
            $response = json_decode($response, true);
            if (isset($response['description'])) {
                error_log("Request was successfull: {$response['description']}\n");
            }
            $response = $response['result'];
        }
    
        return $response;
    }

    function apiRequest($method, $parameters) {

        foreach ($parameters as $key => &$val) {
            // encoding to JSON array parameters, for example reply_markup
            if (!is_numeric($val) && !is_string($val)) {
                $val = json_encode($val);
            }
        }
        $url = API_URL.$method.'?'.http_build_query($parameters);

        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);

        return exec_curl_request($handle);
    }

    function apiRequestJson($method, $parameters) {
        $parameters["method"] = $method;
    
        $handle = curl_init(API_URL);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    
        return exec_curl_request($handle);
    }

    function processMessage($message) {
        // Include database information
        include 'db.php';

        // Process incoming message
        $type = $message['chat']['type'];
        $chat_id = $message['chat']['id'];

        if (isset($message['text'])) {
            // Incoming text message
            $text = $message['text'];

            // Add user in db
            // Error X: sorry i can't publish my table status

            // Add 1 to Uses Count
            // Error X
            

            // Start message (gif)
            if (strpos($text, "/start started") !== false or $text == "/start") {
                apiRequestJson(
                    'sendChatAction',
                    array(
                        'action' => 'upload_photo',
                        'chat_id' => $chat_id
                    )
                );
                apiRequestJson(
                    'sendPhoto',
                    array(
                        'chat_id' => $chat_id,
                        'caption' => "👋 Hi!\n\n💡 To use this bot, simply type \"@HowAllBot\" into your text box and click one of the results or click the button attached to this message (Share any thing).\n\n💬 To notice about new things in this bot please join in our Channel:\n📣 @OnTopTM",
                        'photo' => "https://on-top.ml/assets/start.jpg",
                        'parse_mode' => "HTML",
                        'disable_web_page_preview' => true,
                        'reply_markup' => array(
                            'inline_keyboard' => array(
                                array(
                                    array(
                                        'text' => "🤝 Share any thing!",
                                        'switch_inline_query' => ""
                                    )    
                                )
                            )
                        )
                    )
                );
                exit;
            } 

            // Bot Ads
            if (strpos($text, "/start ads") !== false or $text == "/ads") {
                apiRequestJson(
                    'sendChatAction',
                    array(
                        'action' => 'typing',
                        'chat_id' => $chat_id
                    )
                );
                apiRequestJson(
                    'sendMessage',
                    array(
                        'chat_id' => $chat_id,
                        'text' => "💬 Prices list\n\n1️⃣ Advertising in the robot (public message) to all users: Negotiable per 1k\n\n2️⃣ Inline ads: Negotiable for every 2k views\n\n3️⃣ Ads on the channel: Negotiable\n\n4️⃣ Sponsorship: Negotiable\n\n5️⃣ Robot order: Negotiable\n\n🔴 Prices in Rials: Send me a message to get information\n\nTo order, send a message to the ID below with #ads\n** Spammers will be blocked **\n📣 @OnTopTM",
                        'reply_markup' => array(
                            'inline_keyboard' => array(
                                array(
                                    array(
                                        'text' => "👉 Order Here 👈",
                                        'url' => "https://t.me/devmti"
                                    )    
                                )
                            )
                        )
                    )
                );
                exit;
            } 

            // Bot statistics
            if (strpos($text, "/statistics") !== false){

                apiRequestJson(
                    'sendChatAction',
                    array(
                        'action' => 'typing',
                        'chat_id' => $chat_id
                    )
                );

                $Uses = "0";// Error X
                $Users = "0";// Error X

                $startTime = new DateTime('2022-02-19 00:00:00');
                $endTime = new DateTime(date("Y-m-d G:i:s"));
                $day = $endTime->diff($startTime)->days;
                
                apiRequestJson(
                    'sendMessage',
                    array(
                        'text' => "📥 Number of uses: ```" . number_format($Uses["Count"]) . "```\n👤 Number of users: ```" . number_format($Users) . "```\n\n💬 These data are from February 19, 2022 until now. (UpTime : $day"."Days)\n- Send /ads to order ads",
                        'chat_id' => $chat_id,
                        'parse_mode' => "Markdown",
                        'reply_markup' => array(
                            'inline_keyboard' => array(
                                array(
                                    array( 
                                        'url' => "https://t.me/OnTopTM",
                                        'text' => "📣 Channel"
                                    )
                                )
                            )
                        )
                    )
                );
                exit;
            }

            // Bot Join
            if (strpos($text, "/start ch") !== false){
                apiRequestJson(
                    'sendChatAction',
                    array(
                        'action' => 'typing',
                        'chat_id' => $chat_id
                    )
                );

                apiRequestJson(
                    'sendMessage',
                    array(
                        'text' => "Thanks for your support 🙏",
                        'chat_id' => $chat_id,
                        'reply_markup' => array(
                            'inline_keyboard' => array(
                                array(
                                    array( 
                                        'url' => "https://t.me/OnTopTM",
                                        'text' => "📣 Join Channel"
                                    )
                                )
                            )
                        )
                    )
                );
                exit;
            }

            // It was not in source
            exit;
        }
    }

    function inlineMessage($inline){
        // Include database information
        include 'db.php';

        // Process incoming message
        $id = $inline['id'];
        $query = $inline['query'];
        $chat_id = $inline['from']['id'];

        // Add 1 to Uses Count
        // Error X

        // Check user is in database or not
        if (/* Error X */ false) {
            // If he/she is not in database he/she must start bot 
            apiRequest(
                'answerInlineQuery',
                array(
                    'cache_time' => 0,
                    'is_personal' => true,
                    'switch_pm_text' => "💬 First, please start the bot",
                    'inline_query_id' => $id,
                    'switch_pm_parameter' => "started"
                )
            );
        }else{
            // If he/she is in database he/she can use it
            if ($query == "" || $query == " ") { 
                // Personal result
                apiRequest(
                    'answerInlineQuery',
                    array(
                        'cache_time' => 120,
                        'is_personal' => true,
                        'switch_pm_text' => "💬 Support us by subscribing to the channel",
                        'inline_query_id' => $id,
                        'switch_pm_parameter' => "ch",
                        'results' => array(
                            array(
                                'id' => 1,
                                'type' => "article",
                                'title' => "❤️‍🔥 How horny are you?",
                                'thumb_url' => "https://On-Top.ml/assets/heart-on-fire.png",
                                'description' => "Send Your Current hornyess To This Chat.",
                                'input_message_content' => array(
                                    'message_text' => "❤️‍🔥 I am " . rand(0,200) . "% horny!"
                                ),
                                'reply_markup' => array(
                                    'inline_keyboard' => array(
                                        array(
                                            array(
                                                'text' => "Share your hornyess! ❤️‍🔥",
                                                'switch_inline_query' => ""
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                );
                exit;
            }else{
                // Result for somebody else
                apiRequest(
                    'answerInlineQuery',
                    array(
                        'cache_time' => 120,
                        'is_personal' => false,
                        'inline_query_id' => $id,
                        'results' => array(
                            array(
                                'id' => 1,
                                'type' => "article",
                                'title' => "❤️‍🔥 How horny is $query?",
                                'thumb_url' => "https://On-Top.ml/assets/heart-on-fire.png",
                                'description' => "Send $query's Current hornyess To This Chat.",
                                'input_message_content' => array(
                                    'message_text' => "❤️‍🔥 $query is " . rand(0,200) . "% horny!"
                                ),
                                'reply_markup' => array(
                                    'inline_keyboard' => array(
                                        array(
                                            array(
                                                'text' => "Share your hornyess! ❤️‍🔥",
                                                'switch_inline_query' => ""
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                );
                exit;
            }

        }

        // It was'nt in source 
        exit;
    }
    
    // Decode updates
    $content = file_get_contents("php://input");
    $update = json_decode($content, true);

    if (!$update) {
        // Receive wrong update, must not happen
        exit;
    }
    if (isset($update["message"])) {
        // Recive and process message
        processMessage($update["message"]);
    }else if(isset($update["inline_query"])){
        // Recive and process inline data
        inlineMessage($update["inline_query"]);
    }