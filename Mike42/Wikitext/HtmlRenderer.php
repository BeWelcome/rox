<?php

/**
 * Methods from this class are called as different types of markup are encountered,
 * and are expected to provide supporting functions like template substitutions,
 * link destinations, and other installation-specific oddities
 */

namespace Mike42\Wikitext;

/**
 * Html Renderer
 * Design pattern : Template method
 */
abstract class HtmlRenderer
{

    private $interwiki;

    public function __construct(?InterwikiRepository $repo = null)
    {
        if (is_null($repo)) {
            $repo = new NullInterwikiRepository();
        }

        $this->interwiki = $repo;
    }

    /**
     * Process an element which has arguments. Links, lists and templates fall under this category
     *
     * @param string $elementName
     * @param string $arg
     */
    public function renderWithArgs($elementName, $arg)
    {
        $fn = array($this, 'render' . ucfirst($elementName));

        if (is_callable($fn)) {
            /* If a function is defined to handle this, use it */
            return call_user_func_array($fn, array($arg));
        } else {
            return $arg[0];
        }
    }

    /**
     * Encapsulate inline elements
     *
     * @param string $text parsed text contained within this element
     * @param string $elementName the name of the element
     * @return string Correct markup for this element
     */
    public function encapsulateElement($elementName, $text)
    {
        $fn = array($this, 'encapsulate' . ucfirst($elementName));

        if (is_callable($fn)) {
            /* If a function is defined to encapsulate this, use it */
            return call_user_func_array($fn, array($text));
        } else {
            return $text;
        }
    }

    public function renderLineBlock($elementName, $list)
    {
        $fn = array($this, 'render' . ucfirst($elementName));

        if (is_callable($fn)) {
            /* If a function is defined to encapsulate this, use it */
            return call_user_func_array($fn, array($elementName, $list));
        } else {
            return $elementName;
        }
    }

    public function renderOl($token, $list)
    {
        return $this->renderList($token, $list);
    }

    public function renderUl($token, $list)
    {
        return $this->renderList($token, $list);
    }

    public function renderDl($token, $list)
    {
        return $this->renderList($token, $list);
    }

    public function renderH($token, $headings)
    {
        $outp = "";
        foreach ($headings as $heading) {
            $tag = "h" . $heading['depth'];
            $outp .= "<$tag>" . $heading['item'] . "</$tag>\n";
        }
        return $outp;
    }

    public function renderPre($token, $lines)
    {
        $outpline = array();
        foreach ($lines as $line) {
            $outpline[] = $line['item'];
        }

        return "<pre>" . implode("\n", $outpline) . "</pre>";
    }

    /**
     * Render list and any sub-lists recursively
     *
     * @param string $token The type of list (expect ul, ol, dl)
     * @param mixed $list The hierachy representing this list
     * @return string HTML markup for the list
     */
    public function renderList($token, $list, $expectedDepth = 1): string
    {
        $outp = '';
        $subtoken = "li";
        $outp .= "<$token>\n";

        foreach ($list as $item) {
            if ($token == 'dl') {
                $subtoken = $item['char'] == ";" ? "dt" : "dd";
            }
            $outp .= "<$subtoken>";
            $diff = $item['depth'] - $expectedDepth;
            /* Some items are undented unusually far ..  */
            if ($diff > 0) {
                $outp .= str_repeat("<$token><$subtoken>", $diff);
            }
            /* Caption of this item */
            $outp .= $item['item'];
            if (count($item['child']) > 0) {
                /* Add children if applicable */
                $outp .= $this->renderList($token, $item['child'], $item['depth'] + 1);
            }
            if ($diff > 0) {
                /* Close above extra encapsulation if applicable */
                $outp .= str_repeat("</$subtoken></$token>", $diff);
            }
            $outp .= "</$subtoken>\n";
        }
        $outp .= "</$token>\n";
        return $outp;
    }

    /**
     * Default rendering of [[link]] or [[link|foo]]
     *
     * @param string $destination page name we are linking to
     * @param string $caption Caption of this link (can inlude parsed wikitext)
     * @return string HTML markup for the link
     */
    public function renderLinkInternal($arg): string
    {
        /* Figure out properties based on arguments */
        if (isset($arg[0])) {
            $destination = $arg[0];
        }
        if (isset($arg[1])) {
            $caption = $arg[1];
        }

        /* Compensate for missing values */
        if (isset($destination) && !isset($caption)) {
            $caption = $destination; // Fill in caption = destination as default
        }
        if (!isset($destination)) {
            if (isset($caption)) {
                $destination = ""; // Empty link
            } else {
                return ""; // Empty link to nowhere (so skip it)
            }
        }

        $info = array('url' => $destination, /* You should override getInternalLinkInfo() to set this better according to your application. */
            'title' => $destination, /* Eg [[foo:bar]] links to "foo:bar". */
            'namespace' => '', /* Eg [[foo:bar]] is in namespace 'foo' */
            'target' => $destination, /* Eg [[foo:bar]] has the target "bar" within the namespace. */
            'namespaceignore' => false, /* eg [[:File:foo.png]], link to the image don't include it */
            'caption' => $caption, /* The link caption eg [[foo:bar|baz]] has the caption 'baz' */
            'exists' => true, /* Causes class="new" for making red-links */
            'external' => false);

        /* Attempt to deduce namespaces */
        if ($destination == '') {
            $split = false;
        } else {
            $split = strpos($destination, ":", 1);
        }

        if (!$split === false) {
            /* We have namespace */
            if (substr($destination, 0, 1) == ":") { /* Eg [[:category:foo]] */
                $info['namespaceignore'] = true;
                $info['namespace'] = strtolower(substr($destination, 1, $split - 1));
            } else {
                $info['namespace'] = strtolower(substr($destination, 0, $split));
            }

            $split++;
            $info['target'] = substr($destination, $split, strlen($destination) - $split);

            if ($info['namespace'] == 'file') {
                /* Render an image instead of a link if requested */
                $info['url'] = $info['target'];
                $info['caption'] = '';
                return $this->renderFile($info, $arg);
            } else if ($this->interwiki->hasNamespace($info['namespace'])) {
                /* We have a known namespace */
                $site = $this->interwiki->getTargetUrl($info['namespace']);
                $info['url'] = str_replace("$1", $info['target'], $site);
                $info['external'] = true;
            }
        }

        /* Allow the local app to contribute to link properties */
        $info = $this->getInternalLinkInfo($info);

        return "<a href=\"" . htmlspecialchars($info['url']) . "\" title=\"" . htmlspecialchars($info['title']) . "\"" . (!$info['exists'] ? " class=\"new\"" : '') . ">" . $info['caption'] . "</a>";
    }

    public function renderFile($info, $arg)
    {
        $info['thumb'] = $info['url']; /* Default no no server-side thumbs */
        $info['class'] = '';
        $info['page'] = '';
        $info['caption'] = '';

        $target = $info['target'];
        $pos = strrpos($target, ".");
        if ($pos === false) {
            $ext = '';
        } else {
            $pos++;
            $ext = substr($target, $pos, strlen($target) - $pos);
        }

        switch ($ext) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'webp':
                /* Image flags parsed. From: http://www.mediawiki.org/wiki/Help:Images */

                /* Named arguments */
                if (isset($arg['link'])) { // |link=
                    $info['url'] = $arg['link'];
                    $info['link'] = $arg['link'];
                    unset($arg['link']);
                }
                if (isset($arg['class'])) { // |class=
                    $info['class'] = $arg['class'];
                    unset($arg['class']);
                }
                if (isset($arg['alt'])) { // |alt=
                    $info['title'] = $arg['alt'];
                    unset($arg['alt']);
                }
                if (isset($arg['page'])) { // |alt=
                    $info['page'] = $arg['page'];
                    unset($arg['page']);
                }

                foreach ($arg as $key => $item) {
                    /* Figure out unnamed arguments */
                    if (is_numeric($key)) { /* Any unsupported named arguments will be ignored */
                        if (substr($item, 0, -2) == 'px') {
                            /* Size */
                            // TODO
                        } else {
                            /* Load recognised switches */
                            switch ($item) {
                                case "frameless":
                                    $info['frameless'] = true;
                                    break;
                                case "border":
                                    $info['border'] = true;
                                    break;
                                case "frame":
                                    $info['frame'] = true;
                                    break;
                                case "thumb":
                                    $info['thumbnail'] = true;
                                    break;
                                case "thumbnail":
                                    $info['thumbnail'] = true;
                                    break;
                                case "left":
                                    $info['left'] = true;
                                    break;
                                case "right":
                                    $info['right'] = true;
                                    break;
                                case "center":
                                    $info['center'] = true;
                                    break;
                                case "none":
                                    $info['none'] = true;
                                    break;
                                default:
                                    $info['caption'] = $item;
                            }
                        }
                    }
                }

                $info = $this->getImageInfo($info);

                if ($info['namespaceignore'] || !$info['exists']) {
                    /* Only link to the image, do not display it */
                    if ($info['caption'] == '') {
                        $info['caption'] = $info['target'];
                    }
                    /* Construct link */
                    return "<a href=\"" . htmlspecialchars($info['url']) . "\" title=\"" . htmlspecialchars($info['title']) . "\"" . (!$info['exists'] ? " class=\"new\"" : '') . ">" . $info['caption'] . "</a>";
                } else {
                    $dend = $dstart = "";
                    if (isset($info['thumbnail']) || isset($info['frame'])) {
                        if (isset($info['right'])) {
                            $align = " tright";
                        } elseif (isset($info['left'])) {
                            $align = " tleft";
                        } else {
                            $align = "";
                        }
                        $dstart = "<div class=\"thumb$align {$info['class']}\">";
                        if ($info['caption'] != '') {
                            $dend .= "<div class=\"thumbcaption\">" . htmlspecialchars($info['caption']) . "</div>";
                        }
                        $dend .= "</div>";
                    }
                    /* Construct link */
                    return "$dstart<a href=\"" . htmlspecialchars($info['url']) . "\"><img src=\"" . htmlspecialchars($info['thumb']) . "\" alt=\"" . htmlspecialchars($info['title']) . "\" /></a>$dend";
                }

                break;
            default:
                /* Something unsupported */
                return "<b>(unsupported media file)</b>";
        }
    }

    /**
     * Method to override when providing extra info about an image (basically external URL and thumbnail path)
     */
    abstract public function getImageInfo($info): array;

    /**
     * Method to override when providing extra info about a link
     */
    abstract public function getInternalLinkInfo($info): array;

    /**
     * Default rendering of [http://... link] or [http://foo]
     *
     * @param string $destination page name we are linking to
     * @param string $caption Caption of this link (can inlude parsed wikitext)
     * @return string HTML markup for the link
     */
    public function renderLinkExternal($arg)
    {
        $caption = $destination = $arg[0];
        if (isset($arg[1])) {
            $caption = $arg[1];
        }
        return "<a href=\"" . htmlspecialchars($destination) . "\" class=\"external\">" . $caption . "</a>";
    }

    /**
     * Default encapsulation for '''bold'''
     *
     * @param string $text Text to make bold
     * @return string
     */
    public function encapsulateBold($text)
    {
        return "<b>" . $text . "</b>";
    }

    /**
     * Default encapsulation for ''italic''
     *
     * @param string $text Text to make bold
     * @return string
     */
    public function encapsulateItalic($text)
    {
        return "<i>" . $text . "</i>";
    }

    public function encapsulateParagraph($text)
    {
        return "<p>" . $text . "</p>\n";
    }

    /**
     * Generate HTML for a table
     */
    public function renderTable($table)
    {
        if ($table['properties'] == '') {
            $outp = "<table>\n";
        } else {
            $outp = "<table " . trim($table['properties']) . ">\n";
        }

        foreach ($table['row'] as $row) {
            $outp .= $this->renderRow($row);
        }

        return $outp . "</table>\n";
    }

    /**
     * Render a single row of a table
     */
    public function renderRow($row)
    {
        /* Show row with or without attributes */
        if ($row['properties'] == '') {
            $outp = "<tr>\n";
        } else {
            $outp = "<tr " . trim($row['properties']) . ">\n";
        }

        foreach ($row['col'] as $col) {
            /* Show column with or without attributes */
            if (count($col['arg']) != 0) {
                $outp .= "<" . $col['token'] . " " . trim($col['arg'][0]) . ">";
            } else {
                $outp .= "<" . $col['token'] . ">";
            }
            $outp .= $col['content'] . "</" . $col['token'] . ">\n";
        }

        return $outp . "</tr>\n";
    }

    /**
     * Function to over-ride if you want to provide a mechanism for getting templates
     *
     * @param string $template
     */
    public function getTemplateMarkup($template)
    {
        return "[[$template]]";
    }

}
