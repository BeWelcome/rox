<?php


class HellouniverseStylesPage extends HellouniversePage
{
    protected function teaserHeadline() {
        echo 'Standard Content Elements';
    }
    
    protected function leftSidebar()
    {
    ?>
        <h2>About this example </h2>

        <p>In the main column you'll find all prestyled content elements</a> from <em><a href="styles/css/minimal/screen/content_minimal.css">css/minimal/screen/content_minimal.css</a></em>.</p>
        <p><strong>quick jump to ...</strong></p>
        <ul>
            <li><a href="#headings">Heading Levels</a></li>
            <li><a href="#paragraphs">Paragraphs</a></li>
            <li><a href="#blockquotes">Blockquotes</a></li>
            <li><a href="#pre">Preformatted text</a></li>
            <li><a href="#inline">Inline Text Decoration</a></li>
            <li><a href="#lists">Lists</a></li>
            <li><a href="#floatpos">Text &amp; Images</a></li>
            <li><a href="#tables">Tables</a></li>
        </ul>
    <?
    }
    
    protected function column_col3()
    {
    ?> 
        <a name="headings"></a>
        <h3>Heading Levels </h3>
        <h1>H1 Heading</h1>
        <h2>H2 Heading</h2>
        <h3>H3 Heading</h3>
        <h4>H4 Heading</h4>
        <h5>H5 Heading</h5>
        <h6>H6 Heading</h6>
        <hr />

        <a name="paragraphs"></a>
        <h3>Paragraphs </h3>
        <p>This is a normal paragraph text. This is a normal paragraph text. This is a normal paragraph text. This is a normal paragraph text. This is a normal paragraph text. This is a normal paragraph text. This is a normal paragraph text. This is a normal paragraph text. </p>
        <p class="highlight">This is a paragraph text with class=&quot;highlight&quot;. This is a paragraph text with class=&quot;highlight&quot;. This is a paragraph text with class=&quot;highlight&quot;. This is a paragraph text with class=&quot;highlight&quot;. This is a paragraph text with class=&quot;highlight&quot;.</p>
        <p class="note">This is a paragraph text with class=&quot;note&quot;. This is a paragraph text with class=&quot;note&quot;. This is a paragraph text with class=&quot;note&quot;. This is a paragraph text with class=&quot;note&quot;. This is a paragraph text with class=&quot;note&quot;.</p>
        <p class="note_big">This is a paragraph text with class=&quot;note_big&quot;. </p>
        <p class="important">This is a paragraph text with class=&quot;important&quot;. This is a paragraph text with class=&quot;important&quot;. This is a paragraph text with class=&quot;important&quot;. This is a paragraph text with class=&quot;important&quot;. This is a paragraph text with class=&quot;important&quot;.</p>
        <p class="warning">This is a paragraph text with class=&quot;warning&quot;. This is a paragraph text with class=&quot;warning&quot;. This is a paragraph text with class=&quot;warning&quot;. This is a paragraph text with class=&quot;warning&quot;. This is a paragraph text with class=&quot;warning&quot;.</p>
        <p class="desc">This is a paragraph text with class=&quot;desc&quot;. </p>
        <p class="error">This is a paragraph text with class=&quot;error&quot;. </p>
        <p class="small">This is a paragraph text with class=&quot;small&quot;. </p>
        <p class="big">This is a paragraph text with class=&quot;big&quot;. </p>

        <hr />

        <a name="blockquotes"></a>
        <h3>Blockquotes</h3>
        <blockquote>
        <p>This is a paragraph text within a &lt;blockquote&gt; element. This is a paragraph text within a &lt;blockquote&gt; element. This is a paragraph text within a &lt;blockquote&gt; element. This is a paragraph text within a &lt;blockquote&gt; element. </p>

        </blockquote>
        <a name="pre"></a>
        <h3>Preformatted Text </h3>
        <pre>This is preformatted text, wrapped in a &lt;pre&gt; element. <br />This is preformatted text, wrapped in a &lt;pre&gt; element.</pre>

        <hr />
        
        <a name="inline"></a>
        <h3>Inline Semantic Text Decoration</h3>
        <ul>
            <li>a <a href="#">link</a> tag (<code>&lt;a&gt;</code>) example </li>

            <li>an <i>italics</i> and <em>emphasize</em> tag (<code>&lt;i&gt;</code>,<code> &lt;em&gt;</code>) example</li>
            <li>a <b>bold</b> and <strong>strong</strong> tag (<code>&lt;b&gt;</code>, <code>&lt;strong&gt;</code>) example</li>

            <li>an <acronym>acronym</acronym> and <abbr>abbreviation</abbr> tag (<code>&lt;acronym&gt;</code>, <code>&lt;abbr&gt;</code>) example </li>
            <li>a <cite>cite</cite> and <q>quote</q> tag (<code>&lt;cite&gt;</code>, <code>&lt;q&gt;</code> ) example </li>

            <li>a <code>code</code> und <var>variable</var> tag (<code>&lt;code&gt;</code>, <code>&lt;var&gt;</code>) example</li>
            <li>an <ins>inserted</ins> and <del>deleted</del> tag (<code>&lt;ins&gt;</code>, <code>&lt;del&gt;</code>) example</li>

            <li>a <kbd>keyboard</kbd> and <samp>sample</samp> tag (<code>&lt;kbd&gt;</code>, <code>&lt;samp&gt;</code>) example</li>
            <li>a <sub>subscript</sub> and <sup>superscript</sup> tag (<code>&lt;sub&gt;</code>, <code>&lt;sup&gt;</code>) example</li>
        </ul>
        <hr />
        
        <a name="lists" id="lists"></a>
        <h3>Unordered List </h3>
        <p>By default, lists have no bullet points. If we want to show bullet points we use the class &quot;bullet&quot;</p>
        <ul class="bullet">
            <li>ut enim ad minim veniam</li>
            <li>occaecat cupidatat non proident
                <ul>
                    <li>facilisis semper</li>
                    <li>quis ac wisi augue</li>
                    <li>risus nec pretium</li>
                    <li>fames scelerisque</li>
                </ul>
            </li>
            <li>nostrud exercitation ullamco</li>
            <li>labore et dolore magna aliqua</li>
            <li>aute irure dolor in reprehenderit in voluptate velit esse cillum dolore</li>
        </ul>
        
        <h3>Ordered List </h3>
        <ol class="bullet">
            <li>ut enim ad minim veniam
                <ol>
                    <li>facilisis semper</li>
                    <li>quis ac wisi augue</li>
                    <li>risus nec pretium</li>
                    <li>fames scelerisque</li>
                </ol>
            </li>
            <li>occaecat cupidatat non proident</li>
            <li>nostrud exercitation ullamco</li>
            <li>labore et dolore magna aliqua</li>
            <li>aute irure dolor in reprehenderit in voluptate velit esse cillum dolore</li>
        </ol>
        
        <h3>Definition List </h3>
        <dl>
            <dt>A definition list &mdash; this is &lt;dt&gt; </dt>
            <dd>A definition list &mdash; this is  &lt;dd&gt; element.  A definition list &mdash; this is  &lt;dd&gt; element. A definition list &mdash; this is  &lt;dd&gt; element. A definition list &mdash; this is  &lt;dd&gt; element. </dd>

            <dt>A definition list &mdash; this is &lt;dt&gt; </dt>
            <dd>A definition list &mdash; this is  &lt;dd&gt; element.  A definition list &mdash; this is  &lt;dd&gt; element. A definition list &mdash; this is  &lt;dd&gt; element. A definition list &mdash; this is  &lt;dd&gt; element. </dd>

            <dt>A definition list &mdash; this is &lt;dt&gt; </dt>
            <dd>A definition list &mdash; this is  &lt;dd&gt; element.  A definition list &mdash; this is  &lt;dd&gt; element. A definition list &mdash; this is  &lt;dd&gt; element. A definition list &mdash; this is  &lt;dd&gt; element. </dd>
        </dl>
        <hr />
        
        <h3>Pagination</h3>
        <div class="pages">
            <ul>
                <li><a href="#">1</a></li>
                <li class="current"><a href="#">2</a></li>
                <li><a class="off" href="#">3</a></li>
            </ul>
        </div>
        <hr />
          
        <a name="floatpos"></a>
        <h3>Text &amp; Images</h3>
        
        <h4>Image with class=&quot;float_right&quot;</h4>
        <div class="floatbox"><img src="images/dummy_150.png" class="float_right" alt="dummy image" />
            <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. </p>
            <p>Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. </p>
        </div>
        <hr />
        
        <h4>Image with class=&quot;float_left&quot;</h4>
        <div class="floatbox"><img src="images/dummy_150.png" class="float_left" alt="dummy image" />
            <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. </p>
            <p>Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. </p>
        </div>
        <hr />
        
        <h4>Image with class=&quot;center&quot;</h4>
        <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam  nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,  sed diam voluptua. At vero eos et accusam et justo duo dolores et ea  rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem  ipsum dolor sit amet. </p>
        <img src="images/dummy_150.png" class="center" alt="dummy image" />
        <p>Lorem ipsum dolor sit amet, consetetur sadipscing  elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna  aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo  dolores et ea rebum.</p>
        <hr />
        
        <h3>Text &amp; Images with Captions</h3>
        <div class="floatbox">
            <p class="icaption_right"><img src="images/dummy_300.png" alt="dummy image" /><strong><b>Fig. 1:</b>  Sample caption for this
                  beautiful dummy
                image. </strong></p>

            <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. </p>
            <p>Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. </p>
            <p>Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. </p>
        </div>
        <hr />
          
          <div class="floatbox">
          <p class="icaption_left"><img src="images/dummy_300.png" alt="dummy image" /><strong><b>Fig. 2:</b> For captions that are longer than one line, you have<br />

            to define a width for the <code>icaption</code> classes in your<br />
<em>content.css</em> or  include line-breaks (<code>&lt;br/&gt;</code>) manually.</strong></p>
          <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. </p>
            <p>Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.  At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>

            <p>Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. </p>
          </div>
          <hr />
          
          <a name="tables"></a>
                  <h3>Tables</h3>
          <table border="0" cellpadding="0" cellspacing="0">
                    <caption>
                      table 1: this is a simple table with caption
                    </caption>

          <thead>
            <tr><th scope="col" colspan="3">table heading</th></tr>
          </thead>
          <tbody>
            <tr>
              <th scope="col">column 1 </th>
              <th scope="col">column 2 </th>

              <th scope="col">column 3 </th>
            </tr>
            <tr>
              <th scope="row">subhead 1 </th>
              <td>dummy content </td>
              <td>dummy content </td>
            </tr>

            <tr>
              <th scope="row">subhead 2 </th>
              <td>dummy content </td>
              <td>dummy content </td>
            </tr>
            <tr>
              <th scope="row" class="sub">subhead 3</th>

              <td>dummy content </td>
              <td>dummy content </td>
            </tr>
          </tbody>
        </table>
          <p>&nbsp;</p>
          <table border="0" cellpadding="0" cellspacing="0" class="full">
                    <caption>

                      table 2: this is a table with class=&quot;full&quot;
                    </caption>
          <thead>
            <tr><th scope="col" colspan="3">table heading</th></tr>
          </thead>
          <tbody>
            <tr>

              <th scope="col">column 1 </th>
              <th scope="col">column 2 </th>
              <th scope="col">column 3 </th>
            </tr>
            <tr>
              <th scope="row" class="sub">subhead 1</th>
              <td>dummy content </td>

              <td>dummy content </td>
            </tr>
            <tr>
              <th scope="row" class="sub">subhead 2 </th>
              <td>dummy content </td>
              <td>dummy content </td>
            </tr>

            <tr>
              <th scope="row" class="sub">subhead 3</th>
              <td>dummy content </td>
              <td>dummy content </td>
            </tr>
          </tbody>
        </table>
<?

    }
}

?>
