<?php


class BwController extends RoxControllerBase
{
    function index($args = false)
    {
        $request = $args->request;
        $file = SCRIPT_BASE.'htdocs/bw/'.implode('/', array_slice($request, 1));
        if (is_file($file)) {
            $page = new BwPage;
            $page->file = $file;
            return $page;
        } else {
            $redir_map = array("whoisonline.php" => "online",
                               "publicfaq.php" => "about/faq",
                               "faq.php" => "about/faq",
                               "impressum.php" => "impressum",
                               "findpeople.php" => "searchmembers",
                               "findpeople_ajax.php" => "searchmembers",
                               "disclaimer.php" => "privacy",
                               "cities.php" => "home",
                               "aboutus.php" => "about",
                               "main.php" => "main",
                               );
            
            if (isset($redir_map[$request[1]])) {
                $this->redirect($redir_map[$request[1]]);
                PPHP::PExit();
            }
            else {
                echo "file '$file' not found";
                return new PageWithHTML;
            }
        }
    }
}


class BwPage extends PageWithHTML
{
    protected function printHTML()
    {
        require_once $this->file;
    }
}


?>