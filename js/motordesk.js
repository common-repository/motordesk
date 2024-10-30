/* MotorDesk Plugin JS */

(function($) {
     
    $(document).ready(function() {

        // Type/body selection
        $('#motordesk_vehicle_search_type').on('change', function() {

            var value = motordesk_string_url($(this).val());
            value = value.toLowerCase();
            if (value=='') {
                value = 'all';
            }

            $('.motordesk_vehicle_search_body').hide().removeAttr('name');
            
            $('#motordesk_vehicle_search_body_' + value).attr('name', 'body_' + value).removeClass('motordesk-hide').show();
            
        });

        // Make/model selection
        $('#motordesk_vehicle_search_make').on('change', function() {

            var value = motordesk_string_url($(this).val());
            value = value.toLowerCase();
            if (value=='') {
                value = 'all';
            }

            $('.motordesk_vehicle_search_model').hide().removeAttr('name');
            
            $('#motordesk_vehicle_search_model_' + value).attr('name', 'model_' + value).removeClass('motordesk-hide').show();
            
        });          
    });

})(jQuery);

function motordesk_url(url) {
    window.location.href = url;
}

function motordesk_string_url(value) {
    value = value.replace(/\s{2,}/g, ' ');
    value = value.replace(/ /g, '-');
    value = value.replace(/-{2,}/g, '-');
    value = value.replace(/[^a-zA-Z0-9-_]/g, '');
    while ((value.substr(0,1)=='-') || (value.substr(0,1)=='_')) {
        value = value.substr(1);
    }
    return value;
}