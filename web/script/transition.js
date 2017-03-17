/*
*	Image Transitions Manager, version 0.1
*	(c) 2007 Ajaxorized.com
*
*	Authors:	Willem Spruijt
*				Martijn de Kuijper
*	Website:	http://www.ajaxorized.com/
*/

Element.addMethods( {
		bringToFront : function(p_eElement) {
			p_eElement.setStyle({zIndex:'2'});
			return p_eElement;
		},
		sendToBack : function(p_eElement) {
			p_eElement.setStyle({zIndex:'1'});
			return p_eElement;
		},
		getHeight : function (p_eElement) {
			return p_eElement.offsetHeight;	
		},
		getWidth : function (p_eElement) {
			return p_eElement.offsetWidth;	
		},		
		getCenterHeight : function(p_eElement) {	
			return (p_eElement.offsetHeight/2);
		},
		getCenterWidth : function(p_eElement) {
			return (p_eElement.offsetWidth/2);
		},
		isLoaded : function (p_eElement) {
			return (p_eElement.complete);
		}
	}
);

/* The Transition Class */
var Transition = Class.create();
Transition.prototype = {
		initialize: function( p_eTarget, p_sImage ) {
		this.m_eTarget = $( p_eTarget );
		this.m_eTarget.setStyle( { position: 'relative', overflow:'hidden'} );
		this.m_eLoading = null;

		/* Define the refence to this object globally so we can use it within the scope of the anchors */
		g_eTransition = this;		
	
		// clear all content of holder.
		while( this.m_eTarget.hasChildNodes() )
			this.m_eTarget.removeChild( this.m_eTarget.firstChild );
		
		/*///////////////////////////////////////////////////////////////////////////////////////////
		*	TEMPORARY WAY TO ADD FIRST IMAGE!!
		*	Should be added by the loadImage function, but this expects an anchor as parameter.
		*/
				var eImage = document.createElement( 'img' );
				eImage.setAttribute( 'src', p_sImage );	
				this.m_eTarget.appendChild( eImage );		
				this.m_eCurrent = eImage;
				new Effect.Appear( this.m_eCurrent, { duration: 1.5, from: 0.0, to: 1.0 } );
		/*******************************************************************************************/

		// loop through all anchors. REFACTORED: pretty nifty eh :)
		$$('a').each( function( eAnchor ) {
			var sRel = String( eAnchor.getAttribute( 'rel' ) );			
			if ( eAnchor.getAttribute( 'href' ) && ( sRel.toLowerCase().match( 'transition' ) ) ) {
				eAnchor.m_eRef = this;
				eAnchor.onclick = function () { g_eTransition.loadImage(this); return false; }
			}
		});
	},

	loadImage: function( p_eAnchor ) {
		
		// Get transition type and image url.
		var sTransition = /^transition\[(.+)\]$/.exec( p_eAnchor.getAttribute( 'rel' ) )[ 1 ];
		var sImage = p_eAnchor.getAttribute( 'href' );
		
		var eImage = document.createElement( 'img' );
		eImage.setAttribute( 'src', sImage );	
		$(eImage).setStyle( { position: 'absolute', left: '0px', top: '0px', opacity: '0' } );
		this.m_eTarget.appendChild( eImage );
		if(!eImage.isLoaded()) {
			/* The image is not yet loaded, so fix the loading div */
			this.m_eLoading = document.createElement( 'div' );
			$( this.m_eLoading ).setStyle( { position: 'absolute', left: '5px',  bottom: '5px', color: '#FFF' } );
			this.m_eLoading.appendChild( document.createTextNode( 'loading...' ) );
			this.m_eTarget.appendChild( this.m_eLoading );
			Event.observe( eImage, 'load', this._onLoad.bindAsEventListener( null, this, eImage, sTransition ) );
		} else {
			/* The image is already loaded*/
			this.m_eLoading = null;
			this._transImage(eImage, sTransition);
		}
	},
	
	_onLoad: function( p_eEvent, p_oRef, p_eImage, p_sTransition ) {
		p_oRef._transImage( p_eImage, p_sTransition );
	},

	_transImage : function(eImage, sTransition) {
		if(this.m_eLoading != null) this.m_eLoading.remove();
		/* ADDED: switch on different transitions, use the naming conventions of scriptaculous (appear, fade, etc).?) */
		switch(sTransition) {
			case 'appear':
				new Effect.Appear( eImage, { duration: 1.5, from: 0.0, to: 1.0 } );
				new Effect.Appear( this.m_eCurrent, { duration: 1.5, from: 1.0, to: 0.0, afterFinish: this._removeImage } );
			break;
			case 'switch':
				new Effect.Appear( eImage, { duration: 0, from: 0.0, to: 1.0 } );
				new Effect.Appear( this.m_eCurrent, { duration: 0, from: 1.0, to: 0.0, afterFinish: this._removeImage } );				
			break;
			case 'blinddown':
				$(this.m_eCurrent).setStyle({display:'block', opacity:'1'}).sendToBack();
				l_oTargetDim = {left:0,top:0, width:($(eImage).offsetWidth), height:($(eImage).offsetHeight)};
				$(eImage).setStyle({display:'block', opacity:'1',height:'1px',width:($(eImage).offsetWidth-1)+'px'}).bringToFront(); // this is a must for the blinddown effect
				g_eOldImage = $(this.m_eCurrent);
				$(eImage).morph('height:'+l_oTargetDim.height+'px;width:'+l_oTargetDim.width+'px;top:'+l_oTargetDim.top+'px;left:'+l_oTargetDim.left+'px', {duration:1, afterFinish : function() { g_eOldImage.remove()}});				
			break;
			case 'grow':
				$(eImage).setStyle({display:'none', opacity:'1'}).bringToFront();
				$(this.m_eCurrent).sendToBack(); 
				new Effect.Grow( eImage, { duration: 1, direction:'center' } );				
				new Effect.Appear( this.m_eCurrent, { duration: 1, afterFinish: this._removeImage } );											
			break;
			case 'shrink':
				$(this.m_eCurrent).bringToFront();
				$(eImage).sendToBack(); 
				$(eImage).setStyle({display:'block', opacity:'1'});
				g_eOldImage = $(this.m_eCurrent);
				new Effect.Shrink( this.m_eCurrent, { duration: 1, afterFinish : function() { g_eOldImage.remove(); }} ); // bug in scriptaculous? When called _removedImage on callback the element is not passed										
			break;		
			case 'switchoff':
				$(this.m_eCurrent).setStyle({display:'block', opacity:'1'}).bringToFront();
				$(eImage).setStyle({display:'block', opacity:'1'}).sendToBack();				
				l_oTargetDim = {left:0,top:$(eImage).getCenterHeight(), width:($(eImage).offsetWidth-1), height:0};
				$(this.m_eCurrent).morph('height:'+l_oTargetDim.height+'px;width:'+l_oTargetDim.width+'px;top:'+l_oTargetDim.top+'px;left:'+l_oTargetDim.left+'px', {duration:1, afterFinish:this._removeImage});
			break;
			case 'slidedown':
				$(this.m_eCurrent).setStyle({display:'block', opacity:'1'}).bringToFront();
				$(eImage).setStyle({top:'-'+eImage.getHeight()+'px', display:'block', opacity:'1'});
				$(eImage).morph('top:0px', {duration:1});				
				$(this.m_eCurrent).morph('top:'+eImage.getHeight()+'px', {duration:1, afterFinish : this._removeImage});
			break;
			case 'slideright':
				$(this.m_eCurrent).setStyle({display:'block', opacity:'1'}).bringToFront();
				$(eImage).setStyle({left:'-'+eImage.getWidth()+'px', display:'block', opacity:'1'});
				$(eImage).morph('left:0px', {duration:1});				
				$(this.m_eCurrent).morph('left:'+eImage.getWidth()+'px', {duration:1, afterFinish : this._removeImage});				
			break;
		}	
		this.m_eCurrent = eImage;
	},

	_removeImage: function( p_oObj ) {
		p_oObj.element.remove();
	}
}