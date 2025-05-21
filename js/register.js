$(document).ready(function () {
  $("#registerForm").on("submit", function (e) {
    e.preventDefault();

    const username = $("#username").val();
    const email = $("#email").val();
    const password = $("#password").val();

    $.ajax({
      type: "POST",
      url: "php/register.php",
      contentType: "application/json",
      dataType: "json",
      data: JSON.stringify({
        name: username,
        email: email,
        password: password,
      }),
      success: function (response) {
        if (response.success) {
          localStorage.setItem("username", username);
          alert("Registration successful!");
          window.location.href = "login.html";
        } else {
          $("#responseMessage").html(
            '<div class="responseMessage">' + response.message + "</div>"
          );
        }
      },
      error: function () {
        $("#responseMessage").html(
          '<div class="responseMessage">Server error.</div>'
        );
      },
    });
  });
});
