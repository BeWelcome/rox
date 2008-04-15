/*
    PlotKit Sweet Canvas Renderer
    =============================
    Canvas Renderer for PlotKit which looks pretty!

    Copyright
    ---------
    Copyright 2005,2006 (c) Alastair Tse <alastair^liquidx.net>
    For use under the BSD license. <http://www.liquidx.net/plotkit>
*/

// -------------------------------------------------------------------------
// Check required components
// -------------------------------------------------------------------------

try {    
    if (typeof(PlotKit.CanvasRenderer) == 'undefined')
    {
        throw "";    
    }
} 
catch (e) {    
    throw "SweetCanvas depends on MochiKit.{Base,Color,DOM,Format} and PlotKit.{Layout, Canvas}"
}


if (typeof(PlotKit.SweetCanvasRenderer) == 'undefined') {
    PlotKit.SweetCanvasRenderer = {};
}

PlotKit.SweetCanvasRenderer = function(element, layout, options) {
    if (arguments.length > 0) {
        this.__init__(element, layout, options);
    }
};

PlotKit.SweetCanvasRenderer.NAME = "PlotKit.SweetCanvasRenderer";
PlotKit.SweetCanvasRenderer.VERSION = PlotKit.VERSION;

PlotKit.SweetCanvasRenderer.__repr__ = function() {
    return "[" + this.NAME + " " + this.VERSION + "]";
};

PlotKit.SweetCanvasRenderer.toString = function() {
    return this.__repr__();
};

// ---------------------------------------------------------------------
// Subclassing Magic
// ---------------------------------------------------------------------

PlotKit.SweetCanvasRenderer.prototype = new PlotKit.CanvasRenderer();
PlotKit.SweetCanvasRenderer.prototype.constructor = PlotKit.SweetCanvasRenderer;
PlotKit.SweetCanvasRenderer.__super__ = PlotKit.CanvasRenderer.prototype;

// ---------------------------------------------------------------------
// Constructor
// ---------------------------------------------------------------------

PlotKit.SweetCanvasRenderer.prototype.__init__ = function(el, layout, opts) { 
    var moreOpts = PlotKit.Base.officeBlue();
    MochiKit.Base.update(moreOpts, opts);
    PlotKit.SweetCanvasRenderer.__super__.__init__.call(this, el, layout, moreOpts);
};

// ---------------------------------------------------------------------
// Extended Plotting Functions
// ---------------------------------------------------------------------

PlotKit.SweetCanvasRenderer.prototype._renderBarChart = function() {
    var bind = MochiKit.Base.bind;
    var shadowColor = Color.blackColor().colorWithAlpha(0.1).toRGBString();

    var prepareFakeShadow = function(context, x, y, w, h) {
        context.fillStyle = shadowColor;
        context.fillRect(x-2, y-2, w+4, h+2); 
        context.fillStyle = shadowColor;
        context.fillRect(x-1, y-1, w+2, h+1); 
    };

    var colorCount = this.options.colorScheme.length;
    var colorScheme =  this.options.colorScheme;
    var setNames = PlotKit.Base.keys(this.layout.datasets);
    var setCount = setNames.length;

    var chooseColor = function(name) {
        for (var i = 0; i < setCount; i++) {
            if (name == setNames[i])
                return colorScheme[i%colorCount];
        }
        return colorScheme[0];
    };

    var drawRect = function(context, bar) {
        var x = this.area.w * bar.x + this.area.x;
        var y = this.area.h * bar.y + this.area.y;
        var w = this.area.w * bar.w;
        var h = this.area.h * bar.h;

        if ((w < 1) || (h < 1))
            return;        

        context.save();

        context.shadowBlur = 5.0;
        context.shadowColor = Color.fromHexString("#888888").toRGBString();

        if (this.isIE) {
            context.save();
            context.fillStyle = "#cccccc";
            context.fillRect(x-2, y-2, w+4, h+2); 
            context.restore();
        }
        else {
            prepareFakeShadow(context, x, y, w, h);
        }

        if (this.options.shouldFill) {
            context.fillStyle = chooseColor(bar.name).toRGBString();
            context.fillRect(x, y, w, h);
        }

        context.shadowBlur = 0;
        context.strokeStyle = Color.whiteColor().toRGBString();
        context.lineWidth = 2.0;
        
        if (this.options.shouldStroke) {
            context.strokeRect(x, y, w, h);                
        }

        context.restore();

    };
    this._renderBarChartWrap(this.layout.bars, bind(drawRect, this));
};

PlotKit.SweetCanvasRenderer.prototype._renderLineChart = function() {
    var context = this.element.getContext("2d");
    var colorCount = this.options.colorScheme.length;
    var colorScheme = this.options.colorScheme;
    var setNames = PlotKit.Base.keys(this.layout.datasets);
    var setCount = setNames.length;
    var bind = MochiKit.Base.bind;


    for (var i = 0; i < setCount; i++) {
        var setName = setNames[i];
        var color = colorScheme[i%colorCount];
        var strokeX = this.options.strokeColorTransform;

        // setup graphics context
        context.save();
        
        // create paths
        var makePath = function(ctx) {
            ctx.beginPath();
            ctx.moveTo(this.area.x, this.area.y + this.area.h);
            var addPoint = function(ctx_, point) {
            if (point.name == setName)
                ctx_.lineTo(this.area.w * point.x + this.area.x,
                            this.area.h * point.y + this.area.y);
            };
            MochiKit.Iter.forEach(this.layout.points, partial(addPoint, ctx), this);
            ctx.lineTo(this.area.w + this.area.x,
                           this.area.h + this.area.y);
            ctx.lineTo(this.area.x, this.area.y + this.area.h);
            ctx.closePath();
        };

        // faux shadow for firefox
        if (this.options.shouldFill) {
            context.save();
            if (this.isIE) {
                context.fillStyle = "#cccccc";
            }
            else {
                context.fillStyle = Color.blackColor().colorWithAlpha(0.2).toRGBString();
            }
            context.translate(-1, -2);
            bind(makePath, this)(context);
            if (this.options.shouldFill) {
                context.fill();
            }
            context.restore();
        }

        context.shadowBlur = 5.0;
        context.shadowColor = Color.fromHexString("#888888").toRGBString();
        context.fillStyle = color.toRGBString();
        context.lineWidth = 2.0;
        context.strokeStyle = Color.whiteColor().toRGBString();

        if (this.options.shouldFill) {
            bind(makePath, this)(context);
            context.fill();
        }
        if (this.options.shouldStroke) {
            bind(makePath, this)(context);
            context.stroke();
        }
        context.restore();
    }
};

PlotKit.SweetCanvasRenderer.prototype._renderPieChart = function() {
    var context = this.element.getContext("2d");

    var colorCount = this.options.colorScheme.length;
    var slices = this.layout.slices;

    var centerx = this.area.x + this.area.w * 0.5;
    var centery = this.area.y + this.area.h * 0.5;
    var radius = Math.min(this.area.w * this.options.pieRadius, 
                          this.area.h * this.options.pieRadius);

    if (this.isIE) {
        centerx = parseInt(centerx);
        centery = parseInt(centery);
        radius = parseInt(radius);
    }

	// NOTE NOTE!! Canvas Tag draws the circle clockwise from the y = 0, x = 1
	// so we have to subtract 90 degrees to make it start at y = 1, x = 0

    if (!this.isIE) {
        context.save();
        var shadowColor = Color.blackColor().colorWithAlpha(0.2);
        context.fillStyle = shadowColor.toRGBString();
        context.shadowBlur = 5.0;
        context.shadowColor = Color.fromHexString("#888888").toRGBString();
        context.translate(1, 1);
        context.beginPath();
        context.moveTo(centerx, centery);
        context.arc(centerx, centery, radius + 2, 0, Math.PI*2, false);
        context.closePath();
        context.fill();
        context.restore();
    }

    context.save();
    context.strokeStyle = Color.whiteColor().toRGBString();
    context.lineWidth = 2.0;    
    for (var i = 0; i < slices.length; i++) {
        var color = this.options.colorScheme[i%colorCount];
        context.fillStyle = color.toRGBString();

        var makePath = function() {
            context.beginPath();
            context.moveTo(centerx, centery);
            context.arc(centerx, centery, radius, 
                        slices[i].startAngle - Math.PI/2,
                        slices[i].endAngle - Math.PI/2,
                        false);
            context.lineTo(centerx, centery);
            context.closePath();
        };

        if (Math.abs(slices[i].startAngle - slices[i].endAngle) > 0.0001) {
            if (this.options.shouldFill) {
                makePath();
                context.fill();
            }
            if (this.options.shouldStroke) {
                makePath();
                context.stroke();
            }
        }
    }
    context.restore();
};

PlotKit.SweetCanvasRenderer.prototype._renderBackground = function() {
    var context = this.element.getContext("2d");
   
    if (this.layout.style == "bar" || this.layout.style == "line") {
        context.save();
        context.fillStyle = this.options.backgroundColor.toRGBString();
        context.fillRect(this.area.x, this.area.y, this.area.w, this.area.h);
        context.strokeStyle = this.options.axisLineColor.toRGBString();
        context.lineWidth = 1.0;
        
        var ticks = this.layout.yticks;
        var horiz = false;
        if (this.layout.style == "bar" && 
            this.layout.options.barOrientation == "horizontal") {
                ticks = this.layout.xticks;
                horiz = true;
        }
        
        for (var i = 0; i < ticks.length; i++) {
            var x1 = 0;
            var y1 = 0;
            var x2 = 0;
            var y2 = 0;
            
            if (horiz) {
                x1 = ticks[i][0] * this.area.w + this.area.x;
                y1 = this.area.y;
                x2 = x1;
                y2 = y1 + this.area.h;
            }
            else {
                x1 = this.area.x;
                y1 = ticks[i][0] * this.area.h + this.area.y;
                x2 = x1 + this.area.w;
                y2 = y1;
            }
            
            context.beginPath();
            context.moveTo(x1, y1);
            context.lineTo(x2, y2);
            context.closePath();
            context.stroke();
        }
        context.restore();
    }
    else {
        PlotKit.SweetCanvasRenderer.__super__._renderBackground.call(this);
    }
};

// Namespace Iniitialisation

PlotKit.SweetCanvas = {}
PlotKit.SweetCanvas.SweetCanvasRenderer = PlotKit.SweetCanvasRenderer;

PlotKit.SweetCanvas.EXPORT = [
    "SweetCanvasRenderer"
];

PlotKit.SweetCanvas.EXPORT_OK = [
    "SweetCanvasRenderer"
];

PlotKit.SweetCanvas.__new__ = function() {
    var m = MochiKit.Base;
    
    m.nameFunctions(this);
    
    this.EXPORT_TAGS = {
        ":common": this.EXPORT,
        ":all": m.concat(this.EXPORT, this.EXPORT_OK)
    };
};

PlotKit.SweetCanvas.__new__();
MochiKit.Base._exportSymbols(this, PlotKit.SweetCanvas);

