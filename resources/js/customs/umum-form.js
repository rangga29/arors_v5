function formatDate(event) {
    let input = event.target;
    let value = input.value.replace(/\D/g, '').substring(0, 8); // Remove non-numeric characters and limit to 8 digits

    if (value.length > 4) {
        value = value.replace(/^(\d{2})(\d{2})(\d{0,4})/, '$1/$2/$3'); // Format as dd/mm/yyyy
    } else if (value.length > 2) {
        value = value.replace(/^(\d{2})(\d{0,2})/, '$1/$2'); // Format as dd/mm
    }

    input.value = value;
}

document.addEventListener('input', function (event) {
    if (event.target.matches('#birthday')) {
        formatDate(event);
    }
});

// import moment from "moment";
//
// $("input").on("change", function() {
//     this.setAttribute(
//         "data-date",
//         moment(this.value, "YYYY-MM-DD")
//             .format( this.getAttribute("data-date-format") )
//     )
// }).trigger("change")
