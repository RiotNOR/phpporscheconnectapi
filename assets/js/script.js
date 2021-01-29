// Just handling a single car for now, but $car CAN be an array of all available cars
// Check comments in PorscheAuth.class.php
let handleData = function (data) {
    var car = JSON.parse(data);
    //car = car[0];

    var titleElm = document.getElementById("modelDescription");
    var imageElm = document.getElementById("modelImage");

    titleElm.innerText = car.modelYear + " " + car.modelDescription;
    imageElm.setAttribute("src", car.modelImageUrl);

    console.log("HandleData: ", car);
}

$('.loginForm').on('submit', function (e) {
    e.preventDefault();

    var url = $(this).attr("action");
    var form_data = $(this).serialize();

    $.ajax({
        type: 'POST',
        url: url,
        data: form_data,
        beforeSend: function() {
            $("#loadingInfo").css("display", "block");
        },
        success: function (data) { 
            handleData(data)
        },
        error: function (xhr)
        {

        },
        complete: function () {
            $("#loadingInfo").css("display", "none");
        }
    });
});