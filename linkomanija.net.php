<?php
class linkomanija implements ISite, ISearch {

    const SITE       = "https://www.linkomanija.net";
    const LOGIN_URL  = "https://www.linkomanija.net/takelogin.php";
    const SEARCH_URL = "https://www.linkomanija.net/browse.php";
    const PER_PAGE   = 35;

    private $url;
    private $username;
    private $password;

    public function __construct($url = null, $username = null, $password = null, $meta = null) {
        $this->url      = $url;
        $this->username = $username;
        $this->password = $password;
    }

    static function UnitSize($unit) {
        switch (strtoupper(trim($unit))) {
        case "B":   return 1;
        case "KB":  return 1000;
        case "KIB": return 1024;
        case "MB":  return 1000000;
        case "MIB": return 1048576;
        case "GB":  return 1000000000;
        case "GIB": return 1073741824;
        case "TB":  return 1000000000000;
        case "TIB": return 1099511627776;
        default:    return 1;
        }
    }

    public function Search($keyword, $limit, $category) {
        $page = 0;
        $ajax = new Ajax();
        $found = array();

        // Step 1: GET login page to obtain PHPSESSID cookie
        $ajax->Request(
            array("url" => "https://www.linkomanija.net/login.php"),
            function ($header, $cookie, $cookie2, $body, $url) {
            }
        );

        // Step 2: POST credentials - Ajax follows 302 to index.php on success
        $loggedIn = false;
        $ajax->Request(
            array(
                "url"    => linkomanija::LOGIN_URL,
                "post"   => true,
                "params" => array(
                    "username" => "[YOUR USER NAME HERE]",
                    "password" => "[YOUR PASSWORD HERE]",
                ),
            ),
            function ($header, $cookie, $cookie2, $body, $url) use (&$loggedIn) {
                if (stripos($body, "logout")     !== false
                 || stripos($body, "Atsijungti") !== false) {
                    $loggedIn = true;
                }
            }
        );

        if (!$loggedIn) {
            return $found;
        }

        $success = function ($header, $cookie, $cookie2, $body, $url) use (&$page, &$found, &$limit) {
            preg_match_all(
                "`" .
                "<td[^>]*>\\s*<a[^>]+>\\s*<img[^>]+alt=\"(?P<category>[^\"]+)\"[^>]*/>" .
                ".*?" .
                "href=\"details\\?(?P<id>\\d+)[^\"]*\"[^>]*>\\s*<b>(?P<n>[^<]+)</b>" .
                ".*?" .
                "href=\"(?P<torrent>download\\.php\\?id=\\d+[^\"]+\\.torrent)\"" .
                ".*?" .
                "<td[^>]*>\\s*(?P<size>[\\d.,]+)<br\\s*/?>\\s*(?P<unit>[A-Za-z]+)\\s*</td>" .
                ".*?" .
                "<span class=\"slr\\d+\">(?P<seeds>\\d+)</span>" .
                ".*?" .
                "<td[^>]*>\\s*(?:<b>\\s*<a[^>]*>)?(?P<leechers>\\d+)" .
                "`si",
                $body,
                $result
            );

            if (!$result || ($len = count($result["id"])) == 0) {
                $page = false;
                return;
            }

            for ($i = 0; $i < $len; ++$i) {
                $tlink = new SearchLink;
                $id   = trim($result["id"][$i]);
                $size = str_replace(",", ".", trim($result["size"][$i]));
                $tlink->src           = "linkomanija.net";
                $tlink->link          = linkomanija::SITE . "/details.php?id=" . $id;
                $tlink->name          = html_entity_decode(strip_tags($result["n"][$i]), ENT_QUOTES, "UTF-8");
                $tlink->size          = ($size + 0) * linkomanija::UnitSize($result["unit"][$i]);
                $tlink->time          = new DateTime();
                $tlink->seeds         = $result["seeds"][$i] + 0;
                $tlink->peers         = $result["leechers"][$i] + 0;
                $tlink->category      = strtolower($result["category"][$i]);
                $tlink->enclosure_url = linkomanija::SITE . "/" . str_replace("&amp;", "&", $result["torrent"][$i]) . "&passkey=[PUT YOUR PASS KEY HERE , YOU CAN GET IT FROM GENERATED RRS LINK]";
                $found []= $tlink;
                if (count($found) >= $limit) {
                    $page = false;
                    return;
                }
            }

            if ($len < linkomanija::PER_PAGE) {
                $page = false;
            } else {
                ++$page;
            }
        };

        while ($page !== false && count($found) < $limit) {
            if (!$ajax->Request(
                array(
                    "url"    => linkomanija::SEARCH_URL,
                    "post"   => false,
                    "params" => array(
                        "search"   => $keyword,
                        "submit.x" => "21",
                        "submit.y" => "8",
                        "page"     => $page,
                    ),
                ),
                $success
            )) {
                break;
            }
        }

        return $found;
    }
}
?>
