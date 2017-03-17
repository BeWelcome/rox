/* class currently not used
 *
 * usefull to create a circle marker with label
 *
 * */
L.LealletLabelCircleMarker.js = L.CircleMarker.extend({
        _label : null,

      initialize : function(latlng, options) {
        if (options.label) {
          this._label = options.label;
        }
        L.CircleMarker.prototype.initialize.call(this, latlng, options);

        if (!this._createElement) {
          if (L.VERSION == "0.4") {
            this._createElement = L.CircleMarker.prototype._createElement;
          } else if (L.VERSION == "0.3") {
            this._createElement = L.CircleMarker._createElement;
          }
        }
      },

      _initPath : function() {
        var point = this._map.latLngToLayerPoint(this._latlng);
        this._container = this._createElement('g');

        if (this._label !== null) {
          this._text = this._createElement('text');

          this._text.setAttribute('fill', 'black');
          this._text.setAttribute('font-size', '16');
          this._text.setAttribute('x', point.x);
          this._text.setAttribute('y', point.y);
          this._text
              .setAttribute(
                  'style',
                  'writing-mode:lr-tb; line-height:125%; text-align:center; text-anchor: middle; dominant-baseline: central; font-weight:bold;');
          this._text.textContent = this._label;

          this._container.appendChild(this._text);
        }

        this._path = this._createElement('path');
        this._container.appendChild(this._path);

        this._map._pathRoot.appendChild(this._container);
      },

      _updatePath : function() {
        if (this._map) { /*
                   * if map is null than this is removed
                   * marker
                   */
          L.CircleMarker.prototype._updatePath.call(this);

          var point = this._map.latLngToLayerPoint(this._latlng);
          this._text.setAttribute('x', point.x);
          this._text.setAttribute('y', point.y);
        }
      }
    });