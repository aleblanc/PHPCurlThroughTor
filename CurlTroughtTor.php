<?php

$port = rand(9000, 19000);
$isMac = false;
$isLinux = true;
$tmpPath = dirname(__FILE__) . '/tmp/';

@mkdir($tmpPath);

if ($isMac) {
    echo "Download\n";
    if (!is_file($tmpPath . 'TorBrowser-10.5.6-osx64_en-US.dmg')) {
        file_put_contents($tmpPath . 'TorBrowser-10.5.6-osx64_en-US.dmg', file_get_contents('https://www.torproject.org/dist/torbrowser/10.5.6/TorBrowser-10.5.6-osx64_en-US.dmg'));
    }

    echo "Download OK\n";

    if (!is_file($tmpPath . 'Tor Browser.app/Contents/MacOS/Tor/tor.real')) {
        exec('/usr/local/bin/7z x -y -o' . $tmpPath . ' ' . $tmpPath . 'TorBrowser-10.5.6-osx64_en-US.dmg');
    }

    echo "Extract OK\n";

    chmod($tmpPath . 'Tor Browser.app/Contents/MacOS/Tor/tor.real', 0777);

    echo "Chmod OK\n";
}

if ($isLinux) {
    echo "Download\n";
    if (!is_file($tmpPath . '/tor-browser-linux64-10.5.6_en-US.tar.xz')) {
        file_put_contents($tmpPath . '/tor-browser-linux64-10.5.6_en-US.tar.xz', file_get_contents('https://dist.torproject.org/torbrowser/10.5.6/tor-browser-linux64-10.5.6_en-US.tar.xz'));
    }

    echo "Download OK\n";

    echo 'Extract';
    if (!is_file($tmpPath . '/tor-browser_en-US/Browser/TorBrowser/Tor/tor')) {
        exec('/usr/bin/tar -xf ' . $tmpPath . '/tor-browser-linux64-10.5.6_en-US.tar.xz --directory ' . $tmpPath);
    }
    echo 'Extract OK';

    chmod($tmpPath . '/tor-browser_en-US/Browser/TorBrowser/Tor/tor', 0777);

    echo "Chmod OK\n";
}
echo "CURL https://ipinfo.io/ip\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, 'https://ipinfo.io/ip');
$curl_scraped_page = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

echo "IP actuel\n";
var_dump($curl_scraped_page);

echo "Launch tor with nohup\n";

if ($isMac) {
    file_put_contents($tmpPath . 'tor-nohup.sh', 'LD_LIBRARY_PATH=' . $tmpPath . "Tor\ Browser.app/Contents/Resources/TorBrowser/Tor/ " . $tmpPath . "Tor\ Browser.app/Contents/MacOS/Tor/tor.real --defaults-torrc " . $tmpPath . "Tor\ Browser.app/Contents/Resources/TorBrowser/Tor/torrc-defaults --DataDirectory " . $tmpPath . "Tor\ Browser.app/Contents/Resources/TorBrowser/Tor --GeoIPFile " . $tmpPath . "Tor\ Browser.app/Contents/Resources/TorBrowser/Tor/geoip --GeoIPv6File " . $tmpPath . "Tor\ Browser.app/Contents/Resources/TorBrowser/Tor/geoip6 --SocksPort " . $port);
}
if ($isLinux) {
    file_put_contents($tmpPath . 'tor-nohup.sh', 'LD_LIBRARY_PATH=' . $tmpPath . '/tor-browser_en-US/Browser/TorBrowser/Tor/ ' . $tmpPath . '/tor-browser_en-US/Browser/TorBrowser/Tor/tor --defaults-torrc ' . $tmpPath . '/tor-browser_en-US/Browser/TorBrowser/Data/Tor/torrc-defaults -f ' . $tmpPath . '/tor-browser_en-US/Browser/TorBrowser/Data/Tor/torrc --DataDirectory ' . $tmpPath . '/tor-browser_en-US/Browser/TorBrowser/Data/Tor --GeoIPFile ' . $tmpPath . '/tor-browser_en-US/Browser/TorBrowser/Data/Tor/geoip --GeoIPv6File ' . $tmpPath . '/tor-browser_en-US/Browser/TorBrowser/Data/Tor/geoip6 --SocksPort ' . $port);
}
chmod($tmpPath . 'tor-nohup.sh', 0777);
exec('/usr/bin/nohup ' . $tmpPath . 'tor-nohup.sh  >/dev/null 2>&1 &');
sleep(3);

echo "CURL https://ipinfo.io/ip\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:' . $port);
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, 'https://ipinfo.io/ip');
$curl_scraped_page = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

echo "IP through Tor\n";
var_dump($curl_scraped_page);
echo "\n";

sleep(1);

if ($isMac) {
    echo 'KILL OSX port' . $port;
    exec("/bin/ps aux | /usr/bin/grep 'SocksPort " . $port . "' | /usr/bin/awk '{print $2}' | /usr/bin/xargs /bin/kill -9");
}
if ($isLinux) {
    echo 'KILL LINUX port' . $port;
    exec("/usr/bin/ps aux | /usr/bin/grep 'SocksPort " . $port . "' | /usr/bin/awk '{print $2}' | /usr/bin/xargs /usr/bin/kill -9");
}
unlink($tmpPath . 'tor-nohup.sh');

echo 'END';
