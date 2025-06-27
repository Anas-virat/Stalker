<?php
include("config.php");

if (file_exists($DARK_SIDE . "/detail.anas")) {
    header('Content-Type: application/json');

    $ROCKET_RACCOON = date('l jS \of F Y h:i:s A');
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $THANOS = parse_url($_SERVER["REQUEST_URI"]);
    $THANOS = str_replace("playlist.php", "Anas.php",  $THANOS["path"]);

    // Rebranded header info
    $anas = "#EXTM3U\n#DEVELOPED_BY_ANAS\n#AUTHOR:-ANAS\n#DATE:-" . $ROCKET_RACCOON . "\n#【A】【N】【A】【S】\n" . PHP_EOL;

    $ULTRON = @json_decode(json_fetcher(), true);

    if (isset($ULTRON[0]["id"]) && $ULTRON[0]["logo"] && $ULTRON[0]["genre"] && validation()) {
        foreach ($ULTRON as $VISION) {
            $anas .= '#EXTINF:-1 tvg-id="' . $VISION["id"] . '" tvg-logo="' . $VISION["logo"] . '" group-title="' . $VISION["genre"] . '",' . $VISION['Name'] . "\r\n";
            
            // Optional replacement: if original URLs still include old name
            $fixed_url = str_replace("mono.jitendraunatti", "mono.anas", $VISION["playback_url"]);

            $anas .= "{$protocol}{$host}$THANOS?id=" . $fixed_url . "\r\n" . PHP_EOL;
        }

        file_put_contents($LIGHT_SIDE . "/playlist.m3u", $anas);
    } else {
        json_fetcher();

        $anas .= '#EXTINF:-1 tvg-id="DOCTOR_STRANGE" tvg-logo="' . htmlspecialchars($MJ["Meta_data"]["channel_img"]) . '" group-title="anas",anas Live' . "\r\n";
        $anas .= $MJ["Meta_data"]["channel_video"] . "\n" . PHP_EOL;
    }

    echo $anas;

    $DANAV = explode(".", $WANDA["HOST"]);
    file_put_contents($LIGHT_SIDE . "/" . $DANAV[1] . ".m3u", $anas);
} else {
    header("Location: login.php");
    exit();
}
