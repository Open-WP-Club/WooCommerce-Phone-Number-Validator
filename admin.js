jQuery(document).ready(function($) {
    var $countrySelect = $('#wc_phone_validator_countries');
    var $fieldContainer = $countrySelect.closest('.form-field');

    // Add padding to the bottom of the field container
    $fieldContainer.css('padding-bottom', '10px');

    // Add select all and deselect all buttons below the select field
    $fieldContainer.append('<p id="wc_phone_validator_buttons" style="margin-top: 15px; margin-bottom: 0;"><button type="button" id="wc_phone_validator_select_all" class="button">Select All</button> <button type="button" id="wc_phone_validator_deselect_all" class="button">Deselect All</button></p>');

    // Add a container for the message
    $fieldContainer.append('<p id="wc_phone_validator_message" style="color: #72aee6; margin-top: 10px; display: none;"></p>');

    var $message = $('#wc_phone_validator_message');

    $('#wc_phone_validator_select_all').click(function() {
        $countrySelect.find('option[value="WORLDWIDE"]').prop('selected', true);
        $countrySelect.find('option').not('[value="WORLDWIDE"]').prop('selected', false);
        $countrySelect.trigger('change');
    });

    $('#wc_phone_validator_deselect_all').click(function() {
        $countrySelect.find('option').prop('selected', false);
        $countrySelect.trigger('change');
    });

    // Ensure the description appears below the buttons and message
    var $description = $fieldContainer.find('.description');
    if ($description.length) {
        $description.detach().appendTo($fieldContainer);
    }

    // Handle "Worldwide" selection logic
    $countrySelect.on('change', function() {
        var selectedOptions = $countrySelect.val() || [];
        var worldwideSelected = selectedOptions.includes('WORLDWIDE');

        if (worldwideSelected) {
            // If "Worldwide" is selected, deselect all other options
            $countrySelect.find('option').not('[value="WORLDWIDE"]').prop('selected', false);
            $message.text('Worldwide option includes all countries. Individual country selection is disabled.').show();
        } else {
            // If any other option is selected, deselect "Worldwide"
            $countrySelect.find('option[value="WORLDWIDE"]').prop('selected', false);
            $message.hide();
        }

        // Trigger the change event to update the select2 visual state
        $countrySelect.trigger('change.select2');
    });

    // Add logic for when user tries to select other countries while Worldwide is selected
    $countrySelect.on('select2:selecting', function(e) {
        var selectedOptions = $countrySelect.val() || [];
        if (selectedOptions.includes('WORLDWIDE') && e.params.args.data.id !== 'WORLDWIDE') {
            e.preventDefault(); // Prevent the selection
            $message.text('Worldwide is already selected. Please deselect Worldwide to choose specific countries.').show();
            setTimeout(function() {
                $message.hide();
            }, 3000); // Hide the message after 3 seconds
        }
    });
});