$(document).ready(function () {
  // Load data on page load
  $.ajax({
    url: "/php/profile.php",
    method: "GET",
    success: function (res) {
      if (res.success && res.profile) {
        $("#age").val(res.profile.age || "");
        $("#dob").val(res.profile.dob || "");
        $("#contact").val(res.profile.contact || "");
      }
    },
  });

  // On form submit, send update
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
