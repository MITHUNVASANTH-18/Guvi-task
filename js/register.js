$(document).ready(function () {
  $("#registerForm").on("submit", function (e) {
    e.preventDefault();

    const username = $("#username").val();
    const email = $("#email").val();
    const password = $("#password").val();

    $.ajax({
      type: "POST",
      url: "php/register.php",
      data: {
        username: username,
        email: email,
        password: password,
      },
      success: function (response) {
        if (response === "success") {
          localStorage.setItem("username", username);
          alert("Registration successful!");
          window.location.href = "profile.html";
        } else {
          $("#responseMessage").html(
            '<div class="alert alert-danger">' + response + "</div>"
          );
        }
      },
      error: function () {
        $("#responseMessage").html(
          '<div class="alert alert-danger">Server error.</div>'
        );
      },
    });
  });
});
