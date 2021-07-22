const app = {
    toggle : function() {
        console.log ("executed");
        var x = document.getElementById("formReview");
        if (x.style.display === "none") {
          x.style.display = "block";
        } else {
          x.style.display = "none";
        }
    }
};

document.addEventListener('DOMContentLoaded', app.toggle);