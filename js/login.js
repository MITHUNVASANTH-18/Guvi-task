$(document).ready(function () {
  $("#loginForm").on("submit", function (e) {
    e.preventDefault();

    const email = $("#email").val().trim();
    const password = $("#password").val().trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    $("#loginResponse").text("");

    if (!email || !password) {
      $("#loginResponse").text("Please fill in all required fields.");
      return;
    }

    if (!emailRegex.test(email)) {
      $("#loginResponse").text("Please enter a valid email address.");
      return;
    }

    if (password.length < 6) {
      $("#loginResponse").text("Password must be at least 6 characters.");
      return;
    }
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
