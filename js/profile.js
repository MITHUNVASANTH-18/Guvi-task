$(document).ready(function () {
  const token = localStorage.getItem("token");
  if (!token) {
    window.location.href = "/login.html";
    return;
  }

  $.ajax({
    url: "/php/get_profile.php",
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify({ token }),
    success: function (res) {
      if (!res.success) {
        localStorage.removeItem("token");
        window.location.href = "/login.html";
        return;
      }
      $("#name").val(res.sqlUser.name);
      $("#email").val(res.sqlUser.email);
      $("#age").val(res.mongoProfile.age || "");
      $("#dob").val(res.mongoProfile.dob || "");
      $("#contact").val(res.mongoProfile.contact || "");
    },
    error: function () {
      localStorage.removeItem("token");
      window.location.href = "/login.html";
    },
  });

  $("#profileForm").submit(function (e) {
    e.preventDefault();
    const updateData = {
      token: token,
      age: $("#age").val(),
      dob: $("#dob").val(),
      contact: $("#contact").val(),
    };
    $.ajax({
      url: "/php/update_profile.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(updateData),
      success: function (res) {
        $("#message")
          .text(res.message)
          .removeClass()
          .addClass(res.success ? "text-success" : "text-danger");
      },
      error: function () {
        $("#message")
          .text("Error updating profile")
          .removeClass()
          .addClass("text-danger");
      },
    });
  });

  $("#logoutBtn").click(function () {
    $.ajax({
      url: "/php/logout.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify({ token }),
      complete: function () {
        localStorage.removeItem("token");
        window.location.href = "/login.html";
      },
    });
  });
});
