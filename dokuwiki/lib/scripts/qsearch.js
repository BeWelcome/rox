/**
 * AJAX functions for the pagename quicksearch
 *
 * @license  GPL2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Andreas Gohr <andi@splitbrain.org>
 * @author   Adrian Lang <lang@cosmocode.de>
 * @author   Michal Rezler <m.rezler@centrum.cz>
 */
jQuery.fn.dw_qsearch = function (overrides) {

    var dw_qsearch = {

        output: '#qsearch__out',

        $inObj: this,
        $outObj: null,
        timer: null,
        curRequest: null,

        /**
         * initialize the quick search
         *
         * Attaches the event handlers
         *
         */
        init: function () {
            var do_qsearch;

            dw_qsearch.$outObj = jQuery(dw_qsearch.output);

            // objects found?
            if (dw_qsearch.$inObj.length === 0 ||
                dw_qsearch.$outObj.length === 0) {
                return;
            }

            // attach eventhandler to search field
            do_qsearch = function () {
                // abort any previous request
                if (dw_qsearch.curRequest != null) {
                    dw_qsearch.curRequest.abort();
                }
                var value = dw_qsearch.getSearchterm();
                if (value === '') {
                    dw_qsearch.clear_results();
                    return;
                }
                dw_qsearch.$inObj.parents('form').addClass('searching');
                dw_qsearch.curRequest = jQuery.post(
                    DOKU_BASE + 'lib/exe/ajax.php',
                    {
                        call: 'qsearch',
                        q: encodeURI(value)
                    },
                    dw_qsearch.onCompletion,
                    'html'
                );
            };

            dw_qsearch.$inObj.keyup(
                function () {
                    if (dw_qsearch.timer) {
                        window.clearTimeout(dw_qsearch.timer);
                        dw_qsearch.timer = null;
                    }
                    dw_qsearch.timer = window.setTimeout(do_qsearch, 500);
                }
            );

            // attach eventhandler to output field
            dw_qsearch.$outObj.click(dw_qsearch.clear_results);
        },

        /**
         * Read search term from input
         */
        getSearchterm: function() {
            return dw_qsearch.$inObj.val();
        },

        /**
         * Empty and hide the output div
         */
        clear_results: function () {
            dw_qsearch.$inObj.parents('form').removeClass('searching');
            dw_qsearch.$outObj.hide();
            dw_qsearch.$outObj.text('');
        },

        /**
         * Callback. Reformat and display the results.
         *
         * Namespaces are shortened here to keep the results from overflowing
         * or wrapping
         *
         * @param data The result HTML
         */
        onCompletion: function (data) {
            var max, $links, too_big;
            dw_qsearch.$inObj.parents('form').removeClass('searching');

            dw_qsearch.curRequest = null;

            if (data === '') {
                dw_qsearch.clear_results();
                return;
            }

            dw_qsearch.$outObj
                .html(data)
                .show()
                .css('white-space', 'nowrap');

            // disable overflow during shortening
            dw_qsearch.$outObj.find('li').css('overflow', 'visible');

            $links = dw_qsearch.$outObj.find('a');
            max = dw_qsearch.$outObj[0].clientWidth; // maximum width allowed (but take away paddings below)
            if (document.documentElement.dir === 'rtl') {
                max -= parseInt(dw_qsearch.$outObj.css('padding-left'));
                too_big = function (l) {
                    return l.offsetLeft < 0;
                };
            } else {
                max -= parseInt(dw_qsearch.$outObj.css('padding-right'));
                too_big = function (l) {
                    return l.offsetWidth + l.offsetLeft > max;
                };
            }

            $links.each(function () {
                var start, length, replace, nsL, nsR, eli, runaway;

                if (!too_big(this)) {
                    return;
                }

                nsL = this.textContent.indexOf('(');
                nsR = this.textContent.indexOf(')');
                eli = 0;
                runaway = 0;

                while ((nsR - nsL > 3) && too_big(this) && runaway++ < 500) {
                    if (eli !== 0) {
                        // elipsis already inserted
                        if ((eli - nsL) > (nsR - eli)) {
                            // cut left
                            start = eli - 2;
                            length = 2;
                        } else {
                            // cut right
                            start = eli + 1;
                            length = 1;
                        }
                        replace = '';
                    } else {
                        // replace middle with ellipsis
                        start = Math.floor(nsL + ((nsR - nsL) / 2));
                        length = 1;
                        replace = '…';
                    }
                    this.textContent = substr_replace(this.textContent,
                        replace, start, length);

                    eli = this.textContent.indexOf('…');
                    nsL = this.textContent.indexOf('(');
                    nsR = this.textContent.indexOf(')');
                }
            });

            // reenable overflow
            dw_qsearch.$outObj.find('li').css('overflow', 'hidden').css('text-overflow', 'ellipsis');
        }


    };

    jQuery.extend(dw_qsearch, overrides);

    if (!overrides.deferInit) {
        dw_qsearch.init();
    }

    return dw_qsearch;
};

jQuery(function () {
    jQuery('#qsearch__in').dw_qsearch({
        output: '#qsearch__out'
    });
});
