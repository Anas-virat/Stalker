<?php
include("config.php");
ob_start(); // Start capturing output for logging

$anas = "#EXTM3U\n";
$log = [];

if (!file_exists($DARK_SIDE . "/detail.anas")) {
    $anas .= "# ERROR: detail.anas file not found\n";
    echo $anas;
    file_put_contents("log.txt", ob_get_clean());
    exit;
}

$detail = json_decode(file_get_contents($DARK_SIDE . "/detail.anas"), true);
if (!$detail) {
    $anas .= "# ERROR: detail.anas is not valid JSON\n";
    echo $anas;
    file_put_contents("log.txt", ob_get_clean());
    exit;
}

$portal = rtrim($detail["portal_url"], "/");
$mac = $detail["mac_address"];
$serial = $detail["serial_number"];
$dev1 = $detail["device_id_1"];
$dev2 = $detail["device_id_2"];

$common_headers = [
    "User-Agent: Mozilla/5.0",
    "Referer: $portal/c/",
    "X-User-Agent: Model: MAG254; Link: WiFi",
    "Cookie: mac=$mac; stb_lang=en;",
    "X-Device-Id: $dev1",
    "X-Serial-Number: $serial"
];

// 1. Handshake
$ch = curl_init("$portal/server/load.php?type=stb&action=handshake&token=&JsHttpRequest=1-xml");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $common_headers
]);
$handshake_response = curl_exec($ch);
curl_close($ch);

$token = json_decode($handshake_response, true)['js']['token'] ?? null;
if (!$token) {
    $anas .= "# ERROR: Failed to get token\n";
    echo $anas;
    file_put_contents("log.txt", ob_get_clean());
    exit;
}

// 2. Auth Profile (required for most portals)
$headers_auth = array_merge($common_headers, ["Authorization: Bearer $token"]);
$ch = curl_init("$portal/server/load.php?type=stb&action=get_profile&token=$token&JsHttpRequest=1-xml");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers_auth
]);
curl_exec($ch);
curl_close($ch);

// 3. Get channel list
$ch = curl_init("$portal/server/load.php?type=itv&action=get_all_channels&token=$token&JsHttpRequest=1-xml");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers_auth
]);
$channel_response = curl_exec($ch);
curl_close($ch);

$channels = json_decode($channel_response, true)['js']['data'] ?? [];

if (!$channels) {
    $anas .= "# ERROR: No channels found or portal rejected request\n";
    echo $anas;
    file_put_contents("log.txt", ob_get_clean());
    exit;
}

// 4. Create link for each channel
foreach ($channels as $ch) {
    $name = $ch['name'] ?? 'Unknown';
    $cmd = $ch['cmd'] ?? '';
    $cmd_clean = urlencode(trim(str_replace(['ffmpeg ', 'auto '], '', $cmd)));

    $link_url = "$portal/server/load.php?type=itv&action=create_link&cmd=$cmd_clean&token=$token&JsHttpRequest=1-xml";
    $ch_link = curl_init($link_url);
    curl_setopt_array($ch_link, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers_auth
    ]);
    $link_resp = curl_exec($ch_link);
    curl_close($ch_link);

    $url = json_decode($link_resp, true)['js']['cmd'] ?? '';
    $url = str_replace('mono.jitendraunatti', 'mono.anas', $url);

    if ($url) {
        $anas .= "#EXTINF:-1 group-title=\"ANAS\",$name\n";
        $anas .= "$url\n";
    }
}

echo $anas;
file_put_contents("log.txt", ob_get_clean()); // Save debug log
