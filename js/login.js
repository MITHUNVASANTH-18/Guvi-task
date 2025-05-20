$(document).ready(function () {
  $("#loginForm").on("submit", function (e) {
    e.preventDefault();

    const email = $("#email").val();
    const password = $("#password").val();

    $.ajax({
      url: "php/login.php",
      method: "POST",
      data: { email, password },
      dataType: "json",
      success: function (res) {
        if (res.status === "success") {
          localStorage.setItem("token", res.token);
          window.location.href = "profile.html";
        } else {
          $("#loginResponse").text(res.error || "Login failed.");
        }
      },
      error: function () {
        $("#loginResponse").text("Server error.");
      },
    });
  });
});
