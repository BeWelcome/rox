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
            $redir_map = array(
                               "aboutus.php" => "about",
                               "cities.php" => "home",
                               "countries.php" => "countries",
                               "disclaimer.php" => "privacy",
                               "faq.php" => "about/faq",
                               "findpeople.php" => "searchmembers",
                               "findpeople_ajax.php" => "searchmembers",
                               "impressum.php" => "impressum",
                               "index.php" => "index",
                               "login.php" => "login#login-widget",
                               "main.php" => "main",
                               "missions.php" => "about/missions",
                               "newsletters.php" => "about/newsletters",
                               "publicfaq.php" => "about/faq",
                               "quicksearch.php" => "searchmembers/quicksearch",
                               "search.php" => "searchmembers",
                               "whoisonline.php" => "online",
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