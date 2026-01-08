//Departure date validator
$("#dep-date").on("blur", function () {
  let $button = $(".btn");
  let today = new Date();
  let $depDateFeedback = $("#dep-date-feedback");
  let depart = new Date($(this).val());
  let weekFromToday = new Date();
  weekFromToday.setDate(today.getDate() + 7);

  if (depart <= today) {
    //$(this).parent().css("margin-bottom", "10px");
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
