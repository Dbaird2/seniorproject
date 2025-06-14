//Getting value from "ajax.php".
function fill(Value) {
    //Assigning value to "search" div in "search.php" file.
     $('#search').val(Value);
    //Hiding "display" div in "search.php" file.
     $('#display').hide();
   }
   function searchTrigger() {
        //Assigning search box value to javascript variable named as "name".
        var name = $('#search').val();
        var offset = $('#offset').val();
        var categories = $('#categories').val();
        var statusFilter = $('#statusFilter').val();
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
                    offset: offset,
                    categories: categories,
                    statusFilter, statusFilter,
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
   }
   $(document).ready(function() {
        //On pressing a key on "Search box" in "search.php" file. This function will be called.
        $("#search").keyup(searchTrigger);
        $("#search").ready(searchTrigger); 
   
        $("#search-btn").click(searchTrigger);
        const status = localStorage.getItem('statusFilter');
        if (status) {
            $('#statusFilter').val(status);
        }

        $('#statusFilter').on('change', function () {
            localStorage.setItem('statusFilter', $(this).val());
        });

        const category = localStorage.getItem('categories');
      
        if (category) {
            $('#categories').val(category);
        }

        $('#categories').on('change', function () {
            localStorage.setItem('categories', $(this).val());
        });
       

        $('#search').on('input', function() {
            $('#offset').val(1);
        });
    });
