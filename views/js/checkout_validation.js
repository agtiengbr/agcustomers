$(document).ready(function() {
    if (typeof agcustomer_checkout_errors !== 'undefined' && agcustomer_checkout_errors.length > 0) {
        // Show the modal
        $('#ag-checkout-validation-overlay').show();

        // Ensure no masks are applied to the date input inside modal
        var $bday = $('#ag-checkout-validation-form [name=birthday]');
        if ($bday.length && $bday.attr('type') === 'date' && typeof $.fn.unmask === 'function') {
            try { $bday.unmask(); } catch(e) {}
        }

        // Handle form submission
        $('#ag-checkout-validation-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var $messages = $form.find('.ag-validation-messages');
            var formData = $form.serialize();
            
                var t = (window.agcustomers && window.agcustomers.translations) || {};
                var savingTxt = t.saving || 'Saving...';
                var savedOk = t.saved_ok || 'Data updated successfully! The page will reload.';
                var pleaseFix = t.please_fix || 'Please correct the following errors:';
                var unexpected = t.unexpected_error || 'An unexpected error occurred. Try again.';
                var saveContinue = t.save_and_continue || 'Save and continue';

                $btn.prop('disabled', true).text(savingTxt);
            $messages.html(''); // Clear previous messages

            $.ajax({
                url: agcustomer_checkout_form_action, // This var is set via addJsDef
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                            $messages.html('<p class="success">' + savedOk + '</p>');
                        // Reload the page to reflect changes and continue checkout
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                            var errorHtml = '<p class="error">' + pleaseFix + '</p><ul>';
                        response.errors.forEach(function(error) {
                            errorHtml += '<li>' + error.message + '</li>';
                        });
                        errorHtml += '</ul>';
                        $messages.html(errorHtml);
                            $btn.prop('disabled', false).text(saveContinue);
                    }
                },
                error: function() {
                        $messages.html('<p class="error">' + unexpected + '</p>');
                        $btn.prop('disabled', false).text(saveContinue);
                }
            });
        });
    }
});