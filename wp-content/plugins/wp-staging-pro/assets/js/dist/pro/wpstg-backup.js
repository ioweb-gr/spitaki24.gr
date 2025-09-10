(function () {
  'use strict';

  function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {
    try {
      var info = gen[key](arg);
      var value = info.value;
    } catch (error) {
      reject(error);
      return;
    }

    if (info.done) {
      resolve(value);
    } else {
      Promise.resolve(value).then(_next, _throw);
    }
  }

  function _asyncToGenerator(fn) {
    return function () {
      var self = this,
          args = arguments;
      return new Promise(function (resolve, reject) {
        var gen = fn.apply(self, args);

        function _next(value) {
          asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value);
        }

        function _throw(err) {
          asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err);
        }

        _next(undefined);
      });
    };
  }

  function _unsupportedIterableToArray(o, minLen) {
    if (!o) return;
    if (typeof o === "string") return _arrayLikeToArray(o, minLen);
    var n = Object.prototype.toString.call(o).slice(8, -1);
    if (n === "Object" && o.constructor) n = o.constructor.name;
    if (n === "Map" || n === "Set") return Array.from(o);
    if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
  }

  function _arrayLikeToArray(arr, len) {
    if (len == null || len > arr.length) len = arr.length;

    for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i];

    return arr2;
  }

  function _createForOfIteratorHelperLoose(o, allowArrayLike) {
    var it;

    if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) {
      if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") {
        if (it) o = it;
        var i = 0;
        return function () {
          if (i >= o.length) return {
            done: true
          };
          return {
            done: false,
            value: o[i++]
          };
        };
      }

      throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
    }

    it = o[Symbol.iterator]();
    return it.next.bind(it);
  }

  /**
   * This is a namespaced port of https://github.com/tristen/hoverintent,
   * with slight modification to accept selector with dynamically added element in dom,
   * instead of just already present element.
   *
   * @param {HTMLElement} parent
   * @param {string} selector
   * @param {CallableFunction} onOver
   * @param {CallableFunction} onOut
   *
   * @return {object}
   */

  function wpstgHoverIntent (parent, selector, onOver, onOut) {
    var x;
    var y;
    var pX;
    var pY;
    var mouseOver = false;
    var focused = false;
    var h = {};
    var state = 0;
    var timer = 0;
    var options = {
      sensitivity: 7,
      interval: 100,
      timeout: 0,
      handleFocus: false
    };

    function delay(el, e) {
      if (timer) {
        timer = clearTimeout(timer);
      }

      state = 0;
      return focused ? undefined : onOut(el, e);
    }

    function tracker(e) {
      x = e.clientX;
      y = e.clientY;
    }

    function compare(el, e) {
      if (timer) timer = clearTimeout(timer);

      if (Math.abs(pX - x) + Math.abs(pY - y) < options.sensitivity) {
        state = 1;
        return focused ? undefined : onOver(el, e);
      } else {
        pX = x;
        pY = y;
        timer = setTimeout(function () {
          compare(el, e);
        }, options.interval);
      }
    } // Public methods


    h.options = function (opt) {
      var focusOptionChanged = opt.handleFocus !== options.handleFocus;
      options = Object.assign({}, options, opt);

      if (focusOptionChanged) {
        options.handleFocus ? addFocus() : removeFocus();
      }

      return h;
    };

    function dispatchOver(el, e) {
      mouseOver = true;

      if (timer) {
        timer = clearTimeout(timer);
      }

      el.removeEventListener('mousemove', tracker, false);

      if (state !== 1) {
        pX = e.clientX;
        pY = e.clientY;
        el.addEventListener('mousemove', tracker, false);
        timer = setTimeout(function () {
          compare(el, e);
        }, options.interval);
      }

      return this;
    }
    /**
     * Newly added method,
     * A wrapper around dispatchOver to support dynamically added elements to dom
     */


    function onMouseOver(event) {
      if (event.target.matches(selector + ', ' + selector + ' *')) {
        dispatchOver(event.target.closest(selector), event);
      }
    }

    function dispatchOut(el, e) {
      mouseOver = false;

      if (timer) {
        timer = clearTimeout(timer);
      }

      el.removeEventListener('mousemove', tracker, false);

      if (state === 1) {
        timer = setTimeout(function () {
          delay(el, e);
        }, options.timeout);
      }

      return this;
    }
    /**
     * Newly added method,
     * A wrapper around dispatchOut to support dynamically added elements to dom
     */


    function onMouseOut(event) {
      if (event.target.matches(selector + ', ' + selector + ' *')) {
        dispatchOut(event.target.closest(selector), event);
      }
    }

    function dispatchFocus(el, e) {
      if (!mouseOver) {
        focused = true;
        onOver(el, e);
      }
    }
    /**
     * Newly added method,
     * A wrapper around dispatchFocus to support dynamically added elements to dom
     */


    function onFocus(event) {
      if (event.target.matches(selector + ', ' + selector + ' *')) {
        dispatchFocus(event.target.closest(selector), event);
      }
    }

    function dispatchBlur(el, e) {
      if (!mouseOver && focused) {
        focused = false;
        onOut(el, e);
      }
    }
    /**
     * Newly added method,
     * A wrapper around dispatchBlur to support dynamically added elements to dom
     */


    function onBlur(event) {
      if (event.target.matches(selector + ', ' + selector + ' *')) {
        dispatchBlur(event.target.closest(selector), event);
      }
    }
    /**
     * Modified to support dynamically added element
     */

    function addFocus() {
      parent.addEventListener('focus', onFocus, false);
      parent.addEventListener('blur', onBlur, false);
    }
    /**
     * Modified to support dynamically added element
     */


    function removeFocus() {
      parent.removeEventListener('focus', onFocus, false);
      parent.removeEventListener('blur', onBlur, false);
    }
    /**
     * Modified to support dynamically added element
     */


    h.remove = function () {
      if (!parent) {
        return;
      }

      parent.removeEventListener('mouseover', onMouseOver, false);
      parent.removeEventListener('mouseout', onMouseOut, false);
      removeFocus();
    };
    /**
     * Modified to support dynamically added element
     */


    if (parent) {
      parent.addEventListener('mouseover', onMouseOver, false);
      parent.addEventListener('mouseout', onMouseOut, false);
    }

    return h;
  }

  var WPStagingCommon = (function ($) {
    var WPStagingCommon = {
      continueErrorHandle: true,
      cache: {
        elements: [],
        get: function get(selector) {
          // It is already cached!
          if ($.inArray(selector, this.elements) !== -1) {
            return this.elements[selector];
          } // Create cache and return


          this.elements[selector] = $(selector);
          return this.elements[selector];
        },
        refresh: function refresh(selector) {
          selector.elements[selector] = $(selector);
        }
      },
      listenTooltip: function listenTooltip() {
        wpstgHoverIntent(document, '.wpstg--tooltip', function (target, event) {
          target.querySelector('.wpstg--tooltiptext').style.visibility = 'visible';
        }, function (target, event) {
          target.querySelector('.wpstg--tooltiptext').style.visibility = 'hidden';
        });
      },
      isEmpty: function isEmpty(obj) {
        for (var prop in obj) {
          if (obj.hasOwnProperty(prop)) {
            return false;
          }
        }

        return true;
      },
      // Get the custom themed Swal Modal for WP Staging
      // Easy to maintain now in one place now
      getSwalModal: function getSwalModal(isContentCentered, customClasses) {
        if (isContentCentered === void 0) {
          isContentCentered = false;
        }

        if (customClasses === void 0) {
          customClasses = {};
        }

        // common style for all swal modal used in WP Staging
        var defaultCustomClasses = {
          confirmButton: 'wpstg--btn--confirm wpstg-blue-primary wpstg-button wpstg-link-btn wpstg-100-width',
          cancelButton: 'wpstg--btn--cancel wpstg-blue-primary wpstg-link-btn wpstg-100-width',
          actions: 'wpstg--modal--actions',
          popup: isContentCentered ? 'wpstg-swal-popup centered-modal' : 'wpstg-swal-popup'
        }; // If a attribute exists in both default and additional attributes,
        // The class(es) of the additional attribute will overrite the default one.

        var options = {
          customClass: Object.assign(defaultCustomClasses, customClasses),
          buttonsStyling: false,
          reverseButtons: true,
          showClass: {
            popup: 'wpstg--swal2-show wpstg-swal-show'
          }
        };
        return wpstgSwal.mixin(options);
      },
      showSuccessModal: function showSuccessModal(htmlContent) {
        this.getSwalModal().fire({
          showConfirmButton: false,
          showCancelButton: true,
          cancelButtonText: 'OK',
          icon: 'success',
          title: 'Success!',
          html: '<div class="wpstg--grey" style="text-align: left; margin-top: 8px;">' + htmlContent + '</div>'
        });
      },
      showWarningModal: function showWarningModal(htmlContent) {
        this.getSwalModal().fire({
          showConfirmButton: false,
          showCancelButton: true,
          cancelButtonText: 'OK',
          icon: 'warning',
          title: '',
          html: '<div class="wpstg--grey" style="text-align: left; margin-top: 8px;">' + htmlContent + '</div>'
        });
      },
      showErrorModal: function showErrorModal(htmlContent) {
        this.getSwalModal().fire({
          showConfirmButton: false,
          showCancelButton: true,
          cancelButtonText: 'OK',
          icon: 'error',
          title: 'Error!',
          html: '<div class="wpstg--grey" style="text-align: left; margin-top: 8px;">' + htmlContent + '</div>'
        });
      },
      getSwalContainer: function getSwalContainer() {
        return wpstgSwal.getContainer();
      },
      closeSwalModal: function closeSwalModal() {
        wpstgSwal.close();
      },

      /**
       * Treats a default response object generated by WordPress's
       * wp_send_json_success() or wp_send_json_error() functions in
       * PHP, parses it in JavaScript, and either throws if it's an error,
       * or returns the data if the response is successful.
       *
       * @param {object} response
       * @return {*}
       */
      getDataFromWordPressResponse: function getDataFromWordPressResponse(response) {
        if (typeof response !== 'object') {
          throw new Error('Unexpected response (ERR 1341)');
        }

        if (!response.hasOwnProperty('success')) {
          throw new Error('Unexpected response (ERR 1342)');
        }

        if (!response.hasOwnProperty('data')) {
          throw new Error('Unexpected response (ERR 1343)');
        }

        if (response.success === false) {
          if (response.data instanceof Array && response.data.length > 0) {
            throw new Error(response.data.shift());
          } else {
            throw new Error('Response was not successful');
          }
        } else {
          // Successful response. Return the data.
          return response.data;
        }
      },
      isLoading: function isLoading(_isLoading) {
        if (!_isLoading || _isLoading === false) {
          WPStagingCommon.cache.get('.wpstg-loader').hide();
        } else {
          WPStagingCommon.cache.get('.wpstg-loader').show();
        }
      },

      /**
       * Convert the given url to make it slug compatible
       * @param {string} url
       */
      slugify: function slugify(url) {
        return url.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, '-').replace(/&/g, '-and-').replace(/[^a-z0-9\-]/g, '').replace(/-+/g, '-').replace(/^-*/, '').replace(/-*$/, '');
      },
      showAjaxFatalError: function showAjaxFatalError(response, prependMessage, appendMessage) {
        prependMessage = prependMessage ? prependMessage + '<br/><br/>' : 'Something went wrong! <br/><br/>';
        appendMessage = appendMessage ? appendMessage + '<br/><br/>' : '<br/><br/>Please try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.';

        if (response === false) {
          WPStagingCommon.showError(prependMessage + ' Error: No response.' + appendMessage);
          window.removeEventListener('beforeunload', WPStaging.warnIfClosingDuringProcess);
          return;
        }

        if (typeof response.error !== 'undefined' && response.error) {
          WPStagingCommon.showError(prependMessage + ' Error: ' + response.message + appendMessage);
          window.removeEventListener('beforeunload', WPStaging.warnIfClosingDuringProcess);
          return;
        }
      },
      handleFetchErrors: function handleFetchErrors(response) {
        if (!response.ok) {
          WPStagingCommon.showError('Error: ' + response.status + ' - ' + response.statusText + '. Please try again or contact support.');
        }

        return response;
      },
      showError: function showError(message) {
        WPStagingCommon.cache.get('#wpstg-try-again').css('display', 'inline-block');
        WPStagingCommon.cache.get('#wpstg-cancel-cloning').text('Reset');
        WPStagingCommon.cache.get('#wpstg-resume-cloning').show();
        WPStagingCommon.cache.get('#wpstg-error-wrapper').show();
        WPStagingCommon.cache.get('#wpstg-error-details').show().html(message);
        WPStagingCommon.cache.get('#wpstg-removing-clone').removeClass('loading');
        WPStagingCommon.cache.get('.wpstg-loader').hide();
        $('.wpstg--modal--process--generic-problem').show().html(message);
      },
      resetErrors: function resetErrors() {
        WPStagingCommon.cache.get('#wpstg-error-details').hide().html('');
      },

      /**
       * Ajax Requests
       * @param {Object} data
       * @param {Function} callback
       * @param {string} dataType
       * @param {bool} showErrors
       * @param {int} tryCount
       * @param {float} incrementRatio
       * @param errorCallback
       */
      ajax: function ajax(data, callback, dataType, showErrors, tryCount, incrementRatio, errorCallback) {
        if (incrementRatio === void 0) {
          incrementRatio = null;
        }

        if (errorCallback === void 0) {
          errorCallback = null;
        }

        if ('undefined' === typeof dataType) {
          dataType = 'json';
        }

        if (false !== showErrors) {
          showErrors = true;
        }

        tryCount = 'undefined' === typeof tryCount ? 0 : tryCount;
        var retryLimit = 10;
        var retryTimeout = 10000 * tryCount;
        incrementRatio = parseInt(incrementRatio);

        if (!isNaN(incrementRatio)) {
          retryTimeout *= incrementRatio;
        }

        $.ajax({
          url: ajaxurl + '?action=wpstg_processing&_=' + Date.now() / 1000,
          type: 'POST',
          dataType: dataType,
          cache: false,
          data: data,
          error: function error(xhr, textStatus, errorThrown) {
            console.log(xhr.status + ' ' + xhr.statusText + '---' + textStatus);

            if (typeof errorCallback === 'function') {
              // Custom error handler
              errorCallback(xhr, textStatus, errorThrown);

              if (!WPStagingCommon.continueErrorHandle) {
                // Reset state
                WPStagingCommon.continueErrorHandle = true;
                return;
              }
            } // Default error handler


            tryCount++;

            if (tryCount <= retryLimit) {
              setTimeout(function () {
                WPStagingCommon.ajax(data, callback, dataType, showErrors, tryCount, incrementRatio);
                return;
              }, retryTimeout);
            } else {
              var errorCode = 'undefined' === typeof xhr.status ? 'Unknown' : xhr.status;
              WPStagingCommon.showError('Fatal Error:  ' + errorCode + ' Please try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.');
            }
          },
          success: function success(data) {
            if ('function' === typeof callback) {
              callback(data);
            }
          },
          statusCode: {
            404: function _() {
              if (tryCount >= retryLimit) {
                WPStagingCommon.showError('Error 404 - Can\'t find ajax request URL! Please try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.');
              }
            },
            500: function _() {
              if (tryCount >= retryLimit) {
                WPStagingCommon.showError('Fatal Error 500 - Internal server error while processing the request! Please try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.');
              }
            },
            504: function _() {
              if (tryCount > retryLimit) {
                WPStagingCommon.showError('Error 504 - It looks like your server is rate limiting ajax requests. Please try to resume after a minute. If this still not works try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.\n\ ');
              }
            },
            502: function _() {
              if (tryCount >= retryLimit) {
                WPStagingCommon.showError('Error 502 - It looks like your server is rate limiting ajax requests. Please try to resume after a minute. If this still not works try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.\n\ ');
              }
            },
            503: function _() {
              if (tryCount >= retryLimit) {
                WPStagingCommon.showError('Error 503 - It looks like your server is rate limiting ajax requests. Please try to resume after a minute. If this still not works try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.\n\ ');
              }
            },
            429: function _() {
              if (tryCount >= retryLimit) {
                WPStagingCommon.showError('Error 429 - It looks like your server is rate limiting ajax requests. Please try to resume after a minute. If this still not works try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.\n\ ');
              }
            },
            403: function _() {
              if (tryCount >= retryLimit) {
                WPStagingCommon.showError('Refresh page or login again! The process should be finished successfully. \n\ ');
              }
            }
          }
        });
      }
    };
    return WPStagingCommon;
  })(jQuery);

  /**
   * WP STAGING basic jQuery replacement
   */

  /**
   * Shortcut for document.querySelector() or jQuery's $()
   * Return single element only
   */
  function qs(selector) {
    return document.querySelector(selector);
  }

  /**
   * Copyright (c) 2014-present, Facebook, Inc.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   */

  var runtime = (function (exports) {

    var Op = Object.prototype;
    var hasOwn = Op.hasOwnProperty;
    var undefined$1; // More compressible than void 0.
    var $Symbol = typeof Symbol === "function" ? Symbol : {};
    var iteratorSymbol = $Symbol.iterator || "@@iterator";
    var asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator";
    var toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag";

    function define(obj, key, value) {
      Object.defineProperty(obj, key, {
        value: value,
        enumerable: true,
        configurable: true,
        writable: true
      });
      return obj[key];
    }
    try {
      // IE 8 has a broken Object.defineProperty that only works on DOM objects.
      define({}, "");
    } catch (err) {
      define = function(obj, key, value) {
        return obj[key] = value;
      };
    }

    function wrap(innerFn, outerFn, self, tryLocsList) {
      // If outerFn provided and outerFn.prototype is a Generator, then outerFn.prototype instanceof Generator.
      var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator;
      var generator = Object.create(protoGenerator.prototype);
      var context = new Context(tryLocsList || []);

      // The ._invoke method unifies the implementations of the .next,
      // .throw, and .return methods.
      generator._invoke = makeInvokeMethod(innerFn, self, context);

      return generator;
    }
    exports.wrap = wrap;

    // Try/catch helper to minimize deoptimizations. Returns a completion
    // record like context.tryEntries[i].completion. This interface could
    // have been (and was previously) designed to take a closure to be
    // invoked without arguments, but in all the cases we care about we
    // already have an existing method we want to call, so there's no need
    // to create a new function object. We can even get away with assuming
    // the method takes exactly one argument, since that happens to be true
    // in every case, so we don't have to touch the arguments object. The
    // only additional allocation required is the completion record, which
    // has a stable shape and so hopefully should be cheap to allocate.
    function tryCatch(fn, obj, arg) {
      try {
        return { type: "normal", arg: fn.call(obj, arg) };
      } catch (err) {
        return { type: "throw", arg: err };
      }
    }

    var GenStateSuspendedStart = "suspendedStart";
    var GenStateSuspendedYield = "suspendedYield";
    var GenStateExecuting = "executing";
    var GenStateCompleted = "completed";

    // Returning this object from the innerFn has the same effect as
    // breaking out of the dispatch switch statement.
    var ContinueSentinel = {};

    // Dummy constructor functions that we use as the .constructor and
    // .constructor.prototype properties for functions that return Generator
    // objects. For full spec compliance, you may wish to configure your
    // minifier not to mangle the names of these two functions.
    function Generator() {}
    function GeneratorFunction() {}
    function GeneratorFunctionPrototype() {}

    // This is a polyfill for %IteratorPrototype% for environments that
    // don't natively support it.
    var IteratorPrototype = {};
    IteratorPrototype[iteratorSymbol] = function () {
      return this;
    };

    var getProto = Object.getPrototypeOf;
    var NativeIteratorPrototype = getProto && getProto(getProto(values([])));
    if (NativeIteratorPrototype &&
        NativeIteratorPrototype !== Op &&
        hasOwn.call(NativeIteratorPrototype, iteratorSymbol)) {
      // This environment has a native %IteratorPrototype%; use it instead
      // of the polyfill.
      IteratorPrototype = NativeIteratorPrototype;
    }

    var Gp = GeneratorFunctionPrototype.prototype =
      Generator.prototype = Object.create(IteratorPrototype);
    GeneratorFunction.prototype = Gp.constructor = GeneratorFunctionPrototype;
    GeneratorFunctionPrototype.constructor = GeneratorFunction;
    GeneratorFunction.displayName = define(
      GeneratorFunctionPrototype,
      toStringTagSymbol,
      "GeneratorFunction"
    );

    // Helper for defining the .next, .throw, and .return methods of the
    // Iterator interface in terms of a single ._invoke method.
    function defineIteratorMethods(prototype) {
      ["next", "throw", "return"].forEach(function(method) {
        define(prototype, method, function(arg) {
          return this._invoke(method, arg);
        });
      });
    }

    exports.isGeneratorFunction = function(genFun) {
      var ctor = typeof genFun === "function" && genFun.constructor;
      return ctor
        ? ctor === GeneratorFunction ||
          // For the native GeneratorFunction constructor, the best we can
          // do is to check its .name property.
          (ctor.displayName || ctor.name) === "GeneratorFunction"
        : false;
    };

    exports.mark = function(genFun) {
      if (Object.setPrototypeOf) {
        Object.setPrototypeOf(genFun, GeneratorFunctionPrototype);
      } else {
        genFun.__proto__ = GeneratorFunctionPrototype;
        define(genFun, toStringTagSymbol, "GeneratorFunction");
      }
      genFun.prototype = Object.create(Gp);
      return genFun;
    };

    // Within the body of any async function, `await x` is transformed to
    // `yield regeneratorRuntime.awrap(x)`, so that the runtime can test
    // `hasOwn.call(value, "__await")` to determine if the yielded value is
    // meant to be awaited.
    exports.awrap = function(arg) {
      return { __await: arg };
    };

    function AsyncIterator(generator, PromiseImpl) {
      function invoke(method, arg, resolve, reject) {
        var record = tryCatch(generator[method], generator, arg);
        if (record.type === "throw") {
          reject(record.arg);
        } else {
          var result = record.arg;
          var value = result.value;
          if (value &&
              typeof value === "object" &&
              hasOwn.call(value, "__await")) {
            return PromiseImpl.resolve(value.__await).then(function(value) {
              invoke("next", value, resolve, reject);
            }, function(err) {
              invoke("throw", err, resolve, reject);
            });
          }

          return PromiseImpl.resolve(value).then(function(unwrapped) {
            // When a yielded Promise is resolved, its final value becomes
            // the .value of the Promise<{value,done}> result for the
            // current iteration.
            result.value = unwrapped;
            resolve(result);
          }, function(error) {
            // If a rejected Promise was yielded, throw the rejection back
            // into the async generator function so it can be handled there.
            return invoke("throw", error, resolve, reject);
          });
        }
      }

      var previousPromise;

      function enqueue(method, arg) {
        function callInvokeWithMethodAndArg() {
          return new PromiseImpl(function(resolve, reject) {
            invoke(method, arg, resolve, reject);
          });
        }

        return previousPromise =
          // If enqueue has been called before, then we want to wait until
          // all previous Promises have been resolved before calling invoke,
          // so that results are always delivered in the correct order. If
          // enqueue has not been called before, then it is important to
          // call invoke immediately, without waiting on a callback to fire,
          // so that the async generator function has the opportunity to do
          // any necessary setup in a predictable way. This predictability
          // is why the Promise constructor synchronously invokes its
          // executor callback, and why async functions synchronously
          // execute code before the first await. Since we implement simple
          // async functions in terms of async generators, it is especially
          // important to get this right, even though it requires care.
          previousPromise ? previousPromise.then(
            callInvokeWithMethodAndArg,
            // Avoid propagating failures to Promises returned by later
            // invocations of the iterator.
            callInvokeWithMethodAndArg
          ) : callInvokeWithMethodAndArg();
      }

      // Define the unified helper method that is used to implement .next,
      // .throw, and .return (see defineIteratorMethods).
      this._invoke = enqueue;
    }

    defineIteratorMethods(AsyncIterator.prototype);
    AsyncIterator.prototype[asyncIteratorSymbol] = function () {
      return this;
    };
    exports.AsyncIterator = AsyncIterator;

    // Note that simple async functions are implemented on top of
    // AsyncIterator objects; they just return a Promise for the value of
    // the final result produced by the iterator.
    exports.async = function(innerFn, outerFn, self, tryLocsList, PromiseImpl) {
      if (PromiseImpl === void 0) PromiseImpl = Promise;

      var iter = new AsyncIterator(
        wrap(innerFn, outerFn, self, tryLocsList),
        PromiseImpl
      );

      return exports.isGeneratorFunction(outerFn)
        ? iter // If outerFn is a generator, return the full iterator.
        : iter.next().then(function(result) {
            return result.done ? result.value : iter.next();
          });
    };

    function makeInvokeMethod(innerFn, self, context) {
      var state = GenStateSuspendedStart;

      return function invoke(method, arg) {
        if (state === GenStateExecuting) {
          throw new Error("Generator is already running");
        }

        if (state === GenStateCompleted) {
          if (method === "throw") {
            throw arg;
          }

          // Be forgiving, per 25.3.3.3.3 of the spec:
          // https://people.mozilla.org/~jorendorff/es6-draft.html#sec-generatorresume
          return doneResult();
        }

        context.method = method;
        context.arg = arg;

        while (true) {
          var delegate = context.delegate;
          if (delegate) {
            var delegateResult = maybeInvokeDelegate(delegate, context);
            if (delegateResult) {
              if (delegateResult === ContinueSentinel) continue;
              return delegateResult;
            }
          }

          if (context.method === "next") {
            // Setting context._sent for legacy support of Babel's
            // function.sent implementation.
            context.sent = context._sent = context.arg;

          } else if (context.method === "throw") {
            if (state === GenStateSuspendedStart) {
              state = GenStateCompleted;
              throw context.arg;
            }

            context.dispatchException(context.arg);

          } else if (context.method === "return") {
            context.abrupt("return", context.arg);
          }

          state = GenStateExecuting;

          var record = tryCatch(innerFn, self, context);
          if (record.type === "normal") {
            // If an exception is thrown from innerFn, we leave state ===
            // GenStateExecuting and loop back for another invocation.
            state = context.done
              ? GenStateCompleted
              : GenStateSuspendedYield;

            if (record.arg === ContinueSentinel) {
              continue;
            }

            return {
              value: record.arg,
              done: context.done
            };

          } else if (record.type === "throw") {
            state = GenStateCompleted;
            // Dispatch the exception by looping back around to the
            // context.dispatchException(context.arg) call above.
            context.method = "throw";
            context.arg = record.arg;
          }
        }
      };
    }

    // Call delegate.iterator[context.method](context.arg) and handle the
    // result, either by returning a { value, done } result from the
    // delegate iterator, or by modifying context.method and context.arg,
    // setting context.delegate to null, and returning the ContinueSentinel.
    function maybeInvokeDelegate(delegate, context) {
      var method = delegate.iterator[context.method];
      if (method === undefined$1) {
        // A .throw or .return when the delegate iterator has no .throw
        // method always terminates the yield* loop.
        context.delegate = null;

        if (context.method === "throw") {
          // Note: ["return"] must be used for ES3 parsing compatibility.
          if (delegate.iterator["return"]) {
            // If the delegate iterator has a return method, give it a
            // chance to clean up.
            context.method = "return";
            context.arg = undefined$1;
            maybeInvokeDelegate(delegate, context);

            if (context.method === "throw") {
              // If maybeInvokeDelegate(context) changed context.method from
              // "return" to "throw", let that override the TypeError below.
              return ContinueSentinel;
            }
          }

          context.method = "throw";
          context.arg = new TypeError(
            "The iterator does not provide a 'throw' method");
        }

        return ContinueSentinel;
      }

      var record = tryCatch(method, delegate.iterator, context.arg);

      if (record.type === "throw") {
        context.method = "throw";
        context.arg = record.arg;
        context.delegate = null;
        return ContinueSentinel;
      }

      var info = record.arg;

      if (! info) {
        context.method = "throw";
        context.arg = new TypeError("iterator result is not an object");
        context.delegate = null;
        return ContinueSentinel;
      }

      if (info.done) {
        // Assign the result of the finished delegate to the temporary
        // variable specified by delegate.resultName (see delegateYield).
        context[delegate.resultName] = info.value;

        // Resume execution at the desired location (see delegateYield).
        context.next = delegate.nextLoc;

        // If context.method was "throw" but the delegate handled the
        // exception, let the outer generator proceed normally. If
        // context.method was "next", forget context.arg since it has been
        // "consumed" by the delegate iterator. If context.method was
        // "return", allow the original .return call to continue in the
        // outer generator.
        if (context.method !== "return") {
          context.method = "next";
          context.arg = undefined$1;
        }

      } else {
        // Re-yield the result returned by the delegate method.
        return info;
      }

      // The delegate iterator is finished, so forget it and continue with
      // the outer generator.
      context.delegate = null;
      return ContinueSentinel;
    }

    // Define Generator.prototype.{next,throw,return} in terms of the
    // unified ._invoke helper method.
    defineIteratorMethods(Gp);

    define(Gp, toStringTagSymbol, "Generator");

    // A Generator should always return itself as the iterator object when the
    // @@iterator function is called on it. Some browsers' implementations of the
    // iterator prototype chain incorrectly implement this, causing the Generator
    // object to not be returned from this call. This ensures that doesn't happen.
    // See https://github.com/facebook/regenerator/issues/274 for more details.
    Gp[iteratorSymbol] = function() {
      return this;
    };

    Gp.toString = function() {
      return "[object Generator]";
    };

    function pushTryEntry(locs) {
      var entry = { tryLoc: locs[0] };

      if (1 in locs) {
        entry.catchLoc = locs[1];
      }

      if (2 in locs) {
        entry.finallyLoc = locs[2];
        entry.afterLoc = locs[3];
      }

      this.tryEntries.push(entry);
    }

    function resetTryEntry(entry) {
      var record = entry.completion || {};
      record.type = "normal";
      delete record.arg;
      entry.completion = record;
    }

    function Context(tryLocsList) {
      // The root entry object (effectively a try statement without a catch
      // or a finally block) gives us a place to store values thrown from
      // locations where there is no enclosing try statement.
      this.tryEntries = [{ tryLoc: "root" }];
      tryLocsList.forEach(pushTryEntry, this);
      this.reset(true);
    }

    exports.keys = function(object) {
      var keys = [];
      for (var key in object) {
        keys.push(key);
      }
      keys.reverse();

      // Rather than returning an object with a next method, we keep
      // things simple and return the next function itself.
      return function next() {
        while (keys.length) {
          var key = keys.pop();
          if (key in object) {
            next.value = key;
            next.done = false;
            return next;
          }
        }

        // To avoid creating an additional object, we just hang the .value
        // and .done properties off the next function object itself. This
        // also ensures that the minifier will not anonymize the function.
        next.done = true;
        return next;
      };
    };

    function values(iterable) {
      if (iterable) {
        var iteratorMethod = iterable[iteratorSymbol];
        if (iteratorMethod) {
          return iteratorMethod.call(iterable);
        }

        if (typeof iterable.next === "function") {
          return iterable;
        }

        if (!isNaN(iterable.length)) {
          var i = -1, next = function next() {
            while (++i < iterable.length) {
              if (hasOwn.call(iterable, i)) {
                next.value = iterable[i];
                next.done = false;
                return next;
              }
            }

            next.value = undefined$1;
            next.done = true;

            return next;
          };

          return next.next = next;
        }
      }

      // Return an iterator with no values.
      return { next: doneResult };
    }
    exports.values = values;

    function doneResult() {
      return { value: undefined$1, done: true };
    }

    Context.prototype = {
      constructor: Context,

      reset: function(skipTempReset) {
        this.prev = 0;
        this.next = 0;
        // Resetting context._sent for legacy support of Babel's
        // function.sent implementation.
        this.sent = this._sent = undefined$1;
        this.done = false;
        this.delegate = null;

        this.method = "next";
        this.arg = undefined$1;

        this.tryEntries.forEach(resetTryEntry);

        if (!skipTempReset) {
          for (var name in this) {
            // Not sure about the optimal order of these conditions:
            if (name.charAt(0) === "t" &&
                hasOwn.call(this, name) &&
                !isNaN(+name.slice(1))) {
              this[name] = undefined$1;
            }
          }
        }
      },

      stop: function() {
        this.done = true;

        var rootEntry = this.tryEntries[0];
        var rootRecord = rootEntry.completion;
        if (rootRecord.type === "throw") {
          throw rootRecord.arg;
        }

        return this.rval;
      },

      dispatchException: function(exception) {
        if (this.done) {
          throw exception;
        }

        var context = this;
        function handle(loc, caught) {
          record.type = "throw";
          record.arg = exception;
          context.next = loc;

          if (caught) {
            // If the dispatched exception was caught by a catch block,
            // then let that catch block handle the exception normally.
            context.method = "next";
            context.arg = undefined$1;
          }

          return !! caught;
        }

        for (var i = this.tryEntries.length - 1; i >= 0; --i) {
          var entry = this.tryEntries[i];
          var record = entry.completion;

          if (entry.tryLoc === "root") {
            // Exception thrown outside of any try block that could handle
            // it, so set the completion value of the entire function to
            // throw the exception.
            return handle("end");
          }

          if (entry.tryLoc <= this.prev) {
            var hasCatch = hasOwn.call(entry, "catchLoc");
            var hasFinally = hasOwn.call(entry, "finallyLoc");

            if (hasCatch && hasFinally) {
              if (this.prev < entry.catchLoc) {
                return handle(entry.catchLoc, true);
              } else if (this.prev < entry.finallyLoc) {
                return handle(entry.finallyLoc);
              }

            } else if (hasCatch) {
              if (this.prev < entry.catchLoc) {
                return handle(entry.catchLoc, true);
              }

            } else if (hasFinally) {
              if (this.prev < entry.finallyLoc) {
                return handle(entry.finallyLoc);
              }

            } else {
              throw new Error("try statement without catch or finally");
            }
          }
        }
      },

      abrupt: function(type, arg) {
        for (var i = this.tryEntries.length - 1; i >= 0; --i) {
          var entry = this.tryEntries[i];
          if (entry.tryLoc <= this.prev &&
              hasOwn.call(entry, "finallyLoc") &&
              this.prev < entry.finallyLoc) {
            var finallyEntry = entry;
            break;
          }
        }

        if (finallyEntry &&
            (type === "break" ||
             type === "continue") &&
            finallyEntry.tryLoc <= arg &&
            arg <= finallyEntry.finallyLoc) {
          // Ignore the finally entry if control is not jumping to a
          // location outside the try/catch block.
          finallyEntry = null;
        }

        var record = finallyEntry ? finallyEntry.completion : {};
        record.type = type;
        record.arg = arg;

        if (finallyEntry) {
          this.method = "next";
          this.next = finallyEntry.finallyLoc;
          return ContinueSentinel;
        }

        return this.complete(record);
      },

      complete: function(record, afterLoc) {
        if (record.type === "throw") {
          throw record.arg;
        }

        if (record.type === "break" ||
            record.type === "continue") {
          this.next = record.arg;
        } else if (record.type === "return") {
          this.rval = this.arg = record.arg;
          this.method = "return";
          this.next = "end";
        } else if (record.type === "normal" && afterLoc) {
          this.next = afterLoc;
        }

        return ContinueSentinel;
      },

      finish: function(finallyLoc) {
        for (var i = this.tryEntries.length - 1; i >= 0; --i) {
          var entry = this.tryEntries[i];
          if (entry.finallyLoc === finallyLoc) {
            this.complete(entry.completion, entry.afterLoc);
            resetTryEntry(entry);
            return ContinueSentinel;
          }
        }
      },

      "catch": function(tryLoc) {
        for (var i = this.tryEntries.length - 1; i >= 0; --i) {
          var entry = this.tryEntries[i];
          if (entry.tryLoc === tryLoc) {
            var record = entry.completion;
            if (record.type === "throw") {
              var thrown = record.arg;
              resetTryEntry(entry);
            }
            return thrown;
          }
        }

        // The context.catch method must only be called with a location
        // argument that corresponds to a known catch block.
        throw new Error("illegal catch attempt");
      },

      delegateYield: function(iterable, resultName, nextLoc) {
        this.delegate = {
          iterator: values(iterable),
          resultName: resultName,
          nextLoc: nextLoc
        };

        if (this.method === "next") {
          // Deliberately forget the last sent value so that we don't
          // accidentally pass it on to the delegate.
          this.arg = undefined$1;
        }

        return ContinueSentinel;
      }
    };

    // Regardless of whether this script is executing as a CommonJS module
    // or not, return the runtime object so that we can declare the variable
    // regeneratorRuntime in the outer scope, which allows this module to be
    // injected easily by `bin/regenerator --include-runtime script.js`.
    return exports;

  }(
    // If this script is executing as a CommonJS module, use module.exports
    // as the regeneratorRuntime namespace. Otherwise create a new empty
    // object. Either way, the resulting object will be used to initialize
    // the regeneratorRuntime variable at the top of this file.
    typeof module === "object" ? module.exports : {}
  ));

  try {
    regeneratorRuntime = runtime;
  } catch (accidentalStrictMode) {
    // This module should not be running in strict mode, so the above
    // assignment should always work unless something is misconfigured. Just
    // in case runtime.js accidentally runs in strict mode, we can escape
    // strict mode using a global Function call. This could conceivably fail
    // if a Content Security Policy forbids using Function, but in that case
    // the proper solution is to fix the accidental strict mode problem. If
    // you've misconfigured your bundler to force strict mode and applied a
    // CSP to forbid Function, and you're not willing to fix either of those
    // problems, please detail your unique predicament in a GitHub issue.
    Function("r", "regeneratorRuntime = r")(runtime);
  }

  var WPStagingBackup;

  (function ($) {
    window.addEventListener('backups-tab', function () {
      WPStagingBackup.fetchListing();
    });
    window.addEventListener('backupListingFinished', function () {
      fetch(ajaxurl + "?action=wpstg--backups--import--file-list&_=" + Math.random() + "&accessToken=" + wpstg.accessToken + "&nonce=" + wpstg.nonce + "&withTemplate=true").then(WPStagingCommon.handleFetchErrors).then(function (res) {
        return res.json();
      }).then(function (res) {
        var $ul = $('.wpstg-backup-list ul');
        $ul.empty();
        $ul.html(res);
      })["catch"](function (e) {
        return WPStagingCommon.showAjaxFatalError(e, '', 'Submit an error report.');
      });
    });
    window.addEventListener('finishedProcess', function () {
      window.removeEventListener('beforeunload', WPStaging.warnIfClosingDuringProcess);
    });
    window.addEventListener('finishedProcessWithError', function () {
      window.removeEventListener('beforeunload', WPStaging.warnIfClosingDuringProcess);
    });
    WPStagingBackup = {
      performingCancelRequest: false,
      isCancelled: false,
      isFinished: false,
      processInfo: {
        title: null,
        interval: null
      },
      modal: {
        create: {
          html: null,
          confirmBtnTxt: null
        },
        process: {
          html: null,
          cancelBtnTxt: null,
          modal: null
        },
        download: {
          html: null
        },
        "import": {
          html: null,
          btnTxtNext: null,
          btnTxtConfirm: null,
          btnTxtCancel: null,
          searchReplaceForm: null,
          containerIntroduction: null,
          data: {
            file: null,
            // file path
            fileObject: null,
            // JS "File" class instance
            search: [],
            replace: [],
            backupMetadata: {}
          },
          baseDirectory: null
        }
      },
      messages: {
        WARNING: 'warning',
        ERROR: 'error',
        INFO: 'info',
        DEBUG: 'debug',
        CRITICAL: 'critical',
        data: {
          all: [],
          info: [],
          error: [],
          critical: [],
          warning: [],
          debug: []
        },
        shouldWarn: function shouldWarn() {
          return WPStagingBackup.messages.data.error.length > 0 || WPStagingBackup.messages.data.critical.length > 0;
        },
        countByType: function countByType(type) {
          if (type === void 0) {
            type = WPStagingBackup.messages.ERROR;
          }

          return WPStagingBackup.messages.data[type].length;
        },
        addMessage: function addMessage(message) {
          if (Array.isArray(message)) {
            message.forEach(function (item) {
              WPStagingBackup.messages.addMessage(item);
            });
            return;
          }

          var type = message.type.toLowerCase() || 'info';

          if (!WPStagingBackup.messages.data[type]) {
            WPStagingBackup.messages.data[type] = [];
          }

          WPStagingBackup.messages.data.all.push(message); // TODO RPoC

          WPStagingBackup.messages.data[type].push(message);
        },
        reset: function reset() {
          WPStagingBackup.messages.data = {
            all: [],
            info: [],
            error: [],
            critical: [],
            warning: [],
            debug: []
          };
        }
      },
      timer: {
        totalSeconds: 0,
        interval: null,
        start: function start() {
          if (null !== WPStagingBackup.timer.interval) {
            return;
          }

          var prettify = function prettify(seconds) {
            console.log("Process running for " + seconds + " seconds"); // If potentially anything can exceed 24h execution time than that;
            // const _seconds = parseInt(seconds, 10)
            // const hours = Math.floor(_seconds / 3600)
            // const minutes = Math.floor(_seconds / 60) % 60
            // seconds = _seconds % 60
            //
            // return [hours, minutes, seconds]
            //   .map(v => v < 10 ? '0' + v : v)
            //   .filter((v,i) => v !== '00' || i > 0)
            //   .join(':')
            // ;
            // Are we sure we won't create anything that exceeds 24h execution time? If not then this;

            return "" + new Date(seconds * 1000).toISOString().substr(11, 8);
          };

          WPStagingBackup.timer.interval = setInterval(function () {
            $('.wpstg--modal--process--elapsed-time').text(prettify(WPStagingBackup.timer.totalSeconds));
            WPStagingBackup.timer.totalSeconds++;
          }, 1000);
        },
        stop: function stop() {
          WPStagingBackup.timer.totalSeconds = 0;

          if (WPStagingBackup.timer.interval) {
            clearInterval(WPStagingBackup.timer.interval);
            WPStagingBackup.timer.interval = null;
          }
        }
      },
      status: {
        hasResponse: null,
        reTryAfter: 5000
      },
      fetchListing: function fetchListing(isResetErrors) {
        if (isResetErrors === void 0) {
          isResetErrors = true;
        }

        WPStagingCommon.isLoading(true);
        $('#backup-messages').text('');

        if (isResetErrors) {
          WPStagingCommon.resetErrors();
        }

        return fetch(ajaxurl + "?action=wpstg--backups--listing&_=" + Math.random() + "&accessToken=" + wpstg.accessToken + "&nonce=" + wpstg.nonce).then(WPStagingCommon.handleFetchErrors).then(function (res) {
          return res.json();
        }).then(function (res) {
          WPStagingCommon.showAjaxFatalError(res, '', 'Submit an error report.');
          WPStagingCommon.cache.get('#wpstg--tab--backup').html(res);
          WPStagingCommon.isLoading(false);
          window.dispatchEvent(new Event('backupListingFinished'));
          return res;
        })["catch"](function (e) {
          return WPStagingCommon.showAjaxFatalError(e, '', 'Submit an error report.');
        });
      },

      /**
       * The cancel & cleanup process when we cancel a backup job or when a backup job is stopped due to an error.
       *
       * @param {Object} extraParams Extra parameter to send during cancel process
       * @param {CallableFunction} callback This function is called when a backup cancel/cleanup process response is returned. This callback is always called even if cancel process fails.
       */
      cancel: function cancel(extraParams, callback) {
        if (extraParams === void 0) {
          extraParams = {};
        }

        if (callback === void 0) {
          callback = null;
        }

        WPStagingBackup.timer.stop();
        clearInterval(WPStagingBackup.processInfo.interval);
        WPStagingBackup.isCancelled = true;
        WPStagingBackup.processInfo.interval = null;
        window.removeEventListener('beforeunload', WPStaging.warnIfClosingDuringProcess);
        WPStagingCommon.closeSwalModal();
        WPStagingCommon.getSwalModal(true).fire({
          title: 'Cancelling & Cleaning up',
          text: 'This modal will close automatically when done...',
          icon: 'info',
          showConfirmButton: false,
          showCloseButton: false,
          showLoaderOnConfirm: false,
          showCancelButton: false,
          allowEscapeKey: false,
          allowOutsideClick: false,
          width: 650,
          onRender: function onRender() {
            WPStagingBackup.sendCancelRequest(Object.assign({
              isInit: 'yes'
            }, extraParams), callback);
          }
        });
      },
      sendCancelRequest: function sendCancelRequest(extraParams, callback) {
        if (WPStagingBackup.performingCancelRequest) {
          return;
        }

        WPStagingBackup.performingCancelRequest = true;
        WPStagingCommon.ajax(Object.assign({
          action: 'wpstg--backups--cancel',
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce
        }, extraParams), function (response) {
          WPStagingBackup.performingCancelRequest = false;
          WPStagingBackup.isCancelled = false;
          WPStagingCommon.showAjaxFatalError(response, '', 'Submit an error report.');

          if (!response.hasOwnProperty('status')) {
            WPStagingCommon.showErrorModal('Cancel process did not finish gracefully. Some temporary files might not have been cleaned up.');
          }

          if (response.status === true) {
            WPStagingCommon.closeSwalModal();

            if (callback !== undefined && callback !== null) {
              callback();
            }
          } else {
            WPStagingBackup.sendCancelRequest(extraParams, callback);
          }
        }, 'json', false, 10, // Don't retry upon failure
        1.25, function (xhr, textStatus, errorThrown) {
          WPStagingBackup.performingCancelRequest = false; // Todo: Why do we need this?

          WPStagingCommon.continueErrorHandle = false; // Resource is busy, wait for request to finish and try again

          if (xhr.status === 423) {
            setTimeout(function () {
              WPStagingBackup.sendCancelRequest(extraParams, callback);
            }, 1000);
            return;
          }

          WPStagingCommon.closeSwalModal();
          WPStagingCommon.showErrorModal('Cancel process did not finish gracefully. Some temporary files might not have been cleaned up.');

          if (callback !== undefined && callback !== null) {
            callback();
          }
        });
      },

      /**
      * If process.execute exists, process.data and process.onResponse is not used
      * process = { data: {}, onResponse: (resp) => {}, onAfterClose: () => {}, execute: () => {}, isShowCancelButton: bool }
      * @param {object} process
      */
      process: function process(_process) {
        if (typeof _process.execute !== 'function' && (!_process.data || !_process.onResponse)) {
          WPStagingCommon.closeSwalModal();
          WPStagingCommon.showError('process.data and / or process.onResponse is not set');
          return;
        } // TODO move to backend and get the contents as xhr response?


        if (!WPStagingBackup.modal.process.html || !WPStagingBackup.modal.process.cancelBtnTxt) {
          var $modal = $('#wpstg--modal--backup--process');
          var html = $modal.html();
          var btnTxt = $modal.attr('data-cancelButtonText');
          WPStagingBackup.modal.process.html = html || null;
          WPStagingBackup.modal.process.cancelBtnTxt = btnTxt || null;
          $modal.remove();
        }

        $('body').off('click', '.wpstg--modal--process--logs--tail').on('click', '.wpstg--modal--process--logs--tail', function (e) {
          var logBtn = $(this);
          e.preventDefault();
          var container = WPStagingCommon.getSwalContainer();
          var $logs = $(container).find('.wpstg--modal--process--logs');
          $logs.toggle();

          if ($logs.is(':visible')) {
            logBtn.text(wpstg.i18n.hideLogs);
            container.childNodes[0].style.width = '97%';
            container.style['z-index'] = 9999;
          } else {
            logBtn.text(wpstg.i18n.showLogs);
            container.childNodes[0].style.width = '600px';
          }
        });
        _process.isShowCancelButton = false !== _process.isShowCancelButton;
        WPStagingBackup.modal.process.modal = WPStagingCommon.getSwalModal(true, {
          content: 'wpstg--process--content'
        }).fire({
          html: WPStagingBackup.modal.process.html,
          cancelButtonText: WPStagingBackup.modal.process.cancelBtnTxt,
          showCancelButton: _process.isShowCancelButton,
          showConfirmButton: false,
          allowOutsideClick: false,
          allowEscapeKey: false,
          width: 600,
          onRender: function onRender() {
            var _btnCancel = WPStagingCommon.getSwalContainer().getElementsByClassName('wpstg--swal2-cancel wpstg--btn--cancel')[0];

            var btnCancel = _btnCancel.cloneNode(true);

            _btnCancel.parentNode.replaceChild(btnCancel, _btnCancel);

            btnCancel.addEventListener('click', function (e) {
              if (confirm('Do you want to cancel the process?')) {
                WPStagingBackup.cancel();
              }
            });

            if (typeof _process.execute === 'function') {
              _process.execute();

              return;
            }

            if (!_process.data || !_process.onResponse) {
              WPStagingCommon.closeSwalModal();
              WPStagingCommon.showError('process.data and / or process.onResponse is not set');
              return;
            }

            WPStagingCommon.ajax(_process.data, _process.onResponse);
          },
          onAfterClose: function onAfterClose() {
            return typeof _process.onAfterClose === 'function' && _process.onAfterClose();
          }
        });
      },
      processResponse: function processResponse(response) {
        if (response === null) {
          WPStagingCommon.closeSwalModal();
          WPStagingCommon.showError('Invalid Response; null');
          throw new Error("Invalid Response; " + response);
        }

        var $container = $(WPStagingCommon.getSwalContainer());

        var title = function title() {
          if (response.title || response.statusTitle) {
            $container.find('.wpstg--modal--process--title').text(response.title || response.statusTitle);
          }
        };

        var percentage = function percentage() {
          if (response.percentage) {
            $container.find('.wpstg--modal--process--percent').text(response.percentage);
          }
        };

        var logs = function logs() {
          if (!response.messages) {
            return;
          }

          var $logsContainer = $container.find('.wpstg--modal--process--logs');
          var stoppingTypes = [WPStagingBackup.messages.ERROR, WPStagingBackup.messages.CRITICAL];

          var appendMessage = function appendMessage(message) {
            if (Array.isArray(message)) {
              for (var _iterator = _createForOfIteratorHelperLoose(message), _step; !(_step = _iterator()).done;) {
                var item = _step.value;
                appendMessage(item);
              }

              return;
            }

            var msgClass = "wpstg--modal--process--msg--" + message.type.toLowerCase();
            $logsContainer.append("<p class=\"" + msgClass + "\">[" + message.type + "] - [" + message.date + "] - " + message.message + "</p>");

            if (stoppingTypes.includes(message.type.toLowerCase())) {
              window.dispatchEvent(new Event('finishedProcessWithError')); // Callback approach is used to make sure the logs modal is always shown,
              // after the completion of cancel/cleanup process.
              // Makes sure cancel/cleanup process doesn't close the logs modal.
              // Any other approach would have required a lot of refactoring.

              WPStagingBackup.cancel({}, function () {
                setTimeout(WPStagingBackup.logsModal, 500);
              });
            }
          };

          for (var _iterator2 = _createForOfIteratorHelperLoose(response.messages), _step2; !(_step2 = _iterator2()).done;) {
            var message = _step2.value;

            if (!message) {
              continue;
            }

            WPStagingBackup.messages.addMessage(message);
            appendMessage(message);
          }

          if ($logsContainer.is(':visible')) {
            $logsContainer.scrollTop($logsContainer[0].scrollHeight);
          }

          if (!WPStagingBackup.messages.shouldWarn()) {
            return;
          }

          var $btnShowLogs = $container.find('.wpstg--modal--process--logs--tail');
          $btnShowLogs.html($btnShowLogs.attr('data-txt-bad'));
          $btnShowLogs.find('.wpstg--modal--logs--critical-count').text(WPStagingBackup.messages.countByType(WPStagingBackup.messages.CRITICAL));
          $btnShowLogs.find('.wpstg--modal--logs--error-count').text(WPStagingBackup.messages.countByType(WPStagingBackup.messages.ERROR));
          $btnShowLogs.find('.wpstg--modal--logs--warning-count').text(WPStagingBackup.messages.countByType(WPStagingBackup.messages.WARNING));
        };

        title();
        percentage();
        logs();

        if (response.status === true && response.job_done === true) {
          WPStagingBackup.timer.stop();
          WPStagingBackup.isCancelled = true;
          window.dispatchEvent(new CustomEvent('finishedProcess', {
            response: response
          }));
        }
      },
      logsModal: function logsModal() {
        WPStagingCommon.getSwalModal(true, {
          popup: 'wpstg-swal-popup centered-modal'
        }).fire({
          html: "<div class=\"wpstg--modal--error--logs\" style=\"display:block\"></div><div class=\"wpstg--modal--process--logs\" style=\"display:block\"></div>",
          width: '97%',
          onRender: function onRender() {
            var $container = $(WPStagingCommon.getSwalContainer());
            $container[0].style['z-index'] = 9999;
            var $logsContainer = $container.find('.wpstg--modal--process--logs');
            var $errorContainer = $container.find('.wpstg--modal--error--logs');
            var $translations = $('#wpstg--js--translations');
            var messages = WPStagingBackup.messages;
            var title = $translations.attr('data-modal-logs-title').replace('{critical}', messages.countByType(messages.CRITICAL)).replace('{errors}', messages.countByType(messages.ERROR)).replace('{warnings}', messages.countByType(messages.WARNING));
            $errorContainer.before("<h3>" + title + "</h3>");
            var warnings = [WPStagingBackup.messages.CRITICAL, WPStagingBackup.messages.ERROR, WPStagingBackup.messages.WARNING];

            if (!WPStagingBackup.messages.shouldWarn()) {
              $errorContainer.hide();
            }

            for (var _iterator3 = _createForOfIteratorHelperLoose(messages.data.all), _step3; !(_step3 = _iterator3()).done;) {
              var message = _step3.value;
              var msgClass = "wpstg--modal--process--msg--" + message.type.toLowerCase();

              if (warnings.includes(message.type)) {
                $errorContainer.append("<p class=\"" + msgClass + "\">[" + message.type + "] - [" + message.date + "] - " + message.message + "</p>");
              }

              $logsContainer.append("<p class=\"" + msgClass + "\">[" + message.type + "] - [" + message.date + "] - " + message.message + "</p>");
            }
          },
          onOpen: function onOpen(container) {
            var $logsContainer = $(container).find('.wpstg--modal--process--logs');
            $logsContainer.scrollTop($logsContainer[0].scrollHeight);
          }
        });
      },
      downloadModal: function downloadModal(_ref) {
        var _ref$title = _ref.title,
            title = _ref$title === void 0 ? null : _ref$title;

        if (null === WPStagingBackup.modal.download.html) {
          var $el = $('#wpstg--modal--backup--download');
          WPStagingBackup.modal.download.html = $el.html();
          $el.remove();
        }

        WPStagingCommon.getSwalModal(true).fire({
          'icon': 'success',
          'html': WPStagingBackup.modal.download.html.replace('{title}', title).replace('{btnTxtLog}', 'Show Logs').replace('{text}', 'You can download or restore this backup at any time.'),
          'confirmButtonText': 'Close',
          'showCancelButton': false,
          'showConfirmButton': true
        });
      },
      statusStop: function statusStop() {
        clearInterval(WPStagingBackup.processInfo.interval);
        WPStagingBackup.processInfo.interval = null;
        window.removeEventListener('beforeunload', WPStaging.warnIfClosingDuringProcess);
      },
      statusStart: function statusStart(process) {
        if (WPStagingBackup.processInfo.interval !== null) {
          return;
        } // console.log('Status: Start 2');


        WPStagingBackup.processInfo.interval = setInterval(function () {
          if (true === WPStagingBackup.isCancelled) {
            WPStagingBackup.statusStop();
            return;
          }

          if (WPStagingBackup.status.hasResponse === false) {
            return;
          }

          WPStagingBackup.status.hasResponse = false;
          fetch(ajaxurl + "?action=wpstg--backups--status&" + process + "=restore&accessToken=" + wpstg.accessToken + "&nonce=" + wpstg.nonce).then(function (res) {
            return res.json();
          }).then(function (res) {
            WPStagingBackup.status.hasResponse = true;

            if (typeof res === 'undefined') {
              WPStagingBackup.statusStop();
            }

            if (WPStagingBackup.processInfo.title === res.currentStatusTitle) {
              return;
            }

            WPStagingBackup.processInfo.title = res.currentStatusTitle;
            var $container = $(WPStagingCommon.getSwalContainer());
            $container.find('.wpstg--modal--process--title').text(res.currentStatusTitle);
            $container.find('.wpstg--modal--process--percent').text('0');
          })["catch"](function (e) {
            WPStagingBackup.status.hasResponse = true;
            WPStagingCommon.showAjaxFatalError(e, '', 'Submit an error report.');
          });
        }, 5000);
      }
    };
  })(jQuery);

  var WPStagingBackup$1 = WPStagingBackup;

  var WPStagingBackupCreate;

  (function ($) {
    window.addEventListener('backups-tab', function () {
      WPStagingBackupCreate.listen();
    });
    window.addEventListener('startedCreatingBackup', function () {
      window.addEventListener('beforeunload', WPStaging.warnIfClosingDuringProcess);
    });
    WPStagingBackupCreate = {
      listen: function listen() {
        $('body').off('click', '#wpstg-new-backup', WPStagingBackupCreate.clickedBackup).on('click', '#wpstg-new-backup', WPStagingBackupCreate.clickedBackup).off('change', '.wpstg--swal2-container .wpstg-advanced-options-site input[type=checkbox]').on('change', '.wpstg--swal2-container .wpstg-advanced-options-site input[type=checkbox]', WPStagingBackupCreate.warnExportMediaWithoutDatabase).off('change', '[name="includedDirectories\[\]"], input#includeDatabaseInBackup, input#includeOtherFilesInWpContent').on('change', '[type="checkbox"][name="includedDirectories\[\]"], input#includeDatabaseInBackup, input#includeOtherFilesInWpContent', WPStagingBackupCreate.disableButtonIfNoSelection);
      },
      clickedBackup: function clickedBackup(e) {
        return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
          var $newBackupModal, html, btnTxt, _yield$WPStagingCommo, formValues;

          return regeneratorRuntime.wrap(function _callee$(_context) {
            while (1) {
              switch (_context.prev = _context.next) {
                case 0:
                  WPStagingCommon.resetErrors();
                  e.preventDefault();
                  WPStagingBackup$1.isCancelled = false;

                  if (!WPStagingBackup$1.modal.create.html || !WPStagingBackup$1.modal.create.confirmBtnTxt) {
                    $newBackupModal = $('#wpstg--modal--backup--new');
                    html = $newBackupModal.html();
                    btnTxt = $newBackupModal.attr('data-confirmButtonText');
                    WPStagingBackup$1.modal.create.html = html || null;
                    WPStagingBackup$1.modal.create.confirmBtnTxt = btnTxt || null;
                    $newBackupModal.remove();
                  }

                  _context.next = 6;
                  return WPStagingCommon.getSwalModal(false, {
                    confirmButton: 'wpstg--btn--confirm wpstg-blue-primary wpstg-button wpstg-link-btn'
                  }).fire({
                    title: '',
                    html: WPStagingBackup$1.modal.create.html,
                    focusConfirm: false,
                    confirmButtonText: WPStagingBackup$1.modal.create.confirmBtnTxt,
                    showCancelButton: true,
                    preConfirm: function preConfirm() {
                      var container = WPStagingCommon.getSwalContainer();
                      return {
                        name: container.querySelector('input[name="backup_name"]').value || null,
                        isExportingPlugins: container.querySelector('#includePluginsInBackup:checked') !== null,
                        isExportingMuPlugins: container.querySelector('#includeMuPluginsInBackup:checked') !== null,
                        isExportingThemes: container.querySelector('#includeThemesInBackup:checked') !== null,
                        isExportingUploads: container.querySelector('#includeMediaLibraryInBackup:checked') !== null,
                        isExportingOtherWpContentFiles: container.querySelector('#includeOtherFilesInWpContent:checked') !== null,
                        isExportingDatabase: container.querySelector('#includeDatabaseInBackup:checked') !== null
                      };
                    }
                  });

                case 6:
                  _yield$WPStagingCommo = _context.sent;
                  formValues = _yield$WPStagingCommo.value;

                  if (formValues) {
                    _context.next = 10;
                    break;
                  }

                  return _context.abrupt("return");

                case 10:
                  WPStagingBackup$1.process({
                    execute: function execute() {
                      WPStagingBackup$1.messages.reset();
                      WPStagingBackupCreate.prepareBackup(formValues);
                    }
                  });

                case 11:
                case "end":
                  return _context.stop();
              }
            }
          }, _callee);
        }))();
      },
      prepareBackup: function prepareBackup(data) {
        WPStagingCommon.ajax({
          action: 'wpstg--backups--prepare-export',
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce,
          wpstgExportData: data
        }, function (response) {
          if (response.success) {
            window.dispatchEvent(new Event('startedCreatingBackup'));
            WPStagingBackup$1.timer.start();
            WPStagingBackupCreate.createBackup();
          } else {
            WPStagingCommon.showAjaxFatalError(response.data, '', 'Submit an error report.');
          }
        }, 'json', false, 10, 1.25, function (xhr, textStatus, errorThrown) {
          if (xhr.status === 423) {
            WPStagingCommon.continueErrorHandle = false;
            WPStagingBackup$1.shouldCleanUp = false;
            WPStagingCommon.closeSwalModal();
            setTimeout(function () {
              WPStagingBackup$1.shouldCleanUp = true;
            }, 1000);
            WPStagingCommon.showErrorModal(xhr.responseJSON.data);
          } else {
            WPStagingCommon.continueErrorHandle = true;
          }
        });
      },
      createBackup: function createBackup() {
        var maxBackupSequentialReturnError = 10;
        var backupReturnedError = 0;
        WPStagingCommon.resetErrors();

        if (WPStagingBackup$1.isCancelled) {
          WPStagingBackup$1.statusStop();
          return;
        }

        WPStagingCommon.ajax({
          action: 'wpstg--backups--export',
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce
        }, function (response) {
          backupReturnedError = 0;

          if (WPStagingBackup$1.isCancelled) {
            return;
          }

          if (typeof response === 'undefined') {
            setTimeout(function () {
              WPStagingBackupCreate.createBackup();
            }, wpstg.delayReq);
            return;
          }

          WPStagingBackup$1.processResponse(response);

          if (!WPStagingBackup$1.processInfo.interval) {
            WPStagingBackup$1.statusStart('create');
          }

          if (response.statusTitle === 'JOB_FAIL') {
            response.messages.push({
              'type': 'critical',
              'message': 'There was a PHP fatal error while processing the backup. You might need to check your PHP error log to find out the reason. Please contact WPSTAGING support if you need help.'
            });
            WPStagingBackup$1.processResponse(response);
            WPStagingBackup$1.cancel();
            setTimeout(WPStagingBackup$1.logsModal, 500);
          } else if (response.statusTitle === 'JOB_RETRY') {
            WPStagingBackup$1.processResponse(response);
            WPStagingBackupCreate.createBackup();
          }

          if (response.status === false) {
            WPStagingBackupCreate.createBackup();
          } else if (response.status === true) {
            $('#wpstg--progress--status').text('Backup successfully created!');

            if (WPStagingBackup$1.messages.shouldWarn()) {
              // noinspection JSIgnoredPromiseFromCall
              WPStagingBackup$1.fetchListing();
              WPStagingBackup$1.logsModal();
              return;
            }

            WPStagingBackup$1.statusStop();
            WPStagingCommon.closeSwalModal();
            WPStagingBackup$1.fetchListing().then(function () {
              if (!response.backupMd5) {
                WPStagingCommon.showError('Failed to get backup md5 from response');
                return;
              } // Wait for fetchListing to populate the DOM with the backup data that we want to read


              var $el = '';
              var timesWaited = 0;
              var intervalWaitForBackupInDom = setInterval(function () {
                timesWaited++;
                $el = $(".wpstg-backup[data-md5=\"" + response.backupMd5 + "\"] .wpstg--backup--download"); // Could not find element, let's try again...

                if (!$el.length) {
                  if (timesWaited >= 20) {
                    // Bail: We tried too many times and couldn't find.
                    clearInterval(intervalWaitForBackupInDom);
                  }

                  return;
                } // Found it. No more need for the interval.


                clearInterval(intervalWaitForBackupInDom);
                response.hasOwnProperty('backupSize') ? ' (' + response.backupSize + ')' : '';
                WPStagingBackup$1.downloadModal({
                  title: 'Backup Complete'
                });
                $('.wpstg--modal--download--logs--wrapper').show();
                var $logsContainer = $('.wpstg--modal--process--logs');
                WPStagingBackup$1.messages.data.all.forEach(function (message) {
                  var msgClass = "wpstg--modal--process--msg--" + message.type.toLowerCase();
                  $logsContainer.append("<p class=\"" + msgClass + "\">[" + message.type + "] - [" + message.date + "] - " + message.message + "</p>");
                });
              }, 500);
            });
          } else {
            setTimeout(function () {
              WPStagingBackupCreate.createBackup();
            }, wpstg.delayReq);
          }
        }, 'json', false, 0, // Don't retry upon failure
        1.25, function (xhr, textStatus, errorThrown) {
          WPStagingCommon.continueErrorHandle = false;
          console.log(xhr);
          var response = {
            'messages': []
          };
          backupReturnedError++; // Prevents loop in case PHP crashes constantly before being able to respond

          if (backupReturnedError <= maxBackupSequentialReturnError) {
            WPStagingBackupCreate.createBackup();
          } else {
            response.messages.push({
              'type': 'critical',
              'message': 'There was a PHP fatal error while processing the backup. You might need to check your PHP error log to find out the reason. Please contact WPSTAGING support if you need help.'
            });
            WPStagingBackup$1.processResponse(response);
            WPStagingBackup$1.cancel();
            setTimeout(WPStagingBackup$1.logsModal, 500);
          }
        });
      },
      warnExportMediaWithoutDatabase: function warnExportMediaWithoutDatabase() {
        var isExportingDatabase = document.getElementById('includeDatabaseInBackup').checked;
        var isExportingMediaLibrary = document.getElementById('includeMediaLibraryInBackup').checked;

        if (isExportingMediaLibrary && !isExportingDatabase) {
          document.getElementById('exportUploadsWithoutDatabaseWarning').style.display = 'block';
        } else {
          document.getElementById('exportUploadsWithoutDatabaseWarning').style.display = 'none';
        }
      },
      disableButtonIfNoSelection: function disableButtonIfNoSelection() {
        var isExportingAnyDir = $('[type="checkbox"][name="includedDirectories\[\]"]:checked').length > 0;
        var isExportingDatabase = $('input#includeDatabaseInBackup:checked').length === 1;
        var isExportingOtherFilesInWpContent = $('input#includeOtherFilesInWpContent:checked').length === 1;

        if (!isExportingAnyDir && !isExportingDatabase && !isExportingOtherFilesInWpContent) {
          $('.wpstg--swal2-confirm').prop('disabled', true);
        } else {
          $('.wpstg--swal2-confirm').prop('disabled', false);
        }
      }
    };
  })(jQuery);

  var WPStagingBackupDelete;

  (function ($) {
    window.addEventListener('backups-tab', function () {
      WPStagingBackupDelete.listen();
    });
    WPStagingBackupDelete = {
      listen: function listen() {
        $('#wpstg--tab--backup').off('click', '.wpstg-delete-backup[data-md5]', WPStagingBackupDelete["delete"]).on('click', '.wpstg-delete-backup[data-md5]', WPStagingBackupDelete["delete"]);
      },
      "delete": function _delete(e) {
        var _this = this;

        e.preventDefault();
        WPStagingCommon.resetErrors();
        var htmlContent = 'Are you sure you want to delete this backup?';
        WPStagingCommon.getSwalModal(false, {
          confirmButton: 'wpstg--btn--confirm wpstg-btn-danger wpstg-link-btn'
        }).fire({
          showConfirmButton: true,
          showCancelButton: true,
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Delete',
          icon: 'warning',
          title: 'Delete Backup!',
          html: '<div class="wpstg--modal--delete">' + htmlContent + '</div>'
        }).then(function (result) {
          if (!result.value) {
            return;
          }

          WPStagingCommon.isLoading(true);
          WPStagingCommon.cache.get('#wpstg-existing-backups').hide();

          var md5 = _this.getAttribute('data-md5');

          WPStagingCommon.ajax({
            action: 'wpstg--backups--delete',
            md5: md5,
            accessToken: wpstg.accessToken,
            nonce: wpstg.nonce
          }, function (response) {
            WPStagingCommon.showAjaxFatalError(response, '', ' Please submit an error report by using the REPORT ISSUE button.');
            WPStagingCommon.isLoading(false);
            WPStagingBackup$1.fetchListing();
          });
        });
      }
    };
  })(jQuery);

  var WPStagingBackupEdit;

  (function ($) {
    window.addEventListener('backups-tab', function () {
      WPStagingBackupEdit.listen();
    });
    WPStagingBackupEdit = {
      listen: function listen() {
        $('#wpstg--tab--backup').off('click', '.wpstg--backup--edit[data-md5]', WPStagingBackupEdit.edit).on('click', '.wpstg--backup--edit[data-md5]', WPStagingBackupEdit.edit);
      },
      edit: function edit(e) {
        var _this = this;

        return _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
          var $this, name, notes, _yield$WPStagingCommo, formValues;

          return regeneratorRuntime.wrap(function _callee$(_context) {
            while (1) {
              switch (_context.prev = _context.next) {
                case 0:
                  e.preventDefault();
                  $this = $(_this);
                  name = $this.data('name');
                  notes = $this.data('notes');
                  _context.next = 6;
                  return WPStagingCommon.getSwalModal().fire({
                    title: '',
                    html: "\n                    <label id=\"wpstg-backup-edit-name\">Backup Name</label>\n                    <input id=\"wpstg-backup-edit-name-input\" class=\"wpstg--swal2-input\" value=\"" + name + "\" maxlength=\"100\">\n                    <label>Additional Notes</label>\n                    <textarea id=\"wpstg-backup-edit-notes-textarea\" class=\"wpstg--swal2-textarea\" maxlength=\"1000\">" + notes + "</textarea>\n                  ",
                    focusConfirm: false,
                    confirmButtonText: 'Save',
                    showCancelButton: true,
                    preConfirm: function preConfirm() {
                      return {
                        name: document.getElementById('wpstg-backup-edit-name-input').value || null,
                        notes: document.getElementById('wpstg-backup-edit-notes-textarea').value || null
                      };
                    }
                  });

                case 6:
                  _yield$WPStagingCommo = _context.sent;
                  formValues = _yield$WPStagingCommo.value;

                  if (formValues) {
                    _context.next = 10;
                    break;
                  }

                  return _context.abrupt("return");

                case 10:
                  WPStagingCommon.ajax({
                    action: 'wpstg--backups--edit',
                    accessToken: wpstg.accessToken,
                    nonce: wpstg.nonce,
                    md5: $this.data('md5'),
                    name: formValues.name,
                    notes: formValues.notes
                  }, function (response) {
                    WPStagingCommon.showAjaxFatalError(response, '', 'Submit an error report.'); // noinspection JSIgnoredPromiseFromCall

                    WPStagingBackup$1.fetchListing();
                  });

                case 11:
                case "end":
                  return _context.stop();
              }
            }
          }, _callee);
        }))();
      }
    };
  })(jQuery);

  var WPStagingBackupRestore;

  (function ($) {
    window.addEventListener('backups-tab', function () {
      WPStagingBackupRestore.listen();
    });
    WPStagingBackupRestore = {
      listen: function listen() {
        $('body').off('click', '.wpstg--backup--restore', WPStagingBackupRestore.clickedRestore).on('click', '.wpstg--backup--restore', WPStagingBackupRestore.clickedRestore);
      },
      clickedRestore: function clickedRestore(e) {
        WPStagingCommon.resetErrors();
        e.preventDefault();
        WPStagingBackup$1.isCancelled = false;
        WPStagingBackup$1.modal["import"].data.file = this.getAttribute('data-filePath');
        WPStagingBackupRestore.selectedBackup();
      },
      selectedBackup: function selectedBackup() {
        WPStagingCommon.ajax({
          action: 'wpstg--backups--read-backup-metadata',
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce,
          wpstg: {
            file: WPStagingBackup$1.modal["import"].data.file
          }
        }, function (response) {
          var responseData;

          try {
            responseData = JSON.parse(WPStagingCommon.getDataFromWordPressResponse(response)); // Get the first item of the blogs object:
            // blogs {1: {someData}} => {someData}

            var mainNetwork = responseData.networks[Object.keys(responseData.networks)[0]];
            var mainBlog = mainNetwork.blogs[Object.keys(mainNetwork.blogs)[0]];
            responseData = mainBlog;
          } catch (e) {
            WPStagingCommon.showError(e);
          }

          WPStagingBackup$1.modal["import"].data.backupMetadata = responseData;
          var postData = new FormData();
          postData.append('action', 'wpstg--backups--import--file-info');
          postData.append('accessToken', wpstg.accessToken);
          postData.append('nonce', wpstg.nonce);
          postData.append('filePath', WPStagingBackup$1.modal["import"].data.file);
          fetch(ajaxurl, {
            method: 'POST',
            body: postData
          }).then(WPStagingCommon.handleFetchErrors).then(function (res) {
            return res.json();
          }).then(function (html) {
            WPStagingBackupRestore.importModal(html, postData);
          })["catch"](function (e) {
            return WPStagingCommon.showAjaxFatalError(e, '', 'Submit an error report.');
          });
          $('.wpstg--modal--actions .wpstg--swal2-confirm').show();
          $('.wpstg--modal--actions .wpstg--swal2-confirm').prop('disabled', false);
        });
      },
      importModal: function importModal(confirmHtml) {
        var importSiteBackup = function importSiteBackup(data) {
          WPStagingCommon.resetErrors();

          if (WPStagingBackup$1.isCancelled) {
            // console.log('cancelled');
            WPStagingBackup$1.statusStop();
            return;
          }

          WPStagingBackup$1.timer.start();
          window.addEventListener('beforeunload', WPStaging.warnIfClosingDuringProcess);
          WPStagingCommon.ajax({
            action: 'wpstg--backups--import',
            accessToken: wpstg.accessToken,
            nonce: wpstg.nonce
          }, function (response) {
            if (WPStagingBackup$1.isCancelled) {
              return;
            }

            if (typeof response === 'undefined') {
              setTimeout(function () {
                importSiteBackup();
              }, wpstg.delayReq);
              return;
            }

            WPStagingBackup$1.processResponse(response);

            if (!WPStagingBackup$1.processInfo.interval) {
              WPStagingBackup$1.statusStart('restore');
            }

            if (response.status === false) {
              importSiteBackup();
            } else if (response.status === true) {
              window.removeEventListener('beforeunload', WPStaging.warnIfClosingDuringProcess);
              $('#wpstg--progress--status').text('Backup successfully imported!');

              if (WPStagingBackup$1.messages.shouldWarn()) {
                WPStagingBackup$1.logsModal();
                return;
              }

              WPStagingBackup$1.statusStop();
              var logEntries = $('.wpstg--modal--process--logs').get(1).innerHTML;
              var html = '<div class="wpstg--modal--process--logs">' + logEntries + '</div>';
              var issueFound = html.includes('wpstg--modal--process--msg--warning') || html.includes('wpstg--modal--process--msg--error') ? 'Issues(s) found!<br> ' : '';
              var loginText = WPStagingBackup$1.modal["import"].data.backupMetadata.isExportingDatabase ? 'You will be redirected to the login page after closing this modal.' : ''; // TODO: remove default classes.

              WPStagingCommon.getSwalModal(true, {
                container: 'wpstg-restore-finished-container'
              }).fire({
                icon: 'success',
                title: 'Finished Successfully',
                html: '<div id="wpstg-restore-success" class="wpstg--grey">Site has been restored from backup. ' + loginText + '  <br/><br/><span class="wpstg--modal--process--msg-found">' + issueFound + '</span><button class="wpstg--modal--process--logs--tail" data-txt-bad="">Show Logs</button></div><br/>' + html,
                onClose: function onClose() {
                  if (WPStagingBackup$1.modal["import"].data.backupMetadata.isExportingDatabase) {
                    document.getElementById('wpstg-restore-wait').style.display = 'flex'; // Reload in 15 seconds if something goes wrong with the Ajax call.

                    setTimeout(function () {
                      document.location.reload(true);
                    }, 15000);
                    WPStagingCommon.ajax({
                      action: 'raw_wpstg--backups--login-url',
                      accessToken: wpstg.accessToken,
                      nonce: wpstg.nonce
                    }, function (response) {
                      var loginUrl = new URL(response.data.loginUrl);
                      loginUrl.searchParams.set('accessToken', wpstg.accessToken);
                      loginUrl.searchParams.set('wpstgAfterImport', 'yes');
                      window.location.href = loginUrl.toString();
                    });
                  } else {
                    WPStagingBackup$1.fetchListing();
                  }
                }
              });
            } else {
              setTimeout(function () {
                importSiteBackup();
              }, wpstg.delayReq);
            }
          }, 'json', false, 10, // Don't retry upon failure
          1.25);
        };

        var $modal = $('#wpstg--modal--backup--import');
        WPStagingBackup$1.modal["import"].html = $modal.html();
        WPStagingBackup$1.modal["import"].baseDirectory = $modal.attr('data-baseDirectory');
        WPStagingBackup$1.modal["import"].btnTxtNext = $modal.attr('data-nextButtonText');
        WPStagingBackup$1.modal["import"].btnTxtConfirm = $modal.attr('data-confirmButtonText');
        WPStagingBackup$1.modal["import"].btnTxtCancel = $modal.attr('data-cancelButtonText');
        WPStagingCommon.getSwalModal().mixin({
          progressSteps: ['1', '2']
        }).queue([{
          html: $('.wpstg--modal--backup--import--introduction').first(),
          confirmButtonText: 'Next',
          showCancelButton: true,
          showConfirmButton: true,
          reverseButtons: true,
          width: 600,
          onRender: function onRender() {
            $('.wpstg--modal--backup--import--introduction .wpstg-backup-restore-contains-database').hide();
            $('.wpstg--modal--backup--import--introduction .wpstg-backup-restore-contains-files').hide();

            if (WPStagingBackup$1.modal["import"].data.backupMetadata.isExportingDatabase) {
              $('.wpstg--modal--backup--import--introduction .wpstg-backup-restore-contains-database').show();
            }

            if (WPStagingBackup$1.modal["import"].data.backupMetadata.isExportingMuPlugins || WPStagingBackup$1.modal["import"].data.backupMetadata.isExportingPlugins || WPStagingBackup$1.modal["import"].data.backupMetadata.isExportingThemes || WPStagingBackup$1.modal["import"].data.backupMetadata.isExportingUploads || WPStagingBackup$1.modal["import"].data.backupMetadata.isExportingOtherWpContentFiles) {
              $('.wpstg--modal--backup--import--introduction .wpstg-backup-restore-contains-files').show();
            }
          }
        }, {
          html: confirmHtml,
          confirmButtonText: 'Restore',
          showCancelButton: true,
          showConfirmButton: true,
          reverseButtons: true,
          width: 650
        }]).then(function (res) {
          // Early bail: Dismissed, canceled, or in a incoherent state.
          if (!res || !res.value) {
            return;
          } // Early bail: Step 1 (Confirm) must be true.


          if (!res.value[1] || res.value[1] !== true) {
            return;
          }

          WPStagingBackup$1.isCancelled = false;
          var data = WPStagingBackup$1.modal["import"].data; // Unset fileObject as we don't need it anymore, and it can't be converted to array.

          WPStagingBackup$1.modal["import"].data.fileObject = null;
          data['file'] = WPStagingBackup$1.modal["import"].baseDirectory + data['file'];
          WPStagingCommon.ajax({
            action: 'wpstg--backups--prepare-import',
            accessToken: wpstg.accessToken,
            nonce: wpstg.nonce,
            wpstgImportData: data
          }, function (response) {
            if (response.success) {
              WPStagingBackup$1.process({
                execute: function execute() {
                  WPStagingBackup$1.messages.reset();
                  importSiteBackup();
                }
              });
            } else {
              WPStagingCommon.showAjaxFatalError(response.data, '', 'Submit an error report.');
            }
          }, 'json', false, 10, 1.25, function (xhr, textStatus, errorThrown) {
            if (xhr.status === 423) {
              WPStagingCommon.continueErrorHandle = false;
              WPStagingBackup$1.shouldCleanUp = false;
              WPStagingCommon.closeSwalModal();
              setTimeout(function () {
                WPStagingBackup$1.shouldCleanUp = true;
              }, 1000);
              WPStagingCommon.showErrorModal(xhr.responseJSON.data);
            } else {
              WPStagingCommon.continueErrorHandle = true;
            }
          });
        });
      }
    };
  })(jQuery);

  var WPStagingBackupUpload;

  (function ($) {
    window.addEventListener('backups-tab', function () {
      WPStagingBackupUpload.listen();
    });
    WPStagingBackupUpload = {
      isUploading: false,
      resumable: null,
      resumableFile: null,
      listen: function listen() {
        document.body.addEventListener('click', function (event) {
          if (event.target.id === 'wpstg-upload-backup') {
            WPStagingBackupUpload.uploadModal();
          }
        });
      },

      /**
       * @see https://caniuse.com/fileapi
       */
      uploadNotSupported: function uploadNotSupported() {
        WPStagingCommon.showErrorModal('Your browser do not support the File API, needed for the uploads. Please try a different/updated browser, or upload the Backup using FTP to the folder <strong>wp-content/uploads/wp-staging/backups</strong>');
      },
      setModalStateStart: function setModalStateStart() {
        WPStagingBackupUpload.isUploading = false;
        qs('.wpstg--swal2-container #wpstg-upload-select').style.display = 'block';
        qs('.wpstg--swal2-container #wpstg-upload-progress').style.display = 'none';
        qs('.wpstg--swal2-container .wpstg--modal--backup--import--upload--content .wpstg-linear-loader').style.display = 'none';
      },
      setModalStateUploading: function setModalStateUploading() {
        WPStagingBackupUpload.isUploading = true;
        qs('.wpstg--swal2-container #wpstg-upload-select').style.display = 'none';
        qs('.wpstg--swal2-container #wpstg-upload-progress').style.display = 'block';
        qs('.wpstg--swal2-container .wpstg--modal--backup--import--upload--content .wpstg-linear-loader').style.display = 'block';
        qs('.wpstg--swal2-container .wpstg--modal--import--upload--process').style.display = 'flex';
        var _btnCancel = WPStagingCommon.getSwalContainer().getElementsByClassName('wpstg--swal2-cancel wpstg--btn--cancel')[0];

        var btnCancel = _btnCancel.cloneNode(true);

        _btnCancel.parentNode.replaceChild(btnCancel, _btnCancel);

        btnCancel.addEventListener('click', WPStagingBackupUpload.eventListenerConfirmCancelUpload);
      },
      eventListenerConfirmCancelUpload: function eventListenerConfirmCancelUpload() {
        if (confirm('Do you want to abort the upload?')) {
          WPStagingBackupUpload.resumable.cancel();
          WPStagingCommon.closeSwalModal();
          WPStagingBackupUpload.deleteUnfinishedUploads();
        }
      },
      deleteUnfinishedUploads: function deleteUnfinishedUploads() {
        WPStagingCommon.ajax({
          action: 'wpstg--backups--uploads-delete-unfinished',
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce
        }, function (response) {// no-op
        });
      },
      uploadModal: function uploadModal() {
        WPStagingCommon.getSwalModal(true).fire({
          html: document.getElementById('wpstg--modal--backup--upload').innerHTML,
          showConfirmButton: false,
          showCloseButton: false,
          showLoaderOnConfirm: false,
          showCancelButton: true,
          width: 650,
          allowEscapeKey: function allowEscapeKey() {
            return !WPStagingBackupUpload.isUploading;
          },
          allowOutsideClick: function allowOutsideClick() {
            return !WPStagingBackupUpload.isUploading;
          },
          onRender: function onRender() {
            WPStagingBackupUpload.deleteUnfinishedUploads();
            WPStagingBackupUpload.setModalStateStart();
            WPStagingBackupUpload.resumable = new Resumable({
              target: wpstg.ajaxUrl,
              chunkSize: wpstg.maxUploadChunkSize,
              query: {
                accessToken: wpstg.accessToken,
                action: 'wpstg--backups--import--file-upload',
                uniqueIdentifierSuffix: Math.floor(Math.random() * 99999999)
              },
              permanentErrors: [400, 401, 403, 404, 409, 415, 500, 501, 507, 413],
              // These will STOP the upload. Other errors will retry the chunk.
              fileType: ['wpstg'],
              simultaneousUploads: 1,
              prioritizeFirstAndLastChunk: false,
              testChunks: false,
              forceChunkSize: true
            });
            WPStagingBackupUpload.resumable.resumableFile = null;

            if (!WPStagingBackupUpload.resumable.support) {
              WPStagingCommon.closeSwalModal();
              WPStagingBackupUpload.uploadNotSupported();
              return;
            }

            WPStagingBackupUpload.resumable.assignBrowse(qs('.wpstg--swal2-container .resumable-browse'));
            WPStagingBackupUpload.resumable.assignDrop(qs('.wpstg--swal2-container .resumable-drop'));
            WPStagingBackupUpload.resumable.on('fileAdded', WPStagingBackupUpload.handleFileAdded);
            WPStagingBackupUpload.resumable.on('fileSuccess', WPStagingBackupUpload.handleFileSuccess);
            WPStagingBackupUpload.resumable.on('error', WPStagingBackupUpload.handleError);
            WPStagingBackupUpload.resumable.on('progress', WPStagingBackupUpload.handleProgress);
          }
        });
      },
      handleFileAdded: function handleFileAdded(file) {
        WPStagingBackupUpload.setModalStateUploading();
        WPStagingBackupUpload.resumable.resumableFile = file;
        WPStagingBackupUpload.resumable.upload();
      },
      handleFileSuccess: function handleFileSuccess(file, message) {
        WPStagingCommon.closeSwalModal();
        WPStagingCommon.showSuccessModal('Upload finished!');
        WPStagingBackup$1.fetchListing();
      },
      handleProgress: function handleProgress() {
        var percent = Math.floor(WPStagingBackupUpload.resumable.progress() * 100);
        qs('.wpstg--swal2-container .wpstg--modal--import--upload--progress').style.width = percent + '%';
        qs('.wpstg--swal2-container .wpstg--modal--import--upload--progress--title > span').textContent = percent + '%';
      },
      handleError: function handleError(message, file) {
        try {
          var json = JSON.parse(message);

          if (json.hasOwnProperty('data') && json.data.hasOwnProperty('message')) {
            if (json.data.hasOwnProperty('isDiskFull') && json.data.isDiskFull) {
              WPStagingBackupUpload.resumable.cancel();
              WPStagingCommon.closeSwalModal();
              WPStagingCommon.showErrorModal(json.data.message);
              return;
            }

            if (json.data.hasOwnProperty('backupFailedValidation') && json.data.backupFailedValidation) {
              WPStagingBackupUpload.resumable.cancel();
              WPStagingCommon.closeSwalModal();
              WPStagingCommon.showErrorModal(json.data.message);
              return;
            }
          }
        } catch (e) {// not a JSON message, no-op
        } // Reduce chunk by 10%, with a minimum of 256kb reduction


        var reduceChunkBytes = Math.max(256 * 1024, WPStagingBackupUpload.resumable.opts.chunkSize * 0.1);
        WPStagingBackupUpload.resumable.opts.chunkSize = Math.ceil(WPStagingBackupUpload.resumable.opts.chunkSize - reduceChunkBytes);

        if (WPStagingBackupUpload.resumable.opts.chunkSize < 64 * 1024) {
          WPStagingCommon.closeSwalModal();
          WPStagingCommon.showErrorModal('We could not upload the backup file, please try uploading it directly using FTP to the folder wp-content/uploads/wp-staging/backups. Please also make sure you have enough free disk space on the server.');
        }

        WPStagingBackupUpload.deleteUnfinishedUploads();
        console.log(WPStagingBackupUpload.resumable.opts.chunkSize);
        WPStagingBackupUpload.resumable.files[0].retry();
      }
    };
  })(jQuery);

}());
//# sourceMappingURL=wpstg-backup.js.map
