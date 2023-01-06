<?php
class filelist implements ISite, ISearch, IVerify {
    /*
     * filelist()
     * @param {string} $url
     * @param {string} $username
     * @param {string} $password
     * @param {string} $meta
     */
    const SITE = "https://filelist.io/api.php";
    private $url;
    private $username;
    private $password;
    public function __construct($url = null, $username = null, $password = null, $meta = null) {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
    }

    /*
     * Verify()
     * @return {boolean}
     */
    public function Verify() {
        return true;
    }

   
   /*
     * Search()
     * @param {string} $keyword
     * @param {integer} $limit
     * @param {string} $category
     * @return {array} SearchLink array
     */
    public function Search($keyword, $limit, $category) {
        $page = 1;
        $ajax = new Ajax();
        $found = array();
        
        $success = function ($request, $header, $cookie, $body, $effective_url) use(&$page, &$found, &$limit) {

            if (!$body) {
                return ($page = false);
            }
           

            $json_body = json_decode($body);
            $len = sizeof($json_body);

            for ($i = 0 ; $i < $len ; ++$i) {
                $tlink = new SearchLink;
                
                $tlink->src           = "filelist.io";
                $tlink->link          = "https://www.filelist.io";
                $tlink->name          = $json_body[$i]->name;
                $tlink->size          = $json_body[$i]->size;
                
                
                $time = datetime::createfromformat('Y-m-d H:i:s',$json_body[$i]->upload_date);   //  "upload_date": "2023-01-05 23:09:41",
                $tlink->time = $time;
               
                $tlink->seeds         = $json_body[$i]->seeders;
                $tlink->peers         = $json_body[$i]->leechers;
                $tlink->category      = $json_body[$i]->category;
                $tlink->enclosure_url = $json_body[$i]->download_link;
                
                $found []= $tlink;
                
                if (count($found) >= $limit) {
                    return ($page = false);
                }
            }
        };
        
        

        $request = array(
            "url"       => filelist::SITE,
            "body"      => true,
            "params"    => array (
                "username" => $this->username,
                "passkey"  => $this->password,
                "action"   => "search-torrents",
                "type"     => "name",
                "query"    => "$keyword"
             )
        );
        if (!$ajax->request($request, $success)) {
            echo "error";
        };
        
        
        return $found;
    }
}
?>
