let $button = $(".btn");

//Departure date validator
$("#dep-date").on("blur", function () {
  let today = new Date();
  let $depDateFeedback = $("#dep-date-feedback");
  let depart = new Date($(this).val());
  let weekFromToday = new Date();
  weekFromToday.setDate(today.getDate() + 7);
  if (depart <= today) {
    $(this).parent().css("margin-bottom", "10px");
    $depDateFeedback.text("Departure date must start from tomorrow.");
    $button.attr("disabled", true);
  } else if (depart > weekFromToday) {
    $(this).parent().css("margin-bottom", "10px");
    $depDateFeedback.text("Departure date can not exceed a week from today.");
    $button.attr("disabled", true);
  } else {
    $depDateFeedback.text("");
    $button.attr("disabled", false);
  }
});

// Return date validator
$("#ret-date").on("blur", function () {
  let $departure = $("#dep-date");
  let $retDateFeedback = $("#ret-date-feedback");
  let departureDate = new Date($departure.val());
  let returnDate = new Date($(this).val());
  let weekFromDeparture = new Date();
  weekFromDeparture.setDate(departureDate.getDate() + 7);
  if (returnDate <= departureDate) {
    $(this).parent().css("margin-bottom", "10px");
    $retDateFeedback.text("Return date must be later than departure.");
    $button.attr("disabled", true);
  } else if (returnDate > weekFromDeparture) {
    $(this).parent().css("margin-bottom", "10px");
    $retDateFeedback.text("Return date can not exceed a week from departure.");
    $button.attr("disabled", true);
  } else {
    $retDateFeedback.text("");
    $button.attr("disabled", false);
  }
});
