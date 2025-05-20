$(document).ready(function () {
  $("#signupForm").on("submit", function (e) {
    e.preventDefault();

    if (!this.checkValidity()) {
      e.stopPropagation();
      $(this).addClass("was-validated");
      return;
    }

    const data = {
      username: $("#username").val().trim(),
      email: $("#email").val().trim(),
      password: $("#password").val().trim(),
    };

    $.ajax({
      url: "login.php",
      type: "POST",
      data: JSON.stringify(data),
      contentType: "application/json",
      dataType: "json",
      success: function (response) {
        if (response.success) {
          $("#responseMessage")
            .removeClass("text-danger")
            .addClass("text-success")
            .text("Registration successful! You can now log in.");
          $("#signupForm")[0].reset();
          $("#signupForm").removeClass("was-validated");
        } else {
          $("#responseMessage")
            .removeClass("text-success")
            .addClass("text-danger")
            .text("Error: " + response.message);
        }
      },
      error: function () {
        $("#responseMessage")
          .removeClass("text-success")
          .addClass("text-danger")
          .text("An error occurred. Please try again.");
      },
    });
  });
});
