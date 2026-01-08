$("#confirm-password").on("blur", function () {
  let $passwordFeedback = $("#password-feedback");
  let $button1 = $("input.button");
  if ($(this).val() !== $("#new-password").val()) {
    $(this).parent().css("margin-bottom", "10px");
    $passwordFeedback.text("The passwords don't match.");
    $button1.attr("disabled", true);
  } else {
    $passwordFeedback.text("");
    $button1.attr("disabled", false);
  }
});
