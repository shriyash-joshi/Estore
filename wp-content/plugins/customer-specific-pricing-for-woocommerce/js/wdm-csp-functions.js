/*This file contains the code to convert WC_PRICE function of php to javascript */
function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '')
    .replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
        var k = Math.pow(10, prec);
        return '' + (Math.round(n * k) / k)
        .toFixed(prec);
    };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
    .split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '')
        .length < prec) {
        s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1)
    .join('0');
}
return s.join(dec);
}

function objectLength(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) {
            size++;
        }
    }
    return size;
};

/**
 * Converts formatted number to database understandable number
 * @param  {string} number        Number to be converted
 * @param  {number} decimals      Numbers to be used after decimal
 * @param  {string} dec_sep     Decimal Point in the given number
 * @return {float}               Formatted number
 */
 function reverse_number_format(number, decimals, dec_sep, thousands_sep) {

    var dec_sep_count = occurrences_of_substring(number, dec_sep);

    var thousand_sep_count = occurrences_of_substring(number, thousands_sep);

    if( (dec_sep_count > 1) || (thousand_sep_count >= 1 && thousands_sep != '')) {
        return Number.NaN;
    }

    number = number.trim();

    if (number == '') {
        return Number.NaN;
    }

    decimals = parseInt(decimals);

    if (isNaN(decimals)) {
        return Number.NaN;
    }

    number = number.replace(dec_sep, ".");

    if (isNaN(number)) {
        return Number.NaN;
    }

    return number;
}

/** Function that count occurrences_of_substring of a substring in a string;
 * @param {String} string               The string
 * @param {String} subString            The sub string to search for
 * @param {Boolean} [allowOverlapping]  Optional. (Default:false)
 *
 * @author Vitim.us https://gist.github.com/victornpb/7736865
 * @see Unit Test https://jsfiddle.net/Victornpb/5axuh96u/
 * @see http://stackoverflow.com/questions/4009756/how-to-count-string-occurrence-in-string/7924240#7924240
 */
 function occurrences_of_substring(string, subString, allowOverlapping) {

    string += "";
    subString += "";
    if (subString.length <= 0) return (string.length + 1);

    var n = 0,
    pos = 0,
    step = allowOverlapping ? 1 : subString.length;

    while (true) {
        pos = string.indexOf(subString, pos);
        if (pos >= 0) {
            ++n;
            pos += step;
        } else break;
    }
    return n;
}

function preg_quote(str, delimiter) {


    return String(str)
    .replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
}

function sprintf() {
    var regex = /%%|%(\d+\$)?([-+\'#0 ]*)(\*\d+\$|\*|\d+)?(\.(\*\d+\$|\*|\d+))?([scboxXuideEfFgG])/g;
    var a = arguments;
    var i = 0;
    var format = a[i++];

    // pad()
    var pad = function(str, len, chr, leftJustify) {
        if (!chr) {
            chr = ' ';
        }
        var padding = (str.length >= len) ? '' : new Array(1 + len - str.length >>> 0)
        .join(chr);
        return leftJustify ? str + padding : padding + str;
    };

    // justify()
    var justify = function(value, prefix, leftJustify, minWidth, zeroPad, customPadChar) {
        var diff = minWidth - value.length;
        if (diff > 0) {
            if (leftJustify || !zeroPad) {
                value = pad(value, minWidth, customPadChar, leftJustify);
            } else {
                value = value.slice(0, prefix.length) + pad('', diff, '0', true) + value.slice(prefix.length);
            }
        }
        return value;
    };

    // formatBaseX()
    var formatBaseX = function(value, base, prefix, leftJustify, minWidth, precision, zeroPad) {
        // Note: casts negative numbers to positive ones
        var number = value >>> 0;
        prefix = prefix && number && {
            '2': '0b',
            '8': '0',
            '16': '0x'
        }[base] || '';
        value = prefix + pad(number.toString(base), precision || 0, '0', false);
        return justify(value, prefix, leftJustify, minWidth, zeroPad);
    };

    // formatString()
    var formatString = function(value, leftJustify, minWidth, precision, zeroPad, customPadChar) {
        if (precision != null) {
            value = value.slice(0, precision);
        }
        return justify(value, '', leftJustify, minWidth, zeroPad, customPadChar);
    };

    // doFormat()
    var doFormat = function(substring, valueIndex, flags, minWidth, _, precision, type) {
        var number, prefix, method, textTransform, value;

        if (substring === '%%') {
            return '%';
        }

        // parse flags
        var leftJustify = false;
        var positivePrefix = '';
        var zeroPad = false;
        var prefixBaseX = false;
        var customPadChar = ' ';
        var flagsl = flags.length;
        for (var j = 0; flags && j < flagsl; j++) {
            switch (flags.charAt(j)) {
                case ' ':
                positivePrefix = ' ';
                break;
                case '+':
                positivePrefix = '+';
                break;
                case '-':
                leftJustify = true;
                break;
                case "'":
                customPadChar = flags.charAt(j + 1);
                break;
                case '0':
                zeroPad = true;
                customPadChar = '0';
                break;
                case '#':
                prefixBaseX = true;
                break;
            }
        }

        // parameters may be null, undefined, empty-string or real valued
        // we want to ignore null, undefined and empty-string values
        if (!minWidth) {
            minWidth = 0;
        } else if (minWidth === '*') {
            minWidth = +a[i++];
        } else if (minWidth.charAt(0) == '*') {
            minWidth = +a[minWidth.slice(1, -1)];
        } else {
            minWidth = +minWidth;
        }

        // Note: undocumented perl feature:
        if (minWidth < 0) {
            minWidth = -minWidth;
            leftJustify = true;
        }

        if (!isFinite(minWidth)) {
            throw new Error('sprintf: (minimum-)width must be finite');
        }

        if (!precision) {
            precision = 'fFeE'.indexOf(type) > -1 ? 6 : (type === 'd') ? 0 : undefined;
        } else if (precision === '*') {
            precision = +a[i++];
        } else if (precision.charAt(0) == '*') {
            precision = +a[precision.slice(1, -1)];
        } else {
            precision = +precision;
        }

        // grab value using valueIndex if required?
        value = valueIndex ? a[valueIndex.slice(0, -1)] : a[i++];

        switch (type) {
            case 's':
            return formatString(String(value), leftJustify, minWidth, precision, zeroPad, customPadChar);
            case 'c':
            return formatString(String.fromCharCode(+value), leftJustify, minWidth, precision, zeroPad);
            case 'b':
            return formatBaseX(value, 2, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
            case 'o':
            return formatBaseX(value, 8, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
            case 'x':
            return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
            case 'X':
            return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad)
            .toUpperCase();
            case 'u':
            return formatBaseX(value, 10, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
            case 'i':
            case 'd':
            number = +value || 0;
                number = Math.round(number - number % 1); // Plain Math.round doesn't just truncate
                prefix = number < 0 ? '-' : positivePrefix;
                value = prefix + pad(String(Math.abs(number)), precision, '0', false);
                return justify(value, prefix, leftJustify, minWidth, zeroPad);
                case 'e':
                case 'E':
            case 'f': // Should handle locales (as per setlocale)
            case 'F':
            case 'g':
            case 'G':
            number = +value;
            prefix = number < 0 ? '-' : positivePrefix;
            method = ['toExponential', 'toFixed', 'toPrecision']['efg'.indexOf(type.toLowerCase())];
            textTransform = ['toString', 'toUpperCase']['eEfFgG'.indexOf(type) % 2];
            value = prefix + Math.abs(number)[method](precision);
            return justify(value, prefix, leftJustify, minWidth, zeroPad)[textTransform]();
            default:
            return substring;
        }
    };

    return format.replace(regex, doFormat);
}

function empty(mixedVar) {
    //  discuss at: http://locutus.io/php/empty/
    // original by: Philippe Baumann
    //    input by: Onno Marsman (https://twitter.com/onnomarsman)
    //    input by: LH
    //    input by: Stoyan Kyosev (http://www.svest.org/)
    // bugfixed by: Kevin van Zonneveld (http://kvz.io)
    // improved by: Onno Marsman (https://twitter.com/onnomarsman)
    // improved by: Francesco
    // improved by: Marc Jansen
    // improved by: Rafał Kukawski (http://blog.kukawski.pl)
    //   example 1: empty(null)
    //   returns 1: true
    //   example 2: empty(undefined)
    //   returns 2: true
    //   example 3: empty([])
    //   returns 3: true
    //   example 4: empty({})
    //   returns 4: true
    //   example 5: empty({'aFunc' : function () { alert('humpty'); } })
    //   returns 5: false

    var undef
    var key
    var i
    var len
    var emptyValues = [undef, null, false, 0, '', '0']

    for (i = 0, len = emptyValues.length; i < len; i++) {
        if (mixedVar === emptyValues[i]) {
            return true
        }
    }

    if (typeof mixedVar === 'object') {
        for (key in mixedVar) {
            if (mixedVar.hasOwnProperty(key)) {
                return false
            }
        }
        return true
    }

    return false
}

/**
* Format the price to meet correct format,i.e, with decimal places, thousand separators etc.
* @param int/float price Total Price of product.
*/
function cspFormatPrice(price) {
    decimal_separator = wdm_csp_function_object.decimal_separator;
    thousand_separator = wdm_csp_function_object.thousand_separator;
    decimals = wdm_csp_function_object.decimals;
    price_format = wdm_csp_function_object.price_format;

    negative = price < 0;
    price = parseFloat(negative ? price * -1 : price) || 0
    price = number_format(price, decimals, decimal_separator, thousand_separator)

    if (decimals > 0) {
        price = price.replace('/' + preg_quote(decimal_separator, '/') + '0++$/', '');
        //price = preg_replace( '/' + preg_quote( decimal_separator, '/' ) + '0++$/', '', price );
    }

    return (negative ? '-' : '') + sprintf(price_format, wdm_csp_function_object.currency_symbol, price);
}


/**
* Returns the price appliacble in between the range of min-quantity.
* @param array qtyList min-qty min quantity list.
* @param int qty quantity of product
* @param array csp_prices quantity pricing apirs.
* @param int/float regular_price regular price of product.
* @return price for that quantity range applicable
*/
function qtyInRange(csp_prices, qtyList, qty, regular_price) {
    var qtyListSize = qtyList.length;
    for (var i in qtyList) {
        var next = parseInt(i, 10) + 1;
        if (qty > qtyList[i]) {
            if (next != qtyListSize && qty < qtyList[next]) {
                return parseFloat(csp_prices[qtyList[i]]);
            }
            if (next == qtyListSize) {
                return parseFloat(csp_prices[qtyList[i]]);
            }
        }
    }
    return parseFloat(regular_price);
}

/**
* Get the Price applicable for that quantity of Product.
* @param array qtyList min-qty min quantity list.
* @param int qty quantity of product
* @param array csp_prices quantity pricing apirs.
* @param int/float regular_price regular price of product.
* @return price  applicable for that quantity of Product.
*/
function getApplicablePrice(qtyList, qty, csp_prices, regular_price) {
    if (jQuery.inArray(qty, qtyList) != -1) {
        return parseFloat(csp_prices[qty]);
    } else {
        return qtyInRange(csp_prices, qtyList, parseInt(qty), regular_price);
    }
}

/**
* Show the total price of the product,i.e, product of quantity and specific price.
* @param float price price which is applicable.
* @param int qty quantity of product.
* @param currency currency symbol
* @param float/int/bool current_cart_total current cart total.
*/
function showPrices(price, qty, currency, current_cart_total) {
    if (current_cart_total==NaN || current_cart_total==null) {
        current_cart_total=false;
    }
    var product_total = parseFloat(price * qty);
    jQuery('#product_total_price .price').html(cspFormatPrice(product_total.toFixed(2)));
    jQuery('#product_total_price').show();
}


/**
 * Appends Query Parameter to the Url. It is very similar to WordPress's add_query_arg function.
 * Taken from: http://stackoverflow.com/questions/5999118/add-or-update-query-string-parameter
 */
 function add_query_arg(key, value, url) {
    if (!url) {
        url = window.location.href;
    }
    var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
    hash;

    if (re.test(url)) {
        if (typeof value !== 'undefined' && value !== null) {
            return url.replace(re, '$1' + key + "=" + value + '$2$3');
        } else {
            hash = url.split('#');
            url = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');
            if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
                url += '#' + hash[1];
            }
            return url;
        }
    } else {
        if (typeof value !== 'undefined' && value !== null) {
            var separator = url.indexOf('?') !== -1 ? '&' : '?';
            hash = url.split('#');
            url = hash[0] + separator + key + '=' + value;
            if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
                url += '#' + hash[1];
            }
            return url;
        } else {
            return url;
        }
    }
}

/**
* Checks that value of price is not null
* @param string price selector for price.
* @return bool true if regular price is not set otherwise false.
*/
function regularPriceExist(price) {
    if (jQuery(price).val() != "" && jQuery(price).parents('.woocommerce_variable_attributes').find("input[name^='variable_regular_price']").val() == "") {
        return true;
    }
    return false;
}

/**
* Prevents the submission in case of some error.
* Displays the error on specific location.
* @param string messageText error message.
* @param string error_location location where to display.
* @return false if no error.
*/
function preventSubmission(messageText, error_location) {
    if (error_location==null) {
        error_location='top_of_page';
    }
    if (error_location == 'top_of_page') {
        jQuery('#poststuff, .wdm-tab-info').before("<div id='wdm_message' class='error my-notice'><p>" + messageText + "</p></div>").focus();
        jQuery("html, body").animate({
            scrollTop: 0
        }, "slow");
    } else if (error_location == 'before_variations') {
        var wrapper = jQuery('#variable_product_options').find('.woocommerce_variations')
        wrapper.before("<div id='wdm_message' class='error my-notice'><p>" + messageText + "</p></div>").focus();
        jQuery("html, body").animate({
            scrollTop: jQuery('#wdm_message').offset().top - 50
        }, "slow");
    }
    return false;
}

/**
* Gets the closest element.
* @param string thisElement element whose closest is to find.
* @param string selector element
*/
function cspGetClosestElement(thisElement, selector) {
    //console.log(thisElement);
    return jQuery(thisElement).closest('tr').find(selector);
}
/**
* Checks the various conditions for which price error may occur.
* @param string selector selector element.
* highlights the field if there is any error.
*/
function highlightPriceError(selector) {

    var current_val = selector.val();
    current_val = current_val.trim();

    if (current_val == "") {
        return;
    }

    current_val = reverse_number_format(current_val, wdm_csp_function_object.decimals, wdm_csp_function_object.decimal_separator, wdm_csp_function_object.thousand_separator);

    var error = false;

    if (isNaN(current_val) || (current_val < 0)) {
        error = true;
    }

    if (selector.hasClass('csp-percent-discount') && current_val > 100) {
        error = true;
    }

    if (error) {
        selector.addClass('wdm_error');
    } else {
        if (selector.hasClass('wdm_error')) {
            selector.removeClass('wdm_error');
        }
    }
}

function isInt(value) {
    return !isNaN(value) &&
    parseInt(Number(value)) == value &&
    !isNaN(parseInt(value, 10));
}

function isPositiveInt(value) {
    if (isInt(value)) {
        convertedInt = parseInt(Number(value));
        if (convertedInt > 0) {
            return true;
        }
    }
    return false;
}

function isPositiveInteger(n) {
    return 0 === n % (!isNaN(parseFloat(n)) && 0 <= ~~n);
    }

function entereredValidCSPQuantity(qtyField)
{
    let qty=jQuery(qtyField).val();
    
    if (isPositiveInteger(qty)) {
        if (qty>0) {
            return true;   
        }
    }
    return false;
}


//warning and error messages array.
var wdmScpMessages = {quantityMessage:"Please Enter Product Quantity Greater than or equals to 1",valueFieldMessage:"Please enter a valid value"};
