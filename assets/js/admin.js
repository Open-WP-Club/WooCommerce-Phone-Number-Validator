jQuery(document).ready(function ($) {
  // Existing select all / deselect all functionality
  $("#wc_phone_validator_select_all").click(function () {
    $("#wc_phone_validator_countries option").prop("selected", true);
    $("#wc_phone_validator_countries").trigger("change");
  });

  $("#wc_phone_validator_deselect_all").click(function () {
    $("#wc_phone_validator_countries option").prop("selected", false);
    $("#wc_phone_validator_countries").trigger("change");
  });

  // Bulk validation functionality
  $("#bulk-validate-button").click(function () {
    var $button = $(this);
    var $results = $("#bulk-validation-results");

    $button.prop("disabled", true);
    $results.html("Validating...");

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "bulk_validate_phone_numbers",
      },
      success: function (response) {
        $results.html(response);
        $button.prop("disabled", false);
      },
      error: function () {
        $results.html("An error occurred during validation.");
        $button.prop("disabled", false);
      },
    });
  });
});
