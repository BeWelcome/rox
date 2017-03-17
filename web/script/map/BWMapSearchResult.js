/**
 * Represent a Search result.
 *
 * @member xmlDoc the XML AJAX response
 * @member summary
 * @member header
 * @member markers
 * @member pager
 * @member detailHeader
 * @member firstPage
 * @member per_page
 * @member firstPageChild
 * @member paging
 * @member numResults
 * @member footer
 * @member detailFooter
 *
 */
var BWMapSearchResult = Class
    .create({
      /**
       * constructor
       */
      initialize : function(xmlDoc) {
        bwrox.debug("Parsing XML response...");
        // bwrox.debug(xmlDoc);
        this.xmlDoc = xmlDoc;

        this.header = getxmlEl(xmlDoc, "header");
        this.markers = getxmlEl(xmlDoc, "marker");
        this.pager = getxmlEl(xmlDoc, "pager");

        if (this.header && this.header[0]) {
          this.detailHeader = this.header[0].getAttribute("header");
        } else {
          bwrox.warn("Header is missing.");
        }
        if (! this.detailHeader){
          this.detailHeader = "";
        }
        if (this.pager) {
          this.firstPage = this.pager[0];
          if (this.firstPage != null) {
            this.per_page = this.firstPage.getAttribute('per_page');
            this.firstPageChild = this.firstPage.firstChild;
            this.paging = this.firstPage.getAttribute('paging');
          } else {
            bwrox.warn("First page is missing.");
          }
        } else {
          bwrox.warn("Pager is missing.");
        }
        var results = getxmlEl(xmlDoc, "num_results");
        if (results) {
          this.numResults = Number(results[0].getAttribute("num_results"));
          bwrox.debug("Number of results:" + this.numResults);
        } else {
          bwrox.warn("Number of results is missing.");
        }

        this.numAllResults = Number(results[0].getAttribute("num_all_results"));

        this.footer = getxmlEl(xmlDoc, "footer");
        if (this.footer && this.footer[0]) {
          this.detailFooter = this.footer[0].getAttribute("footer");
        } else {
          bwrox.warn("Footer is missing.");
        }
        if (! this.detailFooter){
          this.detailFooter = "";
        }

      },
      /**
       * @return true if first page exists
       */
      hasResults : function() {
        if (this.numResults && this.numResults != 0) {
          return true;
        } else {
          return false;
        }
      },
      /**
       * remove the first child of first page
       */
      removeFirstPageChild : function() {
        return this.firstPage.removeChild(this.firstPageChild);
      },
      /**
       * return first page paging
       */
      getPaging : function() {
        return this.paging;
      },
      /**
       * Read the points
       */
      readPoints : function() {
        bwrox.log("Reading the " + this.markers.length + " points...");
        this.points = new Array();
        for ( var i = 0; i < this.markers.length; i++) {
          this.points[i] = new BWMapHostPoint(this.markers[i]);
        }
        // fix points
        this.fixPoints();
        bwrox.info(this.points.length + " points have been read.");
        return this.points;
      },
      fixPoints : function() {
        // combine marker summaries when coordinates and accomodation is
        // the same,
        // in groups of columns x rows
        bwrox.log("Combine points summaries when coordinates and accomodation is the same...");
        for ( var i = 0; i < this.points.length; i++) {
          if (this.points[i].summary == '') {
            continue;
          }
          column = 0;
          this.points[i].summary = '<table><tr><td>'
              + this.points[i].summary;
          for ( var j = i + 1; j < this.points.length; j++) {
            if (this.points[j].summary == '') {
              continue;
            }
          }
          this.points[i].summary += '</td></tr></table>';
        }

        // space markers that have the same geo-coordinates
        bwrox.log("Space markers that have the same geo-coordinates...");
        var offset = 1;
        var newpoint = 1;
        for (i = 0; i < this.points.length; i++) {
          for (j = i + 1; j < this.points.length; j++) {
            if (this.points[i].longitude == this.points[j].longitude
                && this.points[i].latitude == this.points[j].latitude) {
              this.points[i].longitude = (0.001 * offset)
                  * Math.cos(40 * newpoint * Math.PI / 180)
                  - (0.001 * offset)
                  * Math.sin(40 * newpoint * Math.PI / 180)
                  + this.points[i].longitude;
              this.points[i].latitude = (0.001 * offset)
                  * Math.sin(40 * newpoint * Math.PI / 180)
                  + (0.001 * offset)
                  * Math.cos(40 * newpoint * Math.PI / 180)
                  + this.points[i].latitude;
              ++newpoint;
              if (newpoint == 9) {
                newpoint = 1;
                offset = offset + 1;
              }
            }
          }
        }
      }
    });