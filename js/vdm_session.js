function vdmOrderWithSession(eventId, buttonId) {
    if (document.cookie.indexOf('voordemensen_session_id') !== -1) {
        const sessionId = getCookie('voordemensen_session_id');
        vdm_order(eventId, sessionId);
    } else {

        // Make an AJAX request to fetch the nonce first
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'voordemensen_generate_nonce',
                security: vdm_basketcounter_data.nonce
            },
            success: function(response) {
                if (response.success && response.data.nonce) {

                    // Now make an AJAX request to fetch the session ID
                    jQuery.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'voordemensen_fetch_session_id',
                            security: response.data.nonce
                        },
                        success: function(response) {
                            if (response.success && response.data.session_id) {
                                vdm_order(eventId, response.data.session_id);
                            } else {
                                console.error('Failed to fetch session ID:', response.data);
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching session ID:', error);
                        }
                    });
                } else {
                    console.error('Failed to generate nonce:', response.data);
                }
            },
            error: function(error) {
                console.error('Error generating nonce:', error);
            }
        });
    }
}

// Utility function to get cookie value by name
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

jQuery(document).ready(function($) {
    // Fetch the session ID and update the basket counter
    function fetchSessionIdAndUpdateBasket() {
        // Check if the session_id cookie is already set
        if (document.cookie.indexOf('voordemensen_session_id') !== -1) {
            const sessionId = getCookie('voordemensen_session_id');
            updateBasketCounter(sessionId);
        } else {
            // Make an AJAX request to fetch the session ID
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'voordemensen_fetch_session_id',
                    security: vdm_basketcounter_data.nonce

                },
                success: function(response) {
                    if (response.success && response.data.session_id) {
                        const sessionId = response.data.session_id;
                        updateBasketCounter(sessionId);
                    } else {
                        console.error('Failed to fetch session ID:', response.data);
                    }
                },
                error: function(error) {
                    console.error('Error fetching session ID:', error);
                }
            });
        }
    }

    // Update the basket counter
    function updateBasketCounter(sessionId) {
        const clientShortname = vdm_basketcounter_data.client_shortname;
        const domainName = vdm_basketcounter_data.domain_name;
        
        $.ajax({
            url: `https://${domainName}/api/${clientShortname}/cart/${sessionId}`,
            method: 'GET',
            success: function(response) {
                let content;
                if (Array.isArray(response) || typeof response === 'object') {
                    content = Object.keys(response).length - 1;  // Count items in the cart
                } else {
                    content = 'n/a';
                }
                $('.vdm_basketcounter').text(content);
            },
            error: function(error) {
                console.error('Error fetching basket count:', error);
                $('.vdm_basketcounter').text('n/a');
            }
        });
    }

    // Utility function to get cookie value by name
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // Fetch the session ID and update the basket counter on page load
    fetchSessionIdAndUpdateBasket();
});
