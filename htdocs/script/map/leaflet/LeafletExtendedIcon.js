/* class currently not used
 *
 * usefull to add a custom class to an icon (in order to use extra-css, for example to add a colored border)
 *
 * */
 LeafletExtendedIcon = L.Class.extend({
  iconUrl: L.ROOT_URL + 'images/marker.png',
  shadowUrl: L.ROOT_URL + 'images/marker-shadow.png',

  iconUrl : '',
  iconSize : new L.Point(24, 24),
  shadowSize : new L.Point(0, 0),
  iconAnchor : new L.Point(0, 0),
  popupAnchor : new L.Point(-0, 0),
  additionalCssClass: '',

  initialize: function (iconUrl, additionalCssClass) {
    if (iconUrl) {
      this.iconUrl = iconUrl;
    }
    if (additionalCssClass) {
      this.additionalCssClass = additionalCssClass;
    }
  },

  createIcon: function () {
    return this._createIcon('icon');
  },

  createShadow: function () {
    return this._createIcon('shadow');
  },

  _createIcon: function (name) {
    var size = this[name + 'Size'],
      src = this[name + 'Url'];
    if (!src && name === 'shadow') {
      return null;
    }

    var img;
    if (!src) {
      img = this._createDiv();
    }
    else {
      img = this._createImg(src);
    }

    img.className = 'leaflet-marker-' + name + ' ' + this.additionalCssClass;

    img.style.marginLeft = (-this.iconAnchor.x) + 'px';
    img.style.marginTop = (-this.iconAnchor.y) + 'px';

    if (size) {
      img.style.width = size.x + 'px';
      img.style.height = size.y + 'px';
    }

    return img;
  },

  _createImg: function (src) {
    var el;
    if (!L.Browser.ie6) {
      el = document.createElement('img');
      el.src = src;
    } else {
      el = document.createElement('div');
      el.style.filter = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src="' + src + '")';
    }
    return el;
  },

  _createDiv: function () {
    return document.createElement('div');
  }
});