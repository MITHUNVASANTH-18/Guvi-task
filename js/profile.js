$(document).ready(function () {
  const token = localStorage.getItem("token");

  if (!token) {
    alert("User not logged in.");
    return;
  }

  $.ajax({
    url: "/php/profile.php",
    method: "GET",
    headers: {
      Authorization: "Bearer " + token,
    },
    success: function (res) {
      if (res.success && res.profile) {
        $("#age").val(res.profile.age || "");
        $("#dob").val(res.profile.dob || "");
        $("#contact").val(res.profile.contact || "");
      } else {
        $("#message").text("Failed to load profile").css("color", "red");
      }
    },
    error: function () {
      $("#message").text("Error loading profile").css("color", "red");
    },
  });

  $("#profileForm").submit(function (e) {
    e.preventDefault();

    const data = {
      age: $("#age").val(),
      dob: $("#dob").val(),
      contact: $("#contact").val(),
    };

    $.ajax({
      url: "/php/profile.php",
      method: "POST",
      headers: {
        Authorization: "Bearer " + token,
      },
      contentType: "application/json",
      data: JSON.stringify(data),
      success: function (res) {
        $("#message").text(res.message).css("color", "green");
      },
      error: function () {
        $("#message").text("Update failed").css("color", "red");
      },
    });
  });
});
