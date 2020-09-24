/*!
 * jsUri v1.1.1
 * https://github.com/derek-watson/jsUri
 *
 * Copyright 2011, Derek Watson
 * Released under the MIT license.
 * http://jquery.org/license
 *
 * Includes parseUri regular expressions
 * http://blog.stevenlevithan.com/archives/parseuri
 * Copyright 2007, Steven Levithan
 * Released under the MIT license.
 *
 * Date: Mon Nov 14 20:06:34 2011 -0800
 */
    

var Query = function (queryString) {

    // query string parsing, parameter manipulation and stringification

    'use strict';

    var // parseQuery(q) parses the uri query string and returns a multi-dimensional array of the components
        parseQuery = function (q) {
            var arr = [], i, ps, p, keyval;

            if (typeof (q) === 'undefined' || q === null || q === '') {
                return arr;
            }

            if (q.indexOf('?') === 0) {
                q = q.substring(1);
            }

            ps = q.toString().split(/[&;]/);

            for (i = 0; i < ps.length; i++) {
                p = ps[i];
                keyval = p.split('=');
                arr.push([keyval[0], keyval[1]]);
            }

            return arr;
        },

        params = parseQuery(queryString),

        // toString() returns a string representation of the internal state of the object
        toString = function () {
            var s = '', i, param;
            for (i = 0; i < params.length; i++) {
                param = params[i];
                if (s.length > 0) {
                    s += '&';
                }
                s += param.join('=');
            }
            return s.length > 0 ? '?' + s : s;
        },

        decode = function (s) {
            s = decodeURIComponent(s);
            s = s.replace('+', ' ');
            return s;
        },

        // getParamValues(key) returns the first query param value found for the key 'key'
        getParamValue = function (key) {
            var param, i;
            for (i = 0; i < params.length; i++) {
                param = params[i];
                if (decode(key) === decode(param[0])) {
                    return param[1];
                }
            }
        },

        // getParamValues(key) returns an array of query param values for the key 'key'
        getParamValues = function (key) {
            var arr = [], i, param;
            for (i = 0; i < params.length; i++) {
                param = params[i];
                if (decode(key) === decode(param[0])) {
                    arr.push(param[1]);
                }
            }
            return arr;
        },

        // deleteParam(key) removes all instances of parameters named (key)
        // deleteParam(key, val) removes all instances where the value matches (val)
        deleteParam = function (key, val) {

            var arr = [], i, param, keyMatchesFilter, valMatchesFilter;

            for (i = 0; i < params.length; i++) {

                param = params[i];
                keyMatchesFilter = decode(param[0]) === decode(key);
                valMatchesFilter = decode(param[1]) === decode(val);

                if ((arguments.length === 1 && !keyMatchesFilter) || (arguments.length === 2 && !keyMatchesFilter && !valMatchesFilter)) {
                    arr.push(param);
                }
            }

            params = arr;

            return this;
        },

        // addParam(key, val) Adds an element to the end of the list of query parameters
        // addParam(key, val, index) adds the param at the specified position (index)
        addParam = function (key, val, index) {

            if (arguments.length === 3 && index !== -1) {
                index = Math.min(index, params.length);
                params.splice(index, 0, [key, val]);
            } else if (arguments.length > 0) {
                params.push([key, val]);
            }
            return this;
        },

        // replaceParam(key, newVal) deletes all instances of params named (key) and replaces them with the new single value
        // replaceParam(key, newVal, oldVal) deletes only instances of params named (key) with the value (val) and replaces them with the new single value
        // this function attempts to preserve query param ordering
        replaceParam = function (key, newVal, oldVal) {

            var index = -1, i, param;

            if (arguments.length === 3) {
                for (i = 0; i < params.length; i++) {
                    param = params[i];
                    if (decode(param[0]) === decode(key) && decodeURIComponent(param[1]) === decode(oldVal)) {
                        index = i;
                        break;
                    }
                }
                deleteParam(key, oldVal).addParam(key, newVal, index);
            } else {
                for (i = 0; i < params.length; i++) {
                    param = params[i];
                    if (decode(param[0]) === decode(key)) {
                        index = i;
                        break;
                    }
                }
                deleteParam(key);
                addParam(key, newVal, index);
            }
            return this;
        };

    // public api
    return {
        getParamValue: getParamValue,
        getParamValues: getParamValues,
        deleteParam: deleteParam,
        addParam: addParam,
        replaceParam: replaceParam,
        
        toString: toString
    };
};

var Uri = function (uriString) {

    // uri string parsing, attribute manipulation and stringification

    'use strict';

    /*global Query: true */
    /*jslint regexp: false, plusplus: false */

    var strictMode = false,

        // parseUri(str) parses the supplied uri and returns an object containing its components
        parseUri = function (str) {

            /*jslint unparam: true */
            var parsers = {
                    strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                    loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
                },
                keys = ["source", "protocol", "authority", "userInfo", "user", "password", "host", "port", "relative", "path", "directory", "file", "query", "anchor"],
                q = {
                    name: "queryKey",
                    parser: /(?:^|&)([^&=]*)=?([^&]*)/g
                },
                m = parsers[strictMode ? "strict" : "loose"].exec(str),
                uri = {},
                i = 14;

            while (i--) {
                uri[keys[i]] = m[i] || "";
            }

            uri[q.name] = {};
            uri[keys[12]].replace(q.parser, function ($0, $1, $2) {
                if ($1) {
                    uri[q.name][$1] = $2;
                }
            });

            return uri;
        },

        uriParts = parseUri(uriString || ''),

        queryObj = new Query(uriParts.query),


        /*
            Basic get/set functions for all properties
        */

        protocol = function (val) {
            if (typeof val !== 'undefined') {
                uriParts.protocol = val;
            }
            return uriParts.protocol;
        },

        hasAuthorityPrefixUserPref = null,

        // hasAuthorityPrefix: if there is no protocol, the leading // can be enabled or disabled
        hasAuthorityPrefix = function (val) {

            if (typeof val !== 'undefined') {
                hasAuthorityPrefixUserPref = val;
            }

            if (hasAuthorityPrefixUserPref === null) {
                return (uriParts.source.indexOf('//') !== -1);
            } else {
                return hasAuthorityPrefixUserPref;
            }
        },

        userInfo = function (val) {
            if (typeof val !== 'undefined') {
                uriParts.userInfo = val;
            }
            return uriParts.userInfo;
        },

        host = function (val) {
            if (typeof val !== 'undefined') {
                uriParts.host = val;
            }
            return uriParts.host;
        },

        port = function (val) {
            if (typeof val !== 'undefined') {
                uriParts.port = val;
            }
            return uriParts.port;
        },

        path = function (val) {
            if (typeof val !== 'undefined') {
                uriParts.path = val;
            }
            return uriParts.path;
        },

        query = function (val) {
            if (typeof val !== 'undefined') {
                queryObj = new Query(val);
            }
            return queryObj;
        },

        anchor = function (val) {
            if (typeof val !== 'undefined') {
                uriParts.anchor = val;
            }
            return uriParts.anchor;
        },


        /*
            Fluent setters for Uri uri properties
        */

        setProtocol = function (val) {
            protocol(val);
            return this;
        },

        setHasAuthorityPrefix = function (val) {
            hasAuthorityPrefix(val);
            return this;
        },

        setUserInfo = function (val) {
            userInfo(val);
            return this;
        },

        setHost = function (val) {
            host(val);
            return this;
        },

        setPort = function (val) {
            port(val);
            return this;
        },

        setPath = function (val) {
            path(val);
            return this;
        },

        setQuery = function (val) {
            query(val);
            return this;
        },

        setAnchor = function (val) {
            anchor(val);
            return this;
        },

        /*
            Query method wrappers
        */
        getQueryParamValue = function (key) {
            return query().getParamValue(key);
        },

        getQueryParamValues = function (key) {
            return query().getParamValues(key);
        },

        deleteQueryParam = function (key, val) {
            if (arguments.length === 2) {
                query().deleteParam(key, val);
            } else {
                query().deleteParam(key);
            }

            return this;
        },

        addQueryParam = function (key, val, index) {
            if (arguments.length === 3) {
                query().addParam(key, val, index);
            } else {
                query().addParam(key, val);
            }
            return this;
        },

        replaceQueryParam = function (key, newVal, oldVal) {
            if (arguments.length === 3) {
                query().replaceParam(key, newVal, oldVal);
            } else {
                query().replaceParam(key, newVal);
            }

            return this;
        },

        /*
            Serialization
        */

        // toString() stringifies the current state of the uri
        toString = function () {

            var s = '',
                is = function (s) {
                    return (s !== null && s !== '');
                };

            if (is(protocol())) {
                s += protocol();
                if (protocol().indexOf(':') !== protocol().length - 1) {
                    s += ':';
                }
                s += '//';
            } else {
                if (hasAuthorityPrefix() && is(host())) {
                    s += '//';
                }
            }

            if (is(userInfo()) && is(host())) {
                s += userInfo();
                if (userInfo().indexOf('@') !== userInfo().length - 1) {
                    s += '@';
                }
            }

            if (is(host())) {
                s += host();
                if (is(port())) {
                    s += ':' + port();
                }
            }

            if (is(path())) {
                s += path();
            } else {
                if (is(host()) && (is(query().toString()) || is(anchor()))) {
                    s += '/';
                }
            }
            if (is(query().toString())) {
                if (query().toString().indexOf('?') !== 0) {
                    s += '?';
                }
                s += query().toString();
            }

            if (is(anchor())) {
                if (anchor().indexOf('#') !== 0) {
                    s += '#';
                }
                s += anchor();
            }

            return s;
        },

        /*
            Cloning
        */

        // clone() returns a new, identical Uri instance
        clone = function () {
            return new Uri(toString());
        };

    // public api
    return {

        protocol: protocol,
        hasAuthorityPrefix: hasAuthorityPrefix,
        userInfo: userInfo,
        host: host,
        port: port,
        path: path,
        query: query,
        anchor: anchor,
        
        setProtocol: setProtocol,
        setHasAuthorityPrefix: setHasAuthorityPrefix,
        setUserInfo: setUserInfo,
        setHost: setHost,
        setPort: setPort,
        setPath: setPath,
        setQuery: setQuery,
        setAnchor: setAnchor,
        
        getQueryParamValue: getQueryParamValue,
        getQueryParamValues: getQueryParamValues,
        deleteQueryParam: deleteQueryParam,
        addQueryParam: addQueryParam,
        replaceQueryParam: replaceQueryParam,
        
        toString: toString,
        clone: clone
    };
};

/* add compatibility for users of jsUri <= 1.1.1 */
var jsUri = Uri;

/*!
 * jQuery blockUI plugin
 * Version 2.64.0-2013.07.18
 * @requires jQuery v1.7 or later
 *
 * Examples at: http://malsup.com/jquery/block/
 * Copyright (c) 2007-2013 M. Alsup
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 * Thanks to Amir-Hossein Sobhi for some excellent contributions!
 */

;(function() {
/*jshint eqeqeq:false curly:false latedef:false */
"use strict";

	function setup($) {
		$.fn._fadeIn = $.fn.fadeIn;

		var noOp = $.noop || function() {};

		// this bit is to ensure we don't call setExpression when we shouldn't (with extra muscle to handle
		// confusing userAgent strings on Vista)
		var msie = /MSIE/.test(navigator.userAgent);
		var ie6  = /MSIE 6.0/.test(navigator.userAgent) && ! /MSIE 8.0/.test(navigator.userAgent);
		var mode = document.documentMode || 0;
		var setExpr = $.isFunction( document.createElement('div').style.setExpression );

		// global $ methods for blocking/unblocking the entire page
		$.blockUI   = function(opts) { install(window, opts); };
		$.unblockUI = function(opts) { remove(window, opts); };

		// convenience method for quick growl-like notifications  (http://www.google.com/search?q=growl)
		$.growlUI = function(title, message, timeout, onClose) {
			var $m = $('<div class="growlUI"></div>');
			if (title) $m.append('<h1>'+title+'</h1>');
			if (message) $m.append('<h2>'+message+'</h2>');
			if (timeout === undefined) timeout = 3000;

			// Added by konapun: Set timeout to 30 seconds if this growl is moused over, like normal toast notifications
			var callBlock = function(opts) {
				opts = opts || {};

				$.blockUI({
					message: $m,
					fadeIn : typeof opts.fadeIn  !== 'undefined' ? opts.fadeIn  : 700,
					fadeOut: typeof opts.fadeOut !== 'undefined' ? opts.fadeOut : 1000,
					timeout: typeof opts.timeout !== 'undefined' ? opts.timeout : timeout,
					centerY: false,
					showOverlay: false,
					onUnblock: onClose,
					css: $.blockUI.defaults.growlCSS
				});
			};

			callBlock();
			var nonmousedOpacity = $m.css('opacity');
			$m.mouseover(function() {
				callBlock({
					fadeIn: 0,
					timeout: 30000
				});

				var displayBlock = $('.blockMsg');
				displayBlock.stop(); // cancel fadeout if it has started
				displayBlock.fadeTo(300, 1); // make it easier to read the message by removing transparency
			}).mouseout(function() {
				$('.blockMsg').fadeOut(1000);
			});
			// End konapun additions
		};

		// plugin method for blocking element content
		$.fn.block = function(opts) {
			if ( this[0] === window ) {
				$.blockUI( opts );
				return this;
			}
			var fullOpts = $.extend({}, $.blockUI.defaults, opts || {});
			this.each(function() {
				var $el = $(this);
				if (fullOpts.ignoreIfBlocked && $el.data('blockUI.isBlocked'))
					return;
				$el.unblock({ fadeOut: 0 });
			});

			return this.each(function() {
				if ($.css(this,'position') == 'static') {
					this.style.position = 'relative';
					$(this).data('blockUI.static', true);
				}
				this.style.zoom = 1; // force 'hasLayout' in ie
				install(this, opts);
			});
		};

		// plugin method for unblocking element content
		$.fn.unblock = function(opts) {
			if ( this[0] === window ) {
				$.unblockUI( opts );
				return this;
			}
			return this.each(function() {
				remove(this, opts);
			});
		};

		$.blockUI.version = 2.60; // 2nd generation blocking at no extra cost!

		// override these in your code to change the default behavior and style
		$.blockUI.defaults = {
			// message displayed when blocking (use null for no message)
			message:  '<h1>Please wait...</h1>',

			title: null,		// title string; only used when theme == true
			draggable: true,	// only used when theme == true (requires jquery-ui.js to be loaded)

			theme: false, // set to true to use with jQuery UI themes

			// styles for the message when blocking; if you wish to disable
			// these and use an external stylesheet then do this in your code:
			// $.blockUI.defaults.css = {};
			css: {
				padding:	0,
				margin:		0,
				width:		'30%',
				top:		'40%',
				left:		'35%',
				textAlign:	'center',
				color:		'#000',
				border:		'3px solid #aaa',
				backgroundColor:'#fff',
				cursor:		'wait'
			},

			// minimal style set used when themes are used
			themedCSS: {
				width:	'30%',
				top:	'40%',
				left:	'35%'
			},

			// styles for the overlay
			overlayCSS:  {
				backgroundColor:	'#000',
				opacity:			0.6,
				cursor:				'wait'
			},

			// style to replace wait cursor before unblocking to correct issue
			// of lingering wait cursor
			cursorReset: 'default',

			// styles applied when using $.growlUI
			growlCSS: {
				width:		'350px',
				top:		'10px',
				left:		'',
				right:		'10px',
				border:		'none',
				padding:	'5px',
				opacity:	0.6,
				cursor:		'default',
				color:		'#fff',
				backgroundColor: '#000',
				'-webkit-border-radius':'10px',
				'-moz-border-radius':	'10px',
				'border-radius':		'10px'
			},

			// IE issues: 'about:blank' fails on HTTPS and javascript:false is s-l-o-w
			// (hat tip to Jorge H. N. de Vasconcelos)
			/*jshint scripturl:true */
			iframeSrc: /^https/i.test(window.location.href || '') ? 'javascript:false' : 'about:blank',

			// force usage of iframe in non-IE browsers (handy for blocking applets)
			forceIframe: false,

			// z-index for the blocking overlay
			baseZ: 1000,

			// set these to true to have the message automatically centered
			centerX: true, // <-- only effects element blocking (page block controlled via css above)
			centerY: true,

			// allow body element to be stetched in ie6; this makes blocking look better
			// on "short" pages.  disable if you wish to prevent changes to the body height
			allowBodyStretch: true,

			// enable if you want key and mouse events to be disabled for content that is blocked
			bindEvents: true,

			// be default blockUI will supress tab navigation from leaving blocking content
			// (if bindEvents is true)
			constrainTabKey: true,

			// fadeIn time in millis; set to 0 to disable fadeIn on block
			fadeIn:  200,

			// fadeOut time in millis; set to 0 to disable fadeOut on unblock
			fadeOut:  400,

			// time in millis to wait before auto-unblocking; set to 0 to disable auto-unblock
			timeout: 0,

			// disable if you don't want to show the overlay
			showOverlay: true,

			// if true, focus will be placed in the first available input field when
			// page blocking
			focusInput: true,

            // elements that can receive focus
            focusableElements: ':input:enabled:visible',

			// suppresses the use of overlay styles on FF/Linux (due to performance issues with opacity)
			// no longer needed in 2012
			// applyPlatformOpacityRules: true,

			// callback method invoked when fadeIn has completed and blocking message is visible
			onBlock: null,

			// callback method invoked when unblocking has completed; the callback is
			// passed the element that has been unblocked (which is the window object for page
			// blocks) and the options that were passed to the unblock call:
			//	onUnblock(element, options)
			onUnblock: null,

			// callback method invoked when the overlay area is clicked.
			// setting this will turn the cursor to a pointer, otherwise cursor defined in overlayCss will be used.
			onOverlayClick: null,

			// don't ask; if you really must know: http://groups.google.com/group/jquery-en/browse_thread/thread/36640a8730503595/2f6a79a77a78e493#2f6a79a77a78e493
			quirksmodeOffsetHack: 4,

			// class name of the message block
			blockMsgClass: 'blockMsg',

			// if it is already blocked, then ignore it (don't unblock and reblock)
			ignoreIfBlocked: false
		};

		// private data and functions follow...

		var pageBlock = null;
		var pageBlockEls = [];

		function install(el, opts) {
			var css, themedCSS;
			var full = (el == window);
			var msg = (opts && opts.message !== undefined ? opts.message : undefined);
			opts = $.extend({}, $.blockUI.defaults, opts || {});

			if (opts.ignoreIfBlocked && $(el).data('blockUI.isBlocked'))
				return;

			opts.overlayCSS = $.extend({}, $.blockUI.defaults.overlayCSS, opts.overlayCSS || {});
			css = $.extend({}, $.blockUI.defaults.css, opts.css || {});
			if (opts.onOverlayClick)
				opts.overlayCSS.cursor = 'pointer';

			themedCSS = $.extend({}, $.blockUI.defaults.themedCSS, opts.themedCSS || {});
			msg = msg === undefined ? opts.message : msg;

			// remove the current block (if there is one)
			if (full && pageBlock)
				remove(window, {fadeOut:0});

			// if an existing element is being used as the blocking content then we capture
			// its current place in the DOM (and current display style) so we can restore
			// it when we unblock
			if (msg && typeof msg != 'string' && (msg.parentNode || msg.jquery)) {
				var node = msg.jquery ? msg[0] : msg;
				var data = {};
				$(el).data('blockUI.history', data);
				data.el = node;
				data.parent = node.parentNode;
				data.display = node.style.display;
				data.position = node.style.position;
				if (data.parent)
					data.parent.removeChild(node);
			}

			$(el).data('blockUI.onUnblock', opts.onUnblock);
			var z = opts.baseZ;

			// blockUI uses 3 layers for blocking, for simplicity they are all used on every platform;
			// layer1 is the iframe layer which is used to supress bleed through of underlying content
			// layer2 is the overlay layer which has opacity and a wait cursor (by default)
			// layer3 is the message content that is displayed while blocking
			var lyr1, lyr2, lyr3, s;
			if (msie || opts.forceIframe)
				lyr1 = $('<iframe class="blockUI" style="z-index:'+ (z++) +';display:none;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="'+opts.iframeSrc+'"></iframe>');
			else
				lyr1 = $('<div class="blockUI" style="display:none"></div>');

			if (opts.theme)
				lyr2 = $('<div class="blockUI blockOverlay ui-widget-overlay" style="z-index:'+ (z++) +';display:none"></div>');
			else
				lyr2 = $('<div class="blockUI blockOverlay" style="z-index:'+ (z++) +';display:none;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0"></div>');

			if (opts.theme && full) {
				s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage ui-dialog ui-widget ui-corner-all" style="z-index:'+(z+10)+';display:none;position:fixed">';
				if ( opts.title ) {
					s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">'+(opts.title || '&nbsp;')+'</div>';
				}
				s += '<div class="ui-widget-content ui-dialog-content"></div>';
				s += '</div>';
			}
			else if (opts.theme) {
				s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement ui-dialog ui-widget ui-corner-all" style="z-index:'+(z+10)+';display:none;position:absolute">';
				if ( opts.title ) {
					s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">'+(opts.title || '&nbsp;')+'</div>';
				}
				s += '<div class="ui-widget-content ui-dialog-content"></div>';
				s += '</div>';
			}
			else if (full) {
				s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage" style="z-index:'+(z+10)+';display:none;position:fixed"></div>';
			}
			else {
				s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement" style="z-index:'+(z+10)+';display:none;position:absolute"></div>';
			}
			lyr3 = $(s);

			// if we have a message, style it
			if (msg) {
				if (opts.theme) {
					lyr3.css(themedCSS);
					lyr3.addClass('ui-widget-content');
				}
				else
					lyr3.css(css);
			}

			// style the overlay
			if (!opts.theme /*&& (!opts.applyPlatformOpacityRules)*/)
				lyr2.css(opts.overlayCSS);
			lyr2.css('position', full ? 'fixed' : 'absolute');

			// make iframe layer transparent in IE
			if (msie || opts.forceIframe)
				lyr1.css('opacity',0.0);

			//$([lyr1[0],lyr2[0],lyr3[0]]).appendTo(full ? 'body' : el);
			var layers = [lyr1,lyr2,lyr3], $par = full ? $('body') : $(el);
			$.each(layers, function() {
				this.appendTo($par);
			});

			if (opts.theme && opts.draggable && $.fn.draggable) {
				lyr3.draggable({
					handle: '.ui-dialog-titlebar',
					cancel: 'li'
				});
			}

			// ie7 must use absolute positioning in quirks mode and to account for activex issues (when scrolling)
			var expr = setExpr && (!$.support.boxModel || $('object,embed', full ? null : el).length > 0);
			if (ie6 || expr) {
				// give body 100% height
				if (full && opts.allowBodyStretch && $.support.boxModel)
					$('html,body').css('height','100%');

				// fix ie6 issue when blocked element has a border width
				if ((ie6 || !$.support.boxModel) && !full) {
					var t = sz(el,'borderTopWidth'), l = sz(el,'borderLeftWidth');
					var fixT = t ? '(0 - '+t+')' : 0;
					var fixL = l ? '(0 - '+l+')' : 0;
				}

				// simulate fixed position
				$.each(layers, function(i,o) {
					var s = o[0].style;
					s.position = 'absolute';
					if (i < 2) {
						if (full)
							s.setExpression('height','Math.max(document.body.scrollHeight, document.body.offsetHeight) - (jQuery.support.boxModel?0:'+opts.quirksmodeOffsetHack+') + "px"');
						else
							s.setExpression('height','this.parentNode.offsetHeight + "px"');
						if (full)
							s.setExpression('width','jQuery.support.boxModel && document.documentElement.clientWidth || document.body.clientWidth + "px"');
						else
							s.setExpression('width','this.parentNode.offsetWidth + "px"');
						if (fixL) s.setExpression('left', fixL);
						if (fixT) s.setExpression('top', fixT);
					}
					else if (opts.centerY) {
						if (full) s.setExpression('top','(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"');
						s.marginTop = 0;
					}
					else if (!opts.centerY && full) {
						var top = (opts.css && opts.css.top) ? parseInt(opts.css.top, 10) : 0;
						var expression = '((document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + '+top+') + "px"';
						s.setExpression('top',expression);
					}
				});
			}

			// show the message
			if (msg) {
				if (opts.theme)
					lyr3.find('.ui-widget-content').append(msg);
				else
					lyr3.append(msg);
				if (msg.jquery || msg.nodeType)
					$(msg).show();
			}

			if ((msie || opts.forceIframe) && opts.showOverlay)
				lyr1.show(); // opacity is zero
			if (opts.fadeIn) {
				var cb = opts.onBlock ? opts.onBlock : noOp;
				var cb1 = (opts.showOverlay && !msg) ? cb : noOp;
				var cb2 = msg ? cb : noOp;
				if (opts.showOverlay)
					lyr2._fadeIn(opts.fadeIn, cb1);
				if (msg)
					lyr3._fadeIn(opts.fadeIn, cb2);
			}
			else {
				if (opts.showOverlay)
					lyr2.show();
				if (msg)
					lyr3.show();
				if (opts.onBlock)
					opts.onBlock();
			}

			// bind key and mouse events
			bind(1, el, opts);

			if (full) {
				pageBlock = lyr3[0];
				pageBlockEls = $(opts.focusableElements,pageBlock);
				if (opts.focusInput)
					setTimeout(focus, 20);
			}
			else
				center(lyr3[0], opts.centerX, opts.centerY);

			if (opts.timeout) {
				// auto-unblock
				var to = setTimeout(function() {
					if (full)
						$.unblockUI(opts);
					else
						$(el).unblock(opts);
				}, opts.timeout);
				$(el).data('blockUI.timeout', to);
			}
		}

		// remove the block
		function remove(el, opts) {
			var count;
			var full = (el == window);
			var $el = $(el);
			var data = $el.data('blockUI.history');
			var to = $el.data('blockUI.timeout');
			if (to) {
				clearTimeout(to);
				$el.removeData('blockUI.timeout');
			}
			opts = $.extend({}, $.blockUI.defaults, opts || {});
			bind(0, el, opts); // unbind events

			if (opts.onUnblock === null) {
				opts.onUnblock = $el.data('blockUI.onUnblock');
				$el.removeData('blockUI.onUnblock');
			}

			var els;
			if (full) // crazy selector to handle odd field errors in ie6/7
				els = $('body').children().filter('.blockUI').add('body > .blockUI');
			else
				els = $el.find('>.blockUI');

			// fix cursor issue
			if ( opts.cursorReset ) {
				if ( els.length > 1 )
					els[1].style.cursor = opts.cursorReset;
				if ( els.length > 2 )
					els[2].style.cursor = opts.cursorReset;
			}

			if (full)
				pageBlock = pageBlockEls = null;

			if (opts.fadeOut) {
				count = els.length;
				els.stop().fadeOut(opts.fadeOut, function() {
					if ( --count === 0)
						reset(els,data,opts,el);
				});
			}
			else
				reset(els, data, opts, el);
		}

		// move blocking element back into the DOM where it started
		function reset(els,data,opts,el) {
			var $el = $(el);
			if ( $el.data('blockUI.isBlocked') )
				return;

			els.each(function(i,o) {
				// remove via DOM calls so we don't lose event handlers
				if (this.parentNode)
					this.parentNode.removeChild(this);
			});

			if (data && data.el) {
				data.el.style.display = data.display;
				data.el.style.position = data.position;
				if (data.parent)
					data.parent.appendChild(data.el);
				$el.removeData('blockUI.history');
			}

			if ($el.data('blockUI.static')) {
				$el.css('position', 'static'); // #22
			}

			if (typeof opts.onUnblock == 'function')
				opts.onUnblock(el,opts);

			// fix issue in Safari 6 where block artifacts remain until reflow
			var body = $(document.body), w = body.width(), cssW = body[0].style.width;
			body.width(w-1).width(w);
			body[0].style.width = cssW;
		}

		// bind/unbind the handler
		function bind(b, el, opts) {
			var full = el == window, $el = $(el);

			// don't bother unbinding if there is nothing to unbind
			if (!b && (full && !pageBlock || !full && !$el.data('blockUI.isBlocked')))
				return;

			$el.data('blockUI.isBlocked', b);

			// don't bind events when overlay is not in use or if bindEvents is false
			if (!full || !opts.bindEvents || (b && !opts.showOverlay))
				return;

			// bind anchors and inputs for mouse and key events
			var events = 'mousedown mouseup keydown keypress keyup touchstart touchend touchmove';
			if (b)
				$(document).bind(events, opts, handler);
			else
				$(document).unbind(events, handler);

		// former impl...
		//		var $e = $('a,:input');
		//		b ? $e.bind(events, opts, handler) : $e.unbind(events, handler);
		}

		// event handler to suppress keyboard/mouse events when blocking
		function handler(e) {
			// allow tab navigation (conditionally)
			if (e.type === 'keydown' && e.keyCode && e.keyCode == 9) {
				if (pageBlock && e.data.constrainTabKey) {
					var els = pageBlockEls;
					var fwd = !e.shiftKey && e.target === els[els.length-1];
					var back = e.shiftKey && e.target === els[0];
					if (fwd || back) {
						setTimeout(function(){focus(back);},10);
						return false;
					}
				}
			}
			var opts = e.data;
			var target = $(e.target);
			if (target.hasClass('blockOverlay') && opts.onOverlayClick)
				opts.onOverlayClick();

			// allow events within the message content
			if (target.parents('div.' + opts.blockMsgClass).length > 0)
				return true;

			// allow events for content that is not being blocked
			return target.parents().children().filter('div.blockUI').length === 0;
		}

		function focus(back) {
			if (!pageBlockEls)
				return;
			var e = pageBlockEls[back===true ? pageBlockEls.length-1 : 0];
			if (e)
				e.focus();
		}

		function center(el, x, y) {
			var p = el.parentNode, s = el.style;
			var l = ((p.offsetWidth - el.offsetWidth)/2) - sz(p,'borderLeftWidth');
			var t = ((p.offsetHeight - el.offsetHeight)/2) - sz(p,'borderTopWidth');
			if (x) s.left = l > 0 ? (l+'px') : '0';
			if (y) s.top  = t > 0 ? (t+'px') : '0';
		}

		function sz(el, p) {
			return parseInt($.css(el,p),10)||0;
		}

	}


	/*global define:true */
	if (typeof define === 'function' && define.amd && define.amd.jQuery) {
		define(['jquery'], setup);
	} else {
		setup(jQuery);
	}

})();

/*
 * Copyright (c) 2009 Nicholas C. Zakas
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

( function( $ ) {

$.idleTimer = function( firstParam, elem, opts ) {

	// defaults that are to be stored as instance props on the elem
	opts = $.extend( {
		startImmediately: true,   //starts a timeout as soon as the timer is set up
		idle: false,              //indicates if the user is idle
		enabled: true,            //indicates if the idle timer is enabled
		timeout: 30000,           //the amount of time (ms) before the user is considered idle
		events: "mousemove keydown DOMMouseScroll mousewheel mousedown touchstart touchmove" // activity is one of these events
	}, opts );


	elem = elem || document;

	var jqElem = $( elem ),
		obj = jqElem.data("idleTimerObj") || {},

		/* (intentionally not documented)
		 * Toggles the idle state and fires an appropriate event.
		 * @return {void}
		 */
		toggleIdleState = function( myelem ) {

			// curse you, mozilla setTimeout lateness bug!
			if ( typeof myelem === "number" ) {
				myelem = undefined;
			}

			var obj = $.data( myelem || elem, "idleTimerObj" );

			//toggle the state
			obj.idle = !obj.idle;

			// reset timeout
			var elapsed = ( +new Date() ) - obj.olddate;
			obj.olddate = +new Date();

			// handle Chrome always triggering idle after js alert or comfirm popup
			if ( obj.idle && ( elapsed < opts.timeout ) ) {
				obj.idle = false;
				clearTimeout( $.idleTimer.tId );
				if ( opts.enabled ) {
					$.idleTimer.tId = setTimeout( toggleIdleState, opts.timeout );
				}
				return;
			}

			// create a custom event, but first, store the new state on the element
			// and then append that string to a namespace
			var event = $.Event( $.data( elem, "idleTimer", obj.idle ? "idle" : "active" ) + ".idleTimer" );
			$( elem ).trigger( event );
		},

		/**
		 * Stops the idle timer. This removes appropriate event handlers
		 * and cancels any pending timeouts.
		 * @return {void}
		 * @method stop
		 * @static
		 */
		stop = function( jqElem ) {

			var obj = jqElem.data("idleTimerObj") || {};

			//set to disabled
			obj.enabled = false;

			//clear any pending timeouts
			clearTimeout( obj.tId );

			//detach the event handlers
			jqElem.off(".idleTimer");
		};

	obj.olddate = obj.olddate || +new Date();

	if ( typeof firstParam === "number" ) {
		opts.timeout = firstParam;
	} else if ( firstParam === "destroy" ) {
		stop( jqElem );
		return this;
	} else if ( firstParam === "getElapsedTime" ) {
		return ( +new Date() ) - obj.olddate;
	}


	/* (intentionally not documented)
	 * Handles a user event indicating that the user isn't idle.
	 * @param {Event} event A DOM2-normalized event object.
	 * @return {void}
	 */
	jqElem.on( $.trim( ( opts.events + " " ).split(" ").join(".idleTimer ") ), function() {
		var obj = $.data( this, "idleTimerObj" );

		//clear any existing timeout
		clearTimeout( obj.tId );

		//if the idle timer is enabled
		if ( obj.enabled ){
			//if it's idle, that means the user is no longer idle
			if ( obj.idle ){
				toggleIdleState( this );
			}

			//set a new timeout
			obj.tId = setTimeout( toggleIdleState, obj.timeout );
		}
	});

	obj.idle = opts.idle;
	obj.enabled = opts.enabled;
	obj.timeout = opts.timeout;

	//set a timeout to toggle state. May wish to omit this in some situations
	if ( opts.startImmediately ) {
		obj.tId = setTimeout( toggleIdleState, obj.timeout );
	}

	// assume the user is active for the first x seconds.
	jqElem.data( "idleTimer", "active" );

	// store our instance on the object
	jqElem.data( "idleTimerObj", obj );
};

$.fn.idleTimer = function( firstParam, opts ) {
	// Allow omission of opts for backward compatibility
	if ( !opts ) {
		opts = {};
	}

	if ( this[0] ){
		$.idleTimer( firstParam, this[0], opts );
	}

	return this;
};

})( jQuery );

// Generated by CoffeeScript 1.6.2
/*
jQuery Waypoints - v2.0.3
Copyright (c) 2011-2013 Caleb Troughton
Dual licensed under the MIT license and GPL license.
https://github.com/imakewebthings/jquery-waypoints/blob/master/licenses.txt
*/


(function() {
  var __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; },
    __slice = [].slice;

  (function(root, factory) {
    if (typeof define === 'function' && define.amd) {
      return define('waypoints', ['jquery'], function($) {
        return factory($, root);
      });
    } else {
      return factory(root.jQuery, root);
    }
  })(this, function($, window) {
    var $w, Context, Waypoint, allWaypoints, contextCounter, contextKey, contexts, isTouch, jQMethods, methods, resizeEvent, scrollEvent, waypointCounter, waypointKey, wp, wps;

    $w = $(window);
    isTouch = __indexOf.call(window, 'ontouchstart') >= 0;
    allWaypoints = {
      horizontal: {},
      vertical: {}
    };
    contextCounter = 1;
    contexts = {};
    contextKey = 'waypoints-context-id';
    resizeEvent = 'resize.waypoints';
    scrollEvent = 'scroll.waypoints';
    waypointCounter = 1;
    waypointKey = 'waypoints-waypoint-ids';
    wp = 'waypoint';
    wps = 'waypoints';
    Context = (function() {
      function Context($element) {
        var _this = this;

        this.$element = $element;
        this.element = $element[0];
        this.didResize = false;
        this.didScroll = false;
        this.id = 'context' + contextCounter++;
        this.oldScroll = {
          x: $element.scrollLeft(),
          y: $element.scrollTop()
        };
        this.waypoints = {
          horizontal: {},
          vertical: {}
        };
        $element.data(contextKey, this.id);
        contexts[this.id] = this;
        $element.bind(scrollEvent, function() {
          var scrollHandler;

          if (!(_this.didScroll || isTouch)) {
            _this.didScroll = true;
            scrollHandler = function() {
              _this.doScroll();
              return _this.didScroll = false;
            };
            return window.setTimeout(scrollHandler, $[wps].settings.scrollThrottle);
          }
        });
        $element.bind(resizeEvent, function() {
          var resizeHandler;

          if (!_this.didResize) {
            _this.didResize = true;
            resizeHandler = function() {
              $[wps]('refresh');
              return _this.didResize = false;
            };
            return window.setTimeout(resizeHandler, $[wps].settings.resizeThrottle);
          }
        });
      }

      Context.prototype.doScroll = function() {
        var axes,
          _this = this;

        axes = {
          horizontal: {
            newScroll: this.$element.scrollLeft(),
            oldScroll: this.oldScroll.x,
            forward: 'right',
            backward: 'left'
          },
          vertical: {
            newScroll: this.$element.scrollTop(),
            oldScroll: this.oldScroll.y,
            forward: 'down',
            backward: 'up'
          }
        };
        if (isTouch && (!axes.vertical.oldScroll || !axes.vertical.newScroll)) {
          $[wps]('refresh');
        }
        $.each(axes, function(aKey, axis) {
          var direction, isForward, triggered;

          triggered = [];
          isForward = axis.newScroll > axis.oldScroll;
          direction = isForward ? axis.forward : axis.backward;
          $.each(_this.waypoints[aKey], function(wKey, waypoint) {
            var _ref, _ref1;

            if ((axis.oldScroll < (_ref = waypoint.offset) && _ref <= axis.newScroll)) {
              return triggered.push(waypoint);
            } else if ((axis.newScroll < (_ref1 = waypoint.offset) && _ref1 <= axis.oldScroll)) {
              return triggered.push(waypoint);
            }
          });
          triggered.sort(function(a, b) {
            return a.offset - b.offset;
          });
          if (!isForward) {
            triggered.reverse();
          }
          return $.each(triggered, function(i, waypoint) {
            if (waypoint.options.continuous || i === triggered.length - 1) {
              return waypoint.trigger([direction]);
            }
          });
        });
        return this.oldScroll = {
          x: axes.horizontal.newScroll,
          y: axes.vertical.newScroll
        };
      };

      Context.prototype.refresh = function() {
        var axes, cOffset, isWin,
          _this = this;

        isWin = $.isWindow(this.element);
        cOffset = this.$element.offset();
        this.doScroll();
        axes = {
          horizontal: {
            contextOffset: isWin ? 0 : cOffset.left,
            contextScroll: isWin ? 0 : this.oldScroll.x,
            contextDimension: this.$element.width(),
            oldScroll: this.oldScroll.x,
            forward: 'right',
            backward: 'left',
            offsetProp: 'left'
          },
          vertical: {
            contextOffset: isWin ? 0 : cOffset.top,
            contextScroll: isWin ? 0 : this.oldScroll.y,
            contextDimension: isWin ? $[wps]('viewportHeight') : this.$element.height(),
            oldScroll: this.oldScroll.y,
            forward: 'down',
            backward: 'up',
            offsetProp: 'top'
          }
        };
        return $.each(axes, function(aKey, axis) {
          return $.each(_this.waypoints[aKey], function(i, waypoint) {
            var adjustment, elementOffset, oldOffset, _ref, _ref1;

            adjustment = waypoint.options.offset;
            oldOffset = waypoint.offset;
            elementOffset = $.isWindow(waypoint.element) ? 0 : waypoint.$element.offset()[axis.offsetProp];
            if ($.isFunction(adjustment)) {
              adjustment = adjustment.apply(waypoint.element);
            } else if (typeof adjustment === 'string') {
              adjustment = parseFloat(adjustment);
              if (waypoint.options.offset.indexOf('%') > -1) {
                adjustment = Math.ceil(axis.contextDimension * adjustment / 100);
              }
            }
            waypoint.offset = elementOffset - axis.contextOffset + axis.contextScroll - adjustment;
            if ((waypoint.options.onlyOnScroll && (oldOffset != null)) || !waypoint.enabled) {
              return;
            }
            if (oldOffset !== null && (oldOffset < (_ref = axis.oldScroll) && _ref <= waypoint.offset)) {
              return waypoint.trigger([axis.backward]);
            } else if (oldOffset !== null && (oldOffset > (_ref1 = axis.oldScroll) && _ref1 >= waypoint.offset)) {
              return waypoint.trigger([axis.forward]);
            } else if (oldOffset === null && axis.oldScroll >= waypoint.offset) {
              return waypoint.trigger([axis.forward]);
            }
          });
        });
      };

      Context.prototype.checkEmpty = function() {
        if ($.isEmptyObject(this.waypoints.horizontal) && $.isEmptyObject(this.waypoints.vertical)) {
          this.$element.unbind([resizeEvent, scrollEvent].join(' '));
          return delete contexts[this.id];
        }
      };

      return Context;

    })();
    Waypoint = (function() {
      function Waypoint($element, context, options) {
        var idList, _ref;

        options = $.extend({}, $.fn[wp].defaults, options);
        if (options.offset === 'bottom-in-view') {
          options.offset = function() {
            var contextHeight;

            contextHeight = $[wps]('viewportHeight');
            if (!$.isWindow(context.element)) {
              contextHeight = context.$element.height();
            }
            return contextHeight - $(this).outerHeight();
          };
        }
        this.$element = $element;
        this.element = $element[0];
        this.axis = options.horizontal ? 'horizontal' : 'vertical';
        this.callback = options.handler;
        this.context = context;
        this.enabled = options.enabled;
        this.id = 'waypoints' + waypointCounter++;
        this.offset = null;
        this.options = options;
        context.waypoints[this.axis][this.id] = this;
        allWaypoints[this.axis][this.id] = this;
        idList = (_ref = $element.data(waypointKey)) != null ? _ref : [];
        idList.push(this.id);
        $element.data(waypointKey, idList);
      }

      Waypoint.prototype.trigger = function(args) {
        if (!this.enabled) {
          return;
        }
        if (this.callback != null) {
          this.callback.apply(this.element, args);
        }
        if (this.options.triggerOnce) {
          return this.destroy();
        }
      };

      Waypoint.prototype.disable = function() {
        return this.enabled = false;
      };

      Waypoint.prototype.enable = function() {
        this.context.refresh();
        return this.enabled = true;
      };

      Waypoint.prototype.destroy = function() {
        delete allWaypoints[this.axis][this.id];
        delete this.context.waypoints[this.axis][this.id];
        return this.context.checkEmpty();
      };

      Waypoint.getWaypointsByElement = function(element) {
        var all, ids;

        ids = $(element).data(waypointKey);
        if (!ids) {
          return [];
        }
        all = $.extend({}, allWaypoints.horizontal, allWaypoints.vertical);
        return $.map(ids, function(id) {
          return all[id];
        });
      };

      return Waypoint;

    })();
    methods = {
      init: function(f, options) {
        var _ref;

        if (options == null) {
          options = {};
        }
        if ((_ref = options.handler) == null) {
          options.handler = f;
        }
        this.each(function() {
          var $this, context, contextElement, _ref1;

          $this = $(this);
          contextElement = (_ref1 = options.context) != null ? _ref1 : $.fn[wp].defaults.context;
          if (!$.isWindow(contextElement)) {
            contextElement = $this.closest(contextElement);
          }
          contextElement = $(contextElement);
          context = contexts[contextElement.data(contextKey)];
          if (!context) {
            context = new Context(contextElement);
          }
          return new Waypoint($this, context, options);
        });
        $[wps]('refresh');
        return this;
      },
      disable: function() {
        return methods._invoke(this, 'disable');
      },
      enable: function() {
        return methods._invoke(this, 'enable');
      },
      destroy: function() {
        return methods._invoke(this, 'destroy');
      },
      prev: function(axis, selector) {
        return methods._traverse.call(this, axis, selector, function(stack, index, waypoints) {
          if (index > 0) {
            return stack.push(waypoints[index - 1]);
          }
        });
      },
      next: function(axis, selector) {
        return methods._traverse.call(this, axis, selector, function(stack, index, waypoints) {
          if (index < waypoints.length - 1) {
            return stack.push(waypoints[index + 1]);
          }
        });
      },
      _traverse: function(axis, selector, push) {
        var stack, waypoints;

        if (axis == null) {
          axis = 'vertical';
        }
        if (selector == null) {
          selector = window;
        }
        waypoints = jQMethods.aggregate(selector);
        stack = [];
        this.each(function() {
          var index;

          index = $.inArray(this, waypoints[axis]);
          return push(stack, index, waypoints[axis]);
        });
        return this.pushStack(stack);
      },
      _invoke: function($elements, method) {
        $elements.each(function() {
          var waypoints;

          waypoints = Waypoint.getWaypointsByElement(this);
          return $.each(waypoints, function(i, waypoint) {
            waypoint[method]();
            return true;
          });
        });
        return this;
      }
    };
    $.fn[wp] = function() {
      var args, method;

      method = arguments[0], args = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
      if (methods[method]) {
        return methods[method].apply(this, args);
      } else if ($.isFunction(method)) {
        return methods.init.apply(this, arguments);
      } else if ($.isPlainObject(method)) {
        return methods.init.apply(this, [null, method]);
      } else if (!method) {
        return $.error("jQuery Waypoints needs a callback function or handler option.");
      } else {
        return $.error("The " + method + " method does not exist in jQuery Waypoints.");
      }
    };
    $.fn[wp].defaults = {
      context: window,
      continuous: true,
      enabled: true,
      horizontal: false,
      offset: 0,
      triggerOnce: false
    };
    jQMethods = {
      refresh: function() {
        return $.each(contexts, function(i, context) {
          return context.refresh();
        });
      },
      viewportHeight: function() {
        var _ref;

        return (_ref = window.innerHeight) != null ? _ref : $w.height();
      },
      aggregate: function(contextSelector) {
        var collection, waypoints, _ref;

        collection = allWaypoints;
        if (contextSelector) {
          collection = (_ref = contexts[$(contextSelector).data(contextKey)]) != null ? _ref.waypoints : void 0;
        }
        if (!collection) {
          return [];
        }
        waypoints = {
          horizontal: [],
          vertical: []
        };
        $.each(waypoints, function(axis, arr) {
          $.each(collection[axis], function(key, waypoint) {
            return arr.push(waypoint);
          });
          arr.sort(function(a, b) {
            return a.offset - b.offset;
          });
          waypoints[axis] = $.map(arr, function(waypoint) {
            return waypoint.element;
          });
          return waypoints[axis] = $.unique(waypoints[axis]);
        });
        return waypoints;
      },
      above: function(contextSelector) {
        if (contextSelector == null) {
          contextSelector = window;
        }
        return jQMethods._filter(contextSelector, 'vertical', function(context, waypoint) {
          return waypoint.offset <= context.oldScroll.y;
        });
      },
      below: function(contextSelector) {
        if (contextSelector == null) {
          contextSelector = window;
        }
        return jQMethods._filter(contextSelector, 'vertical', function(context, waypoint) {
          return waypoint.offset > context.oldScroll.y;
        });
      },
      left: function(contextSelector) {
        if (contextSelector == null) {
          contextSelector = window;
        }
        return jQMethods._filter(contextSelector, 'horizontal', function(context, waypoint) {
          return waypoint.offset <= context.oldScroll.x;
        });
      },
      right: function(contextSelector) {
        if (contextSelector == null) {
          contextSelector = window;
        }
        return jQMethods._filter(contextSelector, 'horizontal', function(context, waypoint) {
          return waypoint.offset > context.oldScroll.x;
        });
      },
      enable: function() {
        return jQMethods._invoke('enable');
      },
      disable: function() {
        return jQMethods._invoke('disable');
      },
      destroy: function() {
        return jQMethods._invoke('destroy');
      },
      extendFn: function(methodName, f) {
        return methods[methodName] = f;
      },
      _invoke: function(method) {
        var waypoints;

        waypoints = $.extend({}, allWaypoints.vertical, allWaypoints.horizontal);
        return $.each(waypoints, function(key, waypoint) {
          waypoint[method]();
          return true;
        });
      },
      _filter: function(selector, axis, test) {
        var context, waypoints;

        context = contexts[$(selector).data(contextKey)];
        if (!context) {
          return [];
        }
        waypoints = [];
        $.each(context.waypoints[axis], function(i, waypoint) {
          if (test(context, waypoint)) {
            return waypoints.push(waypoint);
          }
        });
        waypoints.sort(function(a, b) {
          return a.offset - b.offset;
        });
        return $.map(waypoints, function(waypoint) {
          return waypoint.element;
        });
      }
    };
    $[wps] = function() {
      var args, method;

      method = arguments[0], args = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
      if (jQMethods[method]) {
        return jQMethods[method].apply(null, args);
      } else {
        return jQMethods.aggregate.call(null, method);
      }
    };
    $[wps].settings = {
      resizeThrottle: 100,
      scrollThrottle: 30
    };
    return $w.load(function() {
      return $[wps]('refresh');
    });
  });

}).call(this);

if (!window["WPAC"]) var WPAC = {};
WPAC._Options = WPAC._Options || {}; 

WPAC._BodyRegex = new RegExp("<body[^>]*>((.|\n|\r)*)</body>", "i");
WPAC._ExtractBody = function(html) {
	try {
		return jQuery("<div>"+WPAC._BodyRegex.exec(html)[1]+"</div>");
	} catch (e) {
		return false;
	}
}

WPAC._TitleRegex = new RegExp("<title[^>]*>(.*?)<\\/title>", "im");
WPAC._ExtractTitle = function(html) {
	try {
		return WPAC._TitleRegex.exec(html)[1];
	} catch (e) {
		return false;
	}
}

WPAC._ShowMessage = function (message, type) {

	var top = WPAC._Options.popupMarginTop + jQuery("#wpadminbar").outerHeight();

	var backgroundColor = WPAC._Options.popupBackgroundColorLoading;
	var textColor = WPAC._Options.popupTextColorLoading;
	if (type == "error") {
		backgroundColor = WPAC._Options.popupBackgroundColorError;
		textColor = WPAC._Options.popupTextColorError;
	} else if (type == "success") {
		backgroundColor = WPAC._Options.popupBackgroundColorSuccess;
		textColor = WPAC._Options.popupTextColorSuccess;
	}
	
	jQuery.blockUI({ 
		message: message, 
		fadeIn: WPAC._Options.popupFadeIn, 
		fadeOut: WPAC._Options.popupFadeOut, 
		timeout:(type == "loading") ? 0 : WPAC._Options.popupTimeout,
		centerY: false,
		centerX: true,
		showOverlay: (type == "loading"),
		css: { 
			width: WPAC._Options.popupWidth + "%",
			left: ((100-WPAC._Options.popupWidth)/2) + "%",
			top: top + "px",
			border: "none", 
			padding: WPAC._Options.popupPadding + "px", 
			backgroundColor: backgroundColor, 
			"-webkit-border-radius": WPAC._Options.popupCornerRadius + "px",
			"-moz-border-radius": WPAC._Options.popupCornerRadius + "px",
			"border-radius": WPAC._Options.popupCornerRadius + "px",
			opacity: WPAC._Options.popupOpacity/100, 
			color: textColor,
			textAlign: WPAC._Options.popupTextAlign,
			cursor: (type == "loading") ? "wait" : "default",
			"font-size": WPAC._Options.popupTextFontSize
		},
		overlayCSS:  { 
			backgroundColor: "#000", 
			opacity: 0
		},
		baseZ: WPAC._Options.popupZindex
	}); 
	
}

WPAC._DebugErrorShown = false;
WPAC._Debug = function(level, message) {

	if (!WPAC._Options.debug) return;

	// Fix console.log.apply for IE9
	// see http://stackoverflow.com/a/5539378/516472
	if (Function.prototype.call && Function.prototype.call.bind && typeof window["console"] != "undefined" && console && typeof console.log == "object" && typeof window["console"][level].apply === "undefined") {
		console[level] = Function.prototype.call.bind(console[level], console);
	}

	if (typeof window["console"] === "undefined" || typeof window["console"][level] === "undefined" || typeof window["console"][level].apply === "undefined") {
		if (!WPAC._DebugErrorShown) alert("Unfortunately the console object is undefined or is not supported in your browser, debugging WP Ajaxify Comments is disabled! Please use Firebug, Google Chrome or Internet Explorer 9 or above with enabled Developer Tools (F12) for debugging WP Ajaxify Comments.");
		WPAC._DebugErrorShown = true;
		return;
	}

	var args = jQuery.merge(["[WP Ajaxify Comments] " + message], jQuery.makeArray(arguments).slice(2));
	console[level].apply(console, args);
}

WPAC._DebugSelector = function(elementType, selector, optional) {
	if (!WPAC._Options.debug) return;

	var element = jQuery(selector);
	if (!element.length) {
		WPAC._Debug(optional ? "info" : "error", "Search %s (selector: '%s')... Not found", elementType, selector);
	} else {
		WPAC._Debug("info", "Search %s (selector: '%s')... Found: %o", elementType, selector, element);
	}
}

WPAC._AddQueryParamStringToUrl = function(url, param, value) {
	return new Uri(url).replaceQueryParam(param, value).toString();
}

WPAC._LoadFallbackUrl = function(fallbackUrl) {

	WPAC._ShowMessage(WPAC._Options.textReloadPage, "loading");
	
	var url = WPAC._AddQueryParamStringToUrl(fallbackUrl, "WPACRandom", (new Date()).getTime());
	WPAC._Debug("info", "Something went wrong. Reloading page (URL: '%s')...", url);
	
	var reload = function() { location.href = url; };
	if (!WPAC._Options.debug) {
		reload();
	} else {
		WPAC._Debug("info", "Sleep for 5s to enable analyzing debug messages...");
		window.setTimeout(reload, 5000);
	}
}

WPAC._ScrollToAnchor = function(anchor, updateHash, scrollComplete) {
	scrollComplete = scrollComplete || function() {};
	var anchorElement = jQuery(anchor)
	if (anchorElement.length) {
		WPAC._Debug("info", "Scroll to anchor element %o (scroll speed: %s ms)...", anchorElement, WPAC._Options.scrollSpeed);
		var animateComplete = function() {
			if (updateHash) window.location.hash = anchor; 
			scrollComplete();
		}
		var scrollTargetTopOffset = anchorElement.offset().top
		if (jQuery(window).scrollTop() == scrollTargetTopOffset) {
			animateComplete();
		} else {
			jQuery("html,body").animate({scrollTop: scrollTargetTopOffset}, {
				duration: WPAC._Options.scrollSpeed,
				complete: animateComplete
			});
		}
		return true;
	} else {
		WPAC._Debug("error", "Anchor element not found (selector: '%s')", anchor);
		return false;
	}
}

WPAC._UpdateUrl= function(url) {
	if (url.split("#")[0] == window.location.href.split("#")[0]) {
		return;
	}
	if (window.history.replaceState) {
		window.history.replaceState({}, window.document.title, url);
	} else {
		WPAC._Debug("info", "Browser does not support window.history.replaceState() to update the URL without reloading the page", anchor);
	}
}

WPAC._ReplaceComments = function(data, commentUrl, useFallbackUrl, formData, formFocus, selectorCommentsContainer, selectorCommentForm, selectorRespondContainer, beforeSelectElements, beforeUpdateComments, afterUpdateComments) {
	
	var fallbackUrl = useFallbackUrl ? WPAC._AddQueryParamStringToUrl(commentUrl, "WPACFallback", "1") : commentUrl;
	
	var oldCommentsContainer = jQuery(selectorCommentsContainer);
	if (!oldCommentsContainer.length) {
		WPAC._Debug("error", "Comment container on current page not found (selector: '%s')", selectorCommentsContainer);
		WPAC._LoadFallbackUrl(fallbackUrl);
		return false;
	}
	
	var extractedBody = WPAC._ExtractBody(data);
	if (extractedBody === false) {
		WPAC._Debug("error", "Unsupported server response, unable to extract body (data: '%s')", data);
		WPAC._LoadFallbackUrl(fallbackUrl);
		return false;
	}
	
	beforeSelectElements(extractedBody);
	
	var newCommentsContainer = extractedBody.find(selectorCommentsContainer);
	if (!newCommentsContainer.length) {
		WPAC._Debug("error", "Comment container on requested page not found (selector: '%s')", selectorCommentsContainer);
		WPAC._LoadFallbackUrl(fallbackUrl);
		return false;
	}

	beforeUpdateComments(extractedBody, commentUrl);

	// Update title
	var extractedTitle = WPAC._ExtractTitle(data);
	if (extractedBody !== false) 
		// Decode HTML entities (see http://stackoverflow.com/a/5796744)
		document.title = jQuery('<textarea />').html(extractedTitle).text(); 
	
	// Update comments container
	oldCommentsContainer.replaceWith(newCommentsContainer);
	
	if (WPAC._Options.commentsEnabled) {
	
		var form = jQuery(selectorCommentForm);
		if (form.length) {
	
			// Replace comment form (for spam protection plugin compatibility) if comment form is not nested in comments container
			// If comment form is nested in comments container comment form has already been replaced
			if (!form.parents(selectorCommentsContainer).length) {
	
				WPAC._Debug("info", "Replace comment form...");
				var newCommentForm = extractedBody.find(selectorCommentForm);
				if (newCommentForm.length == 0) {
					WPAC._Debug("error", "Comment form on requested page not found (selector: '%s')", selectorCommentForm);
					WPAC._LoadFallbackUrl(fallbackUrl);
					return false;
				}
				form.replaceWith(newCommentForm);
			}
			
		} else {
	
			WPAC._Debug("info", "Try to re-inject comment form...");
		
			// "Re-inject" comment form, if comment form was removed by updating the comments container; could happen 
			// if theme support threaded/nested comments and form tag is not nested in comments container
			// -> Replace WordPress placeholder <div> (#wp-temp-form-div) with respond <div>
			var wpTempFormDiv = jQuery("#wp-temp-form-div");
			if (!wpTempFormDiv.length) {
				WPAC._Debug("error", "WordPress' #wp-temp-form-div container not found", selectorRespondContainer);
				WPAC._LoadFallbackUrl(fallbackUrl);
				return false;
			}
			var newRespondContainer = extractedBody.find(selectorRespondContainer);
			if (!newRespondContainer.length) {
				WPAC._Debug("error", "Respond container on requested page not found (selector: '%s')", selectorRespondContainer);
				WPAC._LoadFallbackUrl(fallbackUrl);
				return false;
			}
			wpTempFormDiv.replaceWith(newRespondContainer);
	
		}
	
		if (formData) {
			// Re-inject saved form data
			jQuery.each(formData, function(key, value) {
				var formElement = jQuery("[name='"+value.name+"']", selectorCommentForm);
				if (formElement.length != 1 || formElement.val()) return;
				formElement.val(value.value);
			});
		}
		if (formFocus) {
			// Reset focus
			var formElement = jQuery("[name='"+formFocus+"']", selectorCommentForm);
			if (formElement) formElement.focus();
		}

	}
		
	afterUpdateComments(extractedBody, commentUrl);

	return true;
}

WPAC._TestCrossDomainScripting = function(url) {
	if (url.indexOf("http") != 0) return false;
	var domain = window.location.protocol + "//" + window.location.host;
	return (url.indexOf(domain) != 0);
}

WPAC._TestFallbackUrl = function(url) {
	var url = new Uri(location.href); 
	return (url.getQueryParamValue("WPACFallback") && url.getQueryParamValue("WPACRandom"));
}

WPAC.AttachForm = function(options) {

	// Set default options
	options = jQuery.extend({
		selectorCommentForm: WPAC._Options.selectorCommentForm,
		selectorCommentPagingLinks: WPAC._Options.selectorCommentPagingLinks,
		beforeSelectElements: WPAC._Callbacks.beforeSelectElements,
		beforeSubmitComment: WPAC._Callbacks.beforeSubmitComment,
		afterPostComment: WPAC._Callbacks.afterPostComment,
		selectorCommentsContainer: WPAC._Options.selectorCommentsContainer,
		selectorRespondContainer: WPAC._Options.selectorRespondContainer,
		beforeUpdateComments: WPAC._Callbacks.beforeUpdateComments,
		afterUpdateComments: WPAC._Callbacks.afterUpdateComments,
		scrollToAnchor: !WPAC._Options.disableScrollToAnchor,
		updateUrl: !WPAC._Options.disableUrlUpdate,
		selectorCommentLinks: WPAC._Options.selectorCommentLinks
	}, options || {});	

	if (WPAC._Options.debug && WPAC._Options.commentsEnabled) {
		WPAC._Debug("info", "Attach form...")
		WPAC._DebugSelector("comment form", options.selectorCommentForm);
		WPAC._DebugSelector("comments container",options.selectorCommentsContainer);
		WPAC._DebugSelector("respond container", options.selectorRespondContainer)
		WPAC._DebugSelector("comment paging links", options.selectorCommentPagingLinks, true);
		WPAC._DebugSelector("comment links", options.selectorCommentLinks, true);
	}
	
	options.beforeSelectElements(jQuery(document));
	
	// Get addHandler method
	if (jQuery(document).on) {
		// jQuery 1.7+
		var addHandler = function(event, selector, handler) {
			jQuery(document).on(event, selector, handler)
		}
	} else if (jQuery(document).delegate) {
		// jQuery 1.4.3+
		var addHandler = function(event, selector, handler) {
			jQuery(document).delegate(selector, event, handler)
		}
	} else {
		// jQuery 1.3+
		var addHandler = function(event, selector, handler) {
			jQuery(selector).live(event, handler)
		}
	}

	// Handle paging link clicks
	var pagingClickHandler = function(event) {
		var href = jQuery(this).attr("href");
		if (href) {
			event.preventDefault();
			WPAC.LoadComments(href, {
				selectorCommentForm: options.selectorCommentForm,
				selectorCommentsContainer: options.selectorCommentsContainer,
				selectorRespondContainer: options.selectorRespondContainer,
				beforeSelectElements: options.beforeSelectElements,
				beforeUpdateComments: options.beforeUpdateComments,
				afterUpdateComments: options.afterUpdateComments
			});
		}
	};
	addHandler("click", options.selectorCommentPagingLinks, pagingClickHandler);
	
	// Handle comment link clicks
	var linkClickHandler = function(event) {
		var element = jQuery(this);
		if (element.is(options.selectorCommentPagingLinks)) return; // skip if paging link was clicked 
		var href = element.attr("href");
		var anchor = "#" + (new Uri(href)).anchor();
		if (jQuery(anchor).length > 0) {
			if (options.updateUrl) WPAC._UpdateUrl(href);
			WPAC._ScrollToAnchor(anchor, options.updateUrl);
			event.preventDefault();
		}
	};
	addHandler("click", options.selectorCommentLinks, linkClickHandler);
	
	if (!WPAC._Options.commentsEnabled) return;
	
	// Handle form submit
	var formSubmitHandler = function (event) {
		var form = jQuery(this);

		options.beforeSubmitComment();

		var submitUrl = form.attr("action");

		// Cancel AJAX request if cross-domain scripting is detected
		if (WPAC._TestCrossDomainScripting(submitUrl)) {
			if (WPAC._Options.debug && !form.data("submitCrossDomain")) {
				WPAC._Debug("error", "Cross-domain scripting detected (submit url: '%s'), cancel AJAX request", submitUrl);
				WPAC._Debug("info", "Sleep for 5s to enable analyzing debug messages...");
				event.preventDefault();
				form.data("submitCrossDomain", true)
				window.setTimeout(function() { jQuery('#submit', form).remove(); form.submit(); }, 5000);
			}
			return;
		}
		
		// Stop default event handling
		event.preventDefault();
		
		// Test if form is already submitting
		if (form.data("WPAC_SUBMITTING")) {
			WPAC._Debug("info", "Cancel submit, form is already submitting (Form: %o)", form);
			return;
		}
		form.data("WPAC_SUBMITTING", true);

		// Show loading info
		WPAC._ShowMessage(WPAC._Options.textPostComment, "loading");

		var handleErrorResponse = function(data) {

			WPAC._Debug("info", "Comment has not been posted");
			WPAC._Debug("info", "Try to extract error message (selector: '%s')...", WPAC._Options.selectorErrorContainer);
			
			// Extract error message
			var extractedBody = WPAC._ExtractBody(data);
			if (extractedBody !== false) {
				var errorMessage = extractedBody.find(WPAC._Options.selectorErrorContainer);
				if (errorMessage.length) {
					errorMessage = errorMessage.html();
					WPAC._Debug("info", "Error message '%s' successfully extracted", errorMessage);
					WPAC._ShowMessage(errorMessage, "error");
					return;
				}
			}

			WPAC._Debug("error", "Error message could not be extracted, use error message '%s'.", WPAC._Options.textUnknownError);
			WPAC._ShowMessage(WPAC._Options.textUnknownError, "error");
		}
		
		var request = jQuery.ajax({
			url: submitUrl,
			type: "POST",
			data: form.serialize(),
			beforeSend: function(xhr){ xhr.setRequestHeader('X-WPAC-REQUEST', '1'); },
			complete: function(xhr, textStatus) { form.removeData("WPAC_SUBMITTING", true); },
			success: function (data) {

				// Test error state (WordPress >=4.1 does not return 500 status code if posting comment failed)
				if (request.getResponseHeader("X-WPAC-ERROR")) {
					WPAC._Debug("info", "Found error state X-WPAC-ERROR header.", commentUrl);
					handleErrorResponse(data);
					return;
				}
			
				WPAC._Debug("info", "Comment has been posted");

				// Get info from response header
				var commentUrl = request.getResponseHeader("X-WPAC-URL");
				WPAC._Debug("info", "Found comment URL '%s' in X-WPAC-URL header.", commentUrl);
				var unapproved = request.getResponseHeader("X-WPAC-UNAPPROVED");
				WPAC._Debug("info", "Found unapproved state '%s' in X-WPAC-UNAPPROVED", unapproved);

				options.afterPostComment(commentUrl, unapproved == '1');
				
				// Show success message
				WPAC._ShowMessage(unapproved == '1' ? WPAC._Options.textPostedUnapproved : WPAC._Options.textPosted, "success");

				// Replace comments (and return if replacing failed)
				if (!WPAC._ReplaceComments(data, commentUrl, false, {}, "", options.selectorCommentsContainer, options.selectorCommentForm, options.selectorRespondContainer, 
					options.beforeSelectElements, options.beforeUpdateComments, options.afterUpdateComments)) return;
				
				// Smooth scroll to comment url and update browser url
				if (commentUrl) {
						
					if (options.updateUrl)
						WPAC._UpdateUrl(commentUrl);
					
					if (options.scrollToAnchor) {
						var anchor = commentUrl.indexOf("#") >= 0 ? commentUrl.substr(commentUrl.indexOf("#")) : null;
						if (anchor) {
							WPAC._Debug("info", "Anchor '%s' extracted from comment URL '%s'", anchor, commentUrl);
							WPAC._ScrollToAnchor(anchor, options.updateUrl);
						}
					}
				}
				
			},
			error: function (jqXhr, textStatus, errorThrown) {

				// Test if loading comment url failed (due to cross site scripting error)
				if (jqXhr.status === 0 && jqXhr.responseText === "") {
					WPAC._Debug("error", "Comment seems to be posted, but loading comment update failed.");
					WPAC._LoadFallbackUrl(WPAC._AddQueryParamStringToUrl(window.location.href, "WPACFallback", "1"));
					return;
				}
			
				handleErrorResponse(jqXhr.responseText);
			}
		});
	};
	addHandler("submit", options.selectorCommentForm, formSubmitHandler)
}

WPAC._Initialized = false;
WPAC.Init = function() {

	// Test if plugin already has been initialized
	if (WPAC._Initialized) {
		WPAC._Debug("info", "Abort initialization (plugin already initialized)");
		return false;
	}
	WPAC._Initialized = true;
	
	// Assert that environment is set up correctly
	if (!WPAC._Options || !WPAC._Callbacks) {
		WPAC._Debug("error", "Something unexpected happened, initialization failed. Please try to reinstall the plugin.");
		return false;
	}

	// Debug infos
	WPAC._Debug("info", "Initializing version %s", WPAC._Options.version);

	// Debug infos
	if (WPAC._Options.debug) {
		if (!jQuery || !jQuery.fn || !jQuery.fn.jquery) {
			WPAC._Debug("error", "jQuery not found, abort initialization. Please try to reinstall the plugin.");
			return false;
		}
		WPAC._Debug("info", "Found jQuery %s", jQuery.fn.jquery);
		if (!jQuery.blockUI || !jQuery.blockUI.version) {
			WPAC._Debug("error", "jQuery blockUI not found, abort initialization. Please try to reinstall the plugin.");
			return false;
		}
		WPAC._Debug("info", "Found jQuery blockUI %s", jQuery.blockUI.version);
		if (!jQuery.idleTimer) {
			WPAC._Debug("error", "jQuery Idle Timer plugin not found, abort initialization. Please try to reinstall the plugin.");
			return false;
		}
		WPAC._Debug("info", "Found jQuery Idle Timer plugin");
	}

	if (WPAC._Options.selectorPostContainer) {
		WPAC._Debug("info", "Multiple comment form support enabled (selector: '%s')", WPAC._Options.selectorPostContainer);
		jQuery(WPAC._Options.selectorPostContainer).each(function(i,e) {
			var id = jQuery(e).attr("id");
			if (!id) {
				WPAC._Debug("info", "Skip post container element %o (ID not defined)", e);
				return
			}
			WPAC.AttachForm({
				selectorCommentForm: "#" + id + " " + WPAC._Options.selectorCommentForm,
				selectorCommentPagingLinks: "#" + id + " " + WPAC._Options.selectorCommentPagingLinks,
				selectorCommentsContainer: "#" + id + " " + WPAC._Options.selectorCommentsContainer,
				selectorRespondContainer: "#" + id + " " + WPAC._Options.selectorRespondContainer
			});	
		});
	} else {
		WPAC.AttachForm();
	}
	
	// Set up idle timer
	if (WPAC._Options.commentsEnabled && WPAC._Options.autoUpdateIdleTime > 0) {
		WPAC._Debug("info", "Auto updating comments enabled (idle time: %s)", WPAC._Options.autoUpdateIdleTime);
		WPAC._InitIdleTimer();
	}
	
	WPAC._Debug("info", "Initialization completed");

	return true;
}

WPAC._OnIdle = function() {
	WPAC.RefreshComments({ success: WPAC._InitIdleTimer, scrollToAnchor: false});
};

WPAC._InitIdleTimer = function() {
	if (WPAC._TestFallbackUrl(location.href)) {
		WPAC._Debug("error", "Fallback URL was detected (url: '%s'), cancel init idle timer", location.href);
		return;
	}
	
	jQuery(document).idleTimer("destroy");
	jQuery(document).idleTimer(WPAC._Options.autoUpdateIdleTime);
	jQuery(document).on("idle.idleTimer", WPAC._OnIdle);
}

WPAC.RefreshComments = function(options) {
	var url = location.href;
	
	if (WPAC._TestFallbackUrl(location.href)) {
		WPAC._Debug("error", "Fallback URL was detected (url: '%s'), cancel AJAX request", url);
		return false;   
	}
	
	return WPAC.LoadComments(url, options)
}

WPAC.LoadComments = function(url, options) {

	// Cancel AJAX request if cross-domain scripting is detected
	if (WPAC._TestCrossDomainScripting(url)) {
		WPAC._Debug("error", "Cross-domain scripting detected (url: '%s'), cancel AJAX request", url);
		return false;
	}

	// Convert boolean parameter (used in version <0.14.0)
	if (typeof(options) == "boolean")
		options = {scrollToAnchor: options}

	// Set default options
	options = jQuery.extend({
		scrollToAnchor: !WPAC._Options.disableScrollToAnchor,
		showLoadingInfo: true,
		updateUrl: !WPAC._Options.disableUrlUpdate,
		success: function() {},
		selectorCommentForm: WPAC._Options.selectorCommentForm,
		selectorCommentsContainer: WPAC._Options.selectorCommentsContainer,
		selectorRespondContainer: WPAC._Options.selectorRespondContainer,
		disableCache: WPAC._Options.disableCache,
		beforeSelectElements: WPAC._Callbacks.beforeSelectElements, 
		beforeUpdateComments: WPAC._Callbacks.beforeUpdateComments,
		afterUpdateComments: WPAC._Callbacks.afterUpdateComments,
	}, options || {});	
	
	// Save form data and focus
	var formData = jQuery(options.selectorCommentForm).serializeArray();
	var formFocus = (document.activeElement) ? jQuery("[name='"+document.activeElement.name+"']", options.selectorCommentForm).attr("name") : "";
	
	// Show loading info
	if (options.showLoadingInfo)
		WPAC._ShowMessage(WPAC._Options.textRefreshComments, "loading");

	if (options.disableCache) 
		url = WPAC._AddQueryParamStringToUrl(url, "WPACRandom", (new Date()).getTime());
		
	var request = jQuery.ajax({
		url: url,
		type: "GET",
		beforeSend: function(xhr){ xhr.setRequestHeader("X-WPAC-REQUEST", "1"); },
		success: function (data) {

			// Replace comments (and return if replacing failed)
			if (!WPAC._ReplaceComments(data, url, true, formData, formFocus, options.selectorCommentsContainer, options.selectorCommentForm, 
				options.selectorRespondContainer, options.beforeSelectElements, options.beforeUpdateComments, options.afterUpdateComments)) return;
			
			if (options.updateUrl) WPAC._UpdateUrl(url);

			// Scroll to anchor
			var waitForScrollToAnchor = false;
			if (options.scrollToAnchor) {
				var anchor = url.indexOf("#") >= 0 ? url.substr(url.indexOf("#")) : null;
				if (anchor) {
					WPAC._Debug("info", "Anchor '%s' extracted from url", anchor);
					if (WPAC._ScrollToAnchor(anchor, options.updateUrl, function() { options.success(); } )) {
						waitForScrollToAnchor = true;
					}
				}
			}
			
			// Unblock UI
			jQuery.unblockUI();
			
			if (!waitForScrollToAnchor) options.success();
		},
		error: function() {
			WPAC._LoadFallbackUrl(WPAC._AddQueryParamStringToUrl(window.location.href, "WPACFallback", "1"))
		} 
		
	});
	
	return true;
}

jQuery(function() {
	var initSuccesful = WPAC.Init();
	if (WPAC._Options.loadCommentsAsync) {
		if (!initSuccesful) {
			WPAC._LoadFallbackUrl(WPAC._AddQueryParamStringToUrl(window.location.href, "WPACFallback", "1"))
			return;
		}

		var asyncLoadTrigger = WPAC._Options.asyncLoadTrigger;
		WPAC._Debug("info", "Loading comments asynchronously with secondary AJAX request (trigger: '%s')", asyncLoadTrigger);
		
		if (window.location.hash) {
			var regex = /^#comment-[0-9]+$/;
			if (regex.test(window.location.hash)) {
				WPAC._Debug("info", "Comment anchor in URL detected, force loading comments on DomReady (hash: '%s')", window.location.hash);
				asyncLoadTrigger = "DomReady";
			}
		}
		
		if (asyncLoadTrigger == "Viewport") {
			jQuery(WPAC._Options.selectorCommentsContainer).waypoint(function() {
				jQuery(WPAC._Options.selectorCommentsContainer).waypoint("destroy");
				WPAC.RefreshComments();
			}, { offset: "100%" });
		} else if (asyncLoadTrigger == "DomReady") {
			WPAC.RefreshComments({scrollToAnchor: true}); // force scroll to anchor
		}
	} 
});

function wpac_init() {
	WPAC._Debug("info", "wpac_init() is deprecated, please use WPAC.Init()");
	WPAC.Init();
}