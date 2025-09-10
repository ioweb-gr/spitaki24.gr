(function( $ ) {
    'use strict';

    // Create the defaults once
    var pluginName = "gdpr",
        defaults = {
            bla: "",
        };

    // The actual plugin constructor
    function Plugin ( element, options ) {
        this.element = element;
        this.settings = $.extend( {}, defaults, options );
        this._defaults = defaults;

        this._name = pluginName;
        this.init();
    }

    // Avoid Plugin.prototype conflicts
    $.extend( Plugin.prototype, {
        init: function() {
            var that = this;
            this.window = $(window);
            this.documentHeight = $( document ).height();
            this.windowHeight = this.window.height();
            this.privacySettingsCheckPerformed = false;

            this.elements = {};
            this.elements.popUp = $('.wordpress-gdpr-popup-container');
            this.elements.popUpAgreeLink = $('.wordpress-gdpr-popup-agree');
            this.elements.popUpDeclineLink = $('.wordpress-gdpr-popup-decline');
            this.elements.popUpCloseLink = $('.wordpress-gdpr-popup-close');
            this.elements.popUpBackdrop = $('.wordpress-gdpr-popup-overlay-backdrop');

            this.privacySettingsLoaded = {};
            this.elements.privacySettingsPopupTrigger = $('.wordpress-gdpr-privacy-settings-trigger-container');
            this.elements.privacySettingsPopup = $('.wordpress-gdpr-privacy-settings-popup');
            this.elements.privacySettingsPopupPrivacySettings = $('.wordpress-gdpr-privacy-settings-popup-privacy-settings-modal');
            this.elements.privacySettingsPopupAgreeLink = $('.wordpress-gdpr-privacy-settings-popup-agree');
            this.elements.privacySettingsPopupDeclineLink = $('.wordpress-gdpr-privacy-settings-popup-decline');
            this.elements.privacySettingsPopupCloseLink = $('.wordpress-gdpr-privacy-settings-popup-close');
            this.elements.privacySettingsPopupSaveLink = $('.wordpress-gdpr-privacy-settings-popup-services-save');
            
            this.elements.privacySettingsPopupBackdrop = $('.wordpress-gdpr-privacy-settings-popup-backdrop');

            if(this.settings.geoIP == "1") {
                this.getUsersCountry(function(country) {
                    
                    var euCountries = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB', 'NO', 'LI', 'IS'];
                    var isEU = that.isInArray(country, euCountries);
                    if(isEU) {
                        that.popUp();
                    } else {
                        $('.wordpress-gdpr-popup-actions-buttons .wordpress-gdpr-popup-agree').trigger('click');
                        that.elements.privacySettingsPopupTrigger.remove();
                    }
                });
                
            } else {
                this.popUp();
            }

            this.popUpAgree();
            this.popUpClose();
            this.popUpDecline();

            this.popUpPrivacySettings();
            this.popUpPrivacySettingsOpen();
            this.popUpPrivacySettingsClose();

            this.privacyPolicyTermsAcceptance();
            this.commentFormprivacyPolicyTermsAcceptance();
        },
        popUp : function() {

            var botPattern = "(craw|googlebot\/|bot|Googlebot-Mobile|Googlebot-Image|Google favicon|Mediapartners-Google|bingbot|Slurp|Java|wget|curl|Commons-HttpClient|Python-urllib|libwww|httpunit|nutch|phpcrawl|msnbot|jyxobot|FAST-WebCrawler|FAST Enterprise Crawler|biglotron|teoma|convera|seekbot|gigablast|exabot|ngbot|ia_archiver|GingerCrawler|webmon |httrack|webcrawler|grub.org|UsineNouvelleCrawler|antibot|netresearchserver|speedy|fluffy|bibnum.bnf|findlink|msrbot|panscient|yacybot|AISearchBot|IOI|ips-agent|tagoobot|MJ12bot|dotbot|woriobot|yanga|buzzbot|mlbot|yandexbot|purebot|Linguee Bot|Voyager|CyberPatrol|voilabot|baiduspider|citeseerxbot|spbot|twengabot|postrank|turnitinbot|scribdbot|page2rss|sitebot|linkdex|Adidxbot|blekkobot|ezooms|dotbot|Mail.RU_Bot|discobot|heritrix|findthatfile|europarchive.org|NerdByNature.Bot|sistrix crawler|ahrefsbot|Aboundex|domaincrawler|wbsearchbot|summify|ccbot|edisterbot|seznambot|ec2linkfinder|gslfbot|aihitbot|intelium_bot|facebookexternalhit|yeti|RetrevoPageAnalyzer|lb-spider|sogou|lssbot|careerbot|wotbox|wocbot|ichiro|DuckDuckBot|lssrocketcrawler|drupact|webcompanycrawler|acoonbot|openindexspider|gnam gnam spider|web-archive-net.com.bot|backlinkcrawler|coccoc|integromedb|content crawler spider|toplistbot|seokicks-robot|it2media-domain-crawler|ip-web-crawler.com|lighthouse|siteexplorer.info|elisabot|proximic|changedetection|blexbot|arabot|WeSEE:Search|niki-bot|CrystalSemanticsBot|rogerbot|360Spider|psbot|InterfaxScanBot|Lipperhey SEO Service|CC Metadata Scaper|g00g1e.net|GrapeshotCrawler|urlappendbot|brainobot|fr-crawler|binlar|SimpleCrawler|Livelapbot|Twitterbot|cXensebot|smtbot|bnf.fr_bot|A6-Indexer|ADmantX|Facebot|Twitterbot|OrangeBot|memorybot|AdvBot|MegaIndex|SemanticScholarBot|ltx71|nerdybot|xovibot|BUbiNG|Qwantify|archive.org_bot|Applebot|TweetmemeBot|crawler4j|findxbot|SemrushBot|yoozBot|lipperhey|y!j-asr|Domain Re-Animator Bot|AddThis|bot|googlebot|crawler|spider|robot|crawling)";
            var re = new RegExp(botPattern, 'i');
            if (re.test(navigator.userAgent)) {
                return false;
            }

            var that = this;
            var cookiesAllowed = false;


            if(localStorage && that.getParameterByName('gdpr') !== "debug" && ( that.getCookie('wordpress_gdpr_cookies_allowed') && that.getCookie('wordpress_gdpr_cookies_declined') ) ) {

                var retrievedObject = localStorage.getItem('wordpress_gdpr_services_allowed_temp');
                if(retrievedObject) {

                    var services = JSON.parse(retrievedObject);
                    if(services) { 

                        if(services.version == that.settings.version) {

                            that.executeServices(services);
                            return;

                        } else {

                            if(that.elements.popUpBackdrop.length > 0) {
                                that.elements.popUpBackdrop.show();
                            }

                            that.elements.popUp.show();
                            that.checkPrivacySettings();
                            return;
                        }
                    } else {
                        
                        if(that.elements.popUpBackdrop.length > 0) {
                            that.elements.popUpBackdrop.show();
                        }
                        that.elements.popUp.show();
                        that.checkPrivacySettings();
                        return;
                    }

                }
            }

            $.ajax({
                type : 'post',
                url : that.settings.ajaxURL,
                dataType : 'json',
                data : {
                    action : 'check_privacy_setting',
                    setting : 'wordpress_gdpr_cookies_allowed',
                    current_page_id : that.get_current_page_id()
                },
                success : function( response ) {     

                    var popupExcludePages = that.settings.popupExcludePages;
                    var exclude = false;
                    if(!that.isEmpty(popupExcludePages)) {
                        var currentPage, matches = document.body.className.match(/(^|\s)post-id-(\d+)(\s|$)/);
                        if (matches) {
                            currentPage = matches[2];
                        } else {
                            matches = document.body.className.match(/(^|\s)page-id-(\d+)(\s|$)/);
                            if (matches) {
                                currentPage = matches[2];
                            } 
                        }
                        if(currentPage !== "" && that.isInArray(currentPage, popupExcludePages)) {
                            exclude = true;
                        }
                    }

                    if(!exclude) {
                        if(response.firstTime) {
                            if(that.elements.popUpBackdrop.length > 0) {
                                that.elements.popUpBackdrop.show();
                            }
                            $.each($('.gdpr-service-switch:not(:disabled)'), function(i, index) {
                                $(this).prop('checked', true);
                            });
                            that.elements.popUp.show();
                        }
                        else if((!response.allowed && !response.declined) || that.getParameterByName('gdpr') === "debug") {
                            if(that.elements.popUpBackdrop.length > 0) {
                                that.elements.popUpBackdrop.show();
                            }
                            that.elements.popUp.show();
                        } else {
                            if(that.elements.popUpBackdrop.length > 0) {
                                that.elements.popUpBackdrop.hide();
                            }
                            that.elements.popUp.hide();
                        }
                    }

                    //if(response.declined == true) {
                       // return;
                    //}
                    that.checkPrivacySettings();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr);
                    console.log(ajaxOptions);
                    console.log(thrownError);
                }
             });
        },
        popUpClose : function() {

            var that = this;
            
            $(that.elements.popUpCloseLink).on('click', function(e) {
                e.preventDefault();
                if(that.elements.popUpBackdrop.length > 0) {
                    that.elements.popUpBackdrop.fadeOut();
                }
                that.elements.popUp.fadeOut();
            });
        },
        popUpDecline : function() {

            var that = this;
            
            that.elements.popUpDeclineLink.on('click', function(e) {

                e.preventDefault();

                var $this = $(this);
                var text = $this.text();
                $this.html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    type : 'post',
                    url : that.settings.ajaxURL,
                    dataType : 'json',
                    data : {
                        action : 'wordpress_gdpr_decline_cookies'
                    },
                    success : function( response ) {  

                        if(localStorage) {
                            localStorage.removeItem('wordpress_gdpr_services_allowed_temp');
                        }

                        if(that.elements.popUpBackdrop.length > 0) {
                            that.elements.popUpBackdrop.fadeOut();
                        }
                        $('.wordpress-gdpr-privacy-settings-popup, .wordpress-gdpr-privacy-settings-popup-backdrop').fadeOut();
                        that.elements.popUp.fadeOut();
                        $.each($('.gdpr-service-switch:not(:disabled)'), function(i, index) {
                            $(this).prop('checked', false);
                        });
                        $this.text(text);

                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(xhr);
                        console.log(ajaxOptions);
                        console.log(thrownError);
                    }
                 });
            });
        },
        popUpAgree : function() {

            var that = this;

            that.elements.popUpAgreeLink.on('click', function(e) {
                e.preventDefault();

                var $this = $(this);
                var text = $this.text();
                $this.html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    type : 'post',
                    url : that.settings.ajaxURL,
                    dataType : 'json',
                    data : {
                        action : 'wordpress_gdpr_allow_cookies'
                    },
                    success : function( response ) {

                        if(localStorage) {
                            localStorage.removeItem('wordpress_gdpr_services_allowed_temp');
                        }

                        if(that.elements.popUpBackdrop.length > 0) {
                            that.elements.popUpBackdrop.fadeOut();
                        }
                        $('.wordpress-gdpr-privacy-settings-popup, .wordpress-gdpr-privacy-settings-popup-backdrop').fadeOut();
                        that.elements.popUp.fadeOut();
                        $.each($('.gdpr-service-switch:not(:disabled)'), function(i, index) {
                            $(this).prop('checked', true);
                        });
                        
                        that.checkPrivacySettings();
                        $this.text(text);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(xhr);
                        console.log(ajaxOptions);
                        console.log(thrownError);
                    }
                 });
            });
        },
        popUpPrivacySettingsOpen : function () {

            var that = this;

            $('.wordpress-gdpr-open-privacy-settings-modal').on('click', function(e) {
                e.preventDefault();

                if(($('.wordpress-gdpr-privacy-settings-popup-services-container').length < 1)) {
                    alert('No GDPR Service Categories / Services created yet. Remove the trigger or create services and attach them to service categories first.');
                    return false;
                }

                if($('.wordpress-gdpr-popup').length > 0) {
                    $('.wordpress-gdpr-popup').hide(0, function() {
                        $('.wordpress-gdpr-privacy-settings-popup, .wordpress-gdpr-privacy-settings-popup-backdrop').fadeIn();
                    });
                } else {
                    $('.wordpress-gdpr-privacy-settings-popup, .wordpress-gdpr-privacy-settings-popup-backdrop').fadeIn();
                }
            });
        },
        popUpPrivacySettingsClose : function () {

            var that = this;
                
            $(that.elements.privacySettingsPopupCloseLink).on('click', function(e) {
                e.preventDefault();
                if(that.elements.privacySettingsPopupBackdrop.length > 0) {
                    that.elements.privacySettingsPopupBackdrop.fadeOut();
                }
                that.elements.privacySettingsPopup.fadeOut();
            });

            $(that.elements.privacySettingsPopupSaveLink).on('click', function(e) {

                e.preventDefault();
                if(that.elements.privacySettingsPopupBackdrop.length > 0) {
                    that.elements.privacySettingsPopupBackdrop.fadeOut();
                }

                that.checkPrivacySettings();
                that.elements.privacySettingsPopup.fadeOut();
            });

            if(that.settings.privacySettingsPopupBackdropDisableClick == "0") {
                $(that.elements.privacySettingsPopupBackdrop).on('click', function(e) {
                    e.preventDefault();
                    if(that.elements.privacySettingsPopupBackdrop.length > 0) {
                        that.elements.privacySettingsPopupBackdrop.fadeOut();
                    }
                    that.elements.privacySettingsPopup.fadeOut();
                });
            }
        },
        popUpPrivacySettings : function() {

            $('.wordpress-gdpr-popup-privacy-settings-open-service-category').on('click', function(e) {
                e.preventDefault();

                $('.wordpress-gdpr-popup-privacy-settings-open-service-category').removeClass('wordpress-gdpr-popup-privacy-settings-service-category-active');

                $(this).addClass('wordpress-gdpr-popup-privacy-settings-service-category-active');

                var id = $(this).data('id');
                $('.wordpress-gdpr-popup-privacy-settings-services-content:not(#wordpress-gdpr-popup-privacy-settings-services-content-' + id + ')').hide(0, function() {
                    $('#wordpress-gdpr-popup-privacy-settings-services-content-' + id).show();
                });
            });

            $('.wordpress-gdpr-popup-privacy-settings-services-content-title').on('click', function(e) {
                e.preventDefault();

                var $this = $(this);
                var id= $(this).data('id');
                var fa = $this.find('.fa');

                var description = $('#wordpress-gdpr-popup-privacy-settings-services-content-description-' + id);
                if (description.css('display') == 'none') {
                    description.slideDown();
                    fa.removeClass('fa-caret-right').addClass('fa-caret-down');
                } else {
                    description.slideUp();
                    fa.removeClass('fa-caret-down').addClass('fa-caret-right');
                }
            });
        },
        checkPrivacySettings : function() {

            var that = this;
            var settings = {};
            var switches = $('.gdpr-service-switch');

            $.each(switches, function() {
                var serviceID = $(this).data('id');
                settings[serviceID] = serviceID;
            });

            $.ajax({
                type : 'post',
                url : that.settings.ajaxURL,
                dataType : 'json',
                data : {
                    action : 'check_privacy_settings',
                    settings : settings,
                },
                success : function( response ) {

                    if(localStorage && response !== 0) {
                        response.version = that.settings.version;
                        localStorage.setItem('wordpress_gdpr_services_allowed_temp', JSON.stringify(response));
                    }

                    that.executeServices(response);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr);
                    console.log(ajaxOptions);
                    console.log(thrownError);
                }
             });
        },
        executeServices : function(services) {

            var that = this;

            if(!services) {
                return;
            }

            window.dataLayer = window.dataLayer || [];

            if(that.settings.consentModeV2 == "1") {
                window.gtag = function() { dataLayer.push(arguments); }
                window.gtag('consent', 'default', {
                  ad_storage: 'denied',
                  analytics_storage: 'denied',
                  ad_user_data: 'denied',
                  ad_personalization: 'denied',
                  wait_for_update: 500
                });
            }

            var that = this;
            var obj = {'event' : 'consent_status'};
            $.each(services, function(i, index) {

                if(index.allowed && !that.privacySettingsLoaded[i]) {

                    if(index.head !== "") {
                        $("head").append(index.head);
                    }

                    if(index.body !== "") {
                        $(index.body).prependTo($('body'));
                    }
                }

                if(index.adsense == "1" && !index.allowed && that.privacySettingsLoaded[i]) {

                    var adsExists = $(".adsbygoogle");
                    if(adsExists.length > 0 && that.getCookie('wordpress_gdpr_adsense_allowed') !== "true") {
                        adsExists.remove();
                    } 
                }

                if(index.adwords == "1" && index.allowed && that.settings.consentModeV2 == "1") {
                    window.gtag('consent', 'update', {
                      'ad_storage': 'granted',
                      'ad_user_data': 'granted',
                      'ad_personalization': 'granted',
                    });
                }

                if(index.analytics == "1" && index.allowed && that.settings.consentModeV2 == "1") {
                    window.gtag('consent', 'update', {
                      'analytics_storage': 'granted',
                    });
                }

                var checkbox_exists = $('input[name="' + i + '"]') ;
                if(checkbox_exists.length > 0) {
                    if(index.allowed) {
                        checkbox_exists.prop('checked', true);
                        that.privacySettingsLoaded[i] = true;
                    } else {
                        checkbox_exists.prop('checked', false);
                        that.privacySettingsLoaded[i] = false;
                    }
                }
                
                obj[index.name] = index.allowed;

                if(checkbox_exists.length > 0 && !that.privacySettingsCheckPerformed) {

                    checkbox_exists.on('change', function(e) {

                        var checked = $(this).prop('checked');
                        var name = $(this).prop('name');

                        $('.wordpress-gdpr-privacy-settings-popup-message').fadeIn();

                        $.ajax({
                            type : 'post',
                            url : that.settings.ajaxURL,
                            dataType : 'json',
                            data : {
                                action : 'update_privacy_setting',
                                setting : name,
                                checked : checked,
                            },
                            success : function( response ) {

                                if(localStorage) {
                                    localStorage.removeItem('wordpress_gdpr_services_allowed_temp');
                                }

                                setTimeout(function(){ $('.wordpress-gdpr-privacy-settings-popup-message').fadeOut(); }, 1500);                                        

                                var index = response[name];
                                if(index.head !== "" && index.allowed && !that.privacySettingsLoaded[name]) {
                                    $("head").append(index.head);
                                }
                                if(index.body !== "" && index.allowed && !that.privacySettingsLoaded[name]) {
                                    $(index.body).prependTo($('body'));
                                }

                                if(index.adsense == "1" && !index.allowed && !that.privacySettingsLoaded[name]) {
                                    var adsExists = $(".adsbygoogle");

                                    if(adsExists.length > 0 && that.getCookie('wordpress_gdpr_adsense_allowed') !== "true") {
                                        adsExists.remove();
                                    }
                                }
                                
                                var obj = {};
                                obj[index.name] = index.allowed;
                                window.dataLayer.push( { 'event' : 'consent_status', 'consents': obj } );

                                if(index.analytics == "1" && index.allowed && that.settings.consentModeV2 == "1") {
                                    window.gtag('consent', 'update', {
                                      'analytics_storage': 'granted',
                                    });
                                }

                                if(index.adwords == "1" && index.allowed && that.settings.consentModeV2 == "1") {
                                    window.gtag('consent', 'update', {
                                      'ad_storage': 'granted',
                                      'ad_user_data': 'granted',
                                      'ad_personalization': 'granted',
                                    });
                                }

                                var checkbox_exists = $('input[name="' + name + '"]') ;
                                if(checkbox_exists.length > 0) {
                                    if(index.allowed) {
                                        checkbox_exists.prop('checked', true);
                                        that.privacySettingsLoaded[name] = true;
                                    } else {
                                        checkbox_exists.prop('checked', false);
                                    }
                                }
                            }
                        });
                    });
                }
            });

            that.privacySettingsCheckPerformed = true;

            if(that.settings.consentModeV2 == "1") {
                window.dataLayer.push( { 'event' : 'consent_status', 'consents': obj } );
            }
        },
        get_current_page_id : function() {
            var page_body = $('body.page');

            var id = 0;

            if(page_body) {
                var page_body_class = page_body.attr('class')

                if(page_body_class || page_body_class != null) {
                    if(page_body_class.length > 1) {
                        var page_body_class_list = page_body_class.split(/\s+/);

                        $.each(page_body_class_list, function(index, item) {
                            if (item.indexOf('page-id') >= 0) {
                                var item_arr = item.split('-');
                                id =  item_arr[item_arr.length -1];
                                return false;
                            }
                        });
                    }
                }
            }
            return id;
        },
        getUsersCountry : function(callback) {
            var that = this;

            $.ajax({
                url: "https://get.geojs.io/v1/ip/geo.json",
                type: 'get',
                dataType: 'json',
                success : function( response ) {
                    callback(response.country_code);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);
                    console.log(textStatus);
                    console.log(errorThrown);
                }
            });
        },
        commentFormprivacyPolicyTermsAcceptance : function() {
            var that = this;

            var submit = $('#commentform input[type="submit"]');
            if(submit.length < 1) {
                return false;
            }

            var checkbox = $('#commentform #privacy_policy');
            if(checkbox.length < 1) {
                return false;
            }

            submit.on('click', function(e) {
                var checkboxChecked = checkbox.is(':checked');
                if(!checkboxChecked) {
                    e.preventDefault();
                    alert(that.settings.acceptanceText);
                    return false;
                }

                $.ajax({
                    type : 'post',
                    url : that.settings.ajaxURL,
                    dataType : 'json',
                    data : {
                        action : 'wordpress_gdpr_update_privacy_policy_terms',
                        setting : 'wordpress_gdpr_privacy_policy_accepted',
                        checked : checkboxChecked,
                    }
                });
                return true;                
            });
        },
        privacyPolicyTermsAcceptance : function() {
            var that = this;

            if($('#accept-privacy-policy-checkbox').length < 1 && $('#accept-terms-conditions-checkbox').length < 1) {
                return false;
            }

            $('#accept-privacy-policy-checkbox').on('click', function(e) {
                var name = $(this).prop('name');
                var checked = $(this).prop('checked');

                if(!checked) {
                    alert(that.settings.acceptanceText);
                    return false;
                }
                
                $.ajax({
                    type : 'post',
                    url : that.settings.ajaxURL,
                    dataType : 'json',
                    data : {
                        action : 'wordpress_gdpr_update_privacy_policy_terms',
                        setting : name,
                        checked : checked,
                    },
                });
            });

            $('#accept-terms-conditions-checkbox').on('click', function(e) {
                var name = $(this).prop('name');
                var checked = $(this).prop('checked');

                // if(!checked) {
                //     alert(that.settings.termsAcceptanceText);
                //     return false;
                // }
                
                $.ajax({
                    type : 'post',
                    url : that.settings.ajaxURL,
                    dataType : 'json',
                    data : {
                        action : 'wordpress_gdpr_update_privacy_policy_terms',
                        setting : name,
                        checked : checked,
                    },
                });
            });
        },
        //////////////////////
        ///Helper Functions///
        //////////////////////
        deleteAllCookies : function() {
            var cookies = document.cookie.split(";");
            for (var i = 0; i < cookies.length; i++) {
                var cookie = cookies[i];
                var eqPos = cookie.indexOf("=");
                var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
                document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
            }

        },
        isEmpty: function(obj) {

            if (obj == null)        return true;
            if (obj.length > 0)     return false;
            if (obj.length === 0)   return true;

            for (var key in obj) {
                if (hasOwnProperty.call(obj, key)) return false;
            }

            return true;
        },
        sprintf: function parse(str) {
            var args = [].slice.call(arguments, 1),
                i = 0;

            return str.replace(/%s/g, function() {
                return args[i++];

            });
        },
        getCookie: function(cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for(var i=0; i<ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1);
                if (c.indexOf(name) === 0) return c.substring(name.length, c.length);
            }
            return "";
        },
        createCookie: function(name, value, days) {
            var expires = "";

            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days*24*60*60*1000));
                var expires = "; expires="+date.toGMTString();
            }

            document.cookie = name + "=" + value+expires + "; path=/";
        },
        deleteCookie: function(name) {
            this.createCookie(name, '', -10);
        },
        getParameterByName: function(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, "\\$&");
            var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)", "i"),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, " "));
        },
        isInArray : function (value, array) {
            return array.indexOf(value) > -1;
        }
    } );

    // Constructor wrapper
    $.fn[ pluginName ] = function( options ) {
        return this.each( function() {
            if ( !$.data( this, "plugin_" + pluginName ) ) {
                $.data( this, "plugin_" +
                    pluginName, new Plugin( this, options ) );
            }
        } );
    };

    $(document).ready(function() {

        $( "body" ).gdpr( 
            gdpr_options
        );

    } );

})( jQuery );