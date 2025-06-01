//Getting value from "ajax.php".
function fill(Value) {
    //Assigning value to "search" div in "search.php" file.
     $('#search').val(Value);
    //Hiding "display" div in "search.php" file.
     $('#display').hide();
   }
   $(document).ready(function() {
    //On pressing a key on "Search box" in "search.php" file. This function will be called.
     $("#search").keyup(function() {
        //Assigning search box value to javascript variable named as "name".
         var name = $('#search').val();
        //Validating, if "name" is empty.
         if (name == "") {
            //Assigning empty value to "display" div in "search.php" file.
             $("#display").html("");
         }
        //If name is not empty.
         else {
            //AJAX is called.
             $.ajax({
                //AJAX type is "Post".
                 type: "POST",
                //Data will be sent to "ajax.php".
                 url: "Ajax.php",
                //Data, that will be sent to "ajax.php".
                 data: {
                    //Assigning value of "name" into "search" variable.
                     search: name
                 },
                //If result found, this funtion will be called.
                 success: function(html) {
                    //Assigning result to "display" div in "search.php" file.
                     $("#display").html(html).show();
                 }
             });
         }
     });
   });

       // Get the modal
const modal = document.getElementById("myModal");

// Get the link that opens the modal
const openModalLink = document.getElementById("openModalLink");

// Get the <span> element that closes the modal
const closeModalBtn = document.getElementById("closeModalBtn");

// When the user clicks the link, open the modal
openModalLink.onclick = function(event) {
    event.preventDefault(); // Prevent the default link behavior
    modal.style.display = "block"; // Show the modal
}

// When the user clicks on <span> (x), close the modal
closeModalBtn.onclick = function() {
    modal.style.display = "none"; // Close the modal
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none"; // Close the modal if clicked outside
    }
}

function changeTabs(evt, tab) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(tab).style.display = "block";
  evt.currentTarget.className += " active";
}
