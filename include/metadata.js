// include/metadata.js

// console.log("metadata.js is loaded");

window.addEventListener('load', function() {
    var pageLoadTime = window.performance.timing.loadEventEnd > 0 
        ? (window.performance.timing.loadEventEnd - window.performance.timing.navigationStart) / 1000
        : window.performance.now() / 1000;

    var connectionType = 'unknown';
    if (navigator.connection && navigator.connection.effectiveType) {
        connectionType = navigator.connection.effectiveType;
    }

    // console.log("Connection Type detected: " + connectionType);

    var screenResolution = window.screen.width + 'x' + window.screen.height;
    var currentUrl = window.location.href;
    var referrerUrl = document.referrer || 'Direct';
    var price = window.price !== undefined && window.price !== 0 ? window.price : null;
    var timezone = window.userTimezone || 'Unknown';
    var devicePlatform = navigator.userAgentData ? navigator.userAgentData.platform : navigator.platform;
    var fullUserAgent = navigator.userAgent;

    // console.log("Timezone detected: " + timezone);

    if (timezone === 'Unknown') {
        // console.log("Timezone not available from browser, fetching from IPinfo...");
        jQuery.get("https://ipinfo.io", function(response) {
            var location = response.city + ", " + response.region + ", " + response.country;
            timezone = response.timezone || 'Unknown';
            // console.log("Timezone from IPinfo: " + timezone);
            // console.log("Location from IPinfo: " + location);

            var metadata = {
                action: 'save_metadata',
                nonce: wpsfse_ajax.nonce,
                timezone: timezone,
                page_load_time: pageLoadTime,
                connection_type: connectionType,
                screen_resolution: screenResolution,
                location: location,
		        platform: devicePlatform,
		        user_agent: fullUserAgent,
                url: currentUrl,
                referrer_url: referrerUrl
            };

            // Füge 'price' nur hinzu, wenn er gesetzt ist
            if (price !== null) {
                metadata.price = price;
            }

            // console.log("Metadata being sent:", metadata);

            jQuery.ajax({
                url: wpsfse_ajax.ajax_url,
                type: 'POST',
                data: metadata,
                success: function(response) {
                    // console.log('Metadaten erfolgreich gesendet:', response);
                },
                error: function(error) {
                    console.error('Fehler beim Senden der Metadaten:', error);
                }
            });
        }, "jsonp");
    } else {
        // console.log("Timezone from browser: " + timezone);
        jQuery.get("https://ipinfo.io", function(response) {
            var location = response.city + ", " + response.region + ", " + response.country;
            // console.log("Location from IPinfo: " + location);

            var metadata = {
                action: 'save_metadata',
                nonce: wpsfse_ajax.nonce,
                timezone: timezone,
                page_load_time: pageLoadTime,
                connection_type: connectionType,
                screen_resolution: screenResolution,
                location: location,
                url: currentUrl,
                referrer_url: referrerUrl
            };

            // Füge 'price' nur hinzu, wenn er gesetzt ist
            if (price !== null) {
                metadata.price = price;
            }

            // console.log("Metadata being sent:", metadata);

            jQuery.ajax({
                url: wpsfse_ajax.ajax_url,
                type: 'POST',
                data: metadata,
                success: function(response) {
                    // console.log('Metadaten erfolgreich gesendet:', response);
                },
                error: function(error) {
                    console.error('Fehler beim Senden der Metadaten:', error);
                }
            });
        }, "jsonp");
    }
});