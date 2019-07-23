<!--accordeon-->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<!--datatable-->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.jqueryui.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.0.0/css/scroller.jqueryui.min.css">


<!--accordeon-->
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<!--<script src="https://code.jquery.com/jquery-1.12.4.js"></script>-->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<!--datatable-->
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.jqueryui.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.0.0/js/dataTables.scroller.min.js"></script>



<script>
    $(function () {
        $("#accordion").accordion({ clearStyle: true, autoHeight: false, heightStyle: "content"  });
        $( "#resizable" ).resizable();
        $( "#example").resizable();





        var dialog, form,

            // From http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#e-mail-state-%28type=email%29
            emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
            name = $( "#name" ),
            email = $( "#email" ),
            password = $( "#password" ),
            allFields = $( [] ).add( name ).add( email ).add( password ),
            tips = $( ".validateTips" );

        function updateTips( t ) {
            tips
                .text( t )
                .addClass( "ui-state-highlight" );
            setTimeout(function() {
                tips.removeClass( "ui-state-highlight", 1500 );
            }, 500 );
        }

        function checkLength( o, n, min, max ) {
            if ( o.val().length > max || o.val().length < min ) {
                o.addClass( "ui-state-error" );
                updateTips( "Length of " + n + " must be between " +
                    min + " and " + max + "." );
                return false;
            } else {
                return true;
            }
        }

        function checkRegexp( o, regexp, n ) {
            if ( !( regexp.test( o.val() ) ) ) {
                o.addClass( "ui-state-error" );
                updateTips( n );
                return false;
            } else {
                return true;
            }
        }

        function addUser() {
            var valid = true;
            allFields.removeClass( "ui-state-error" );

            valid = valid && checkLength( name, "username", 3, 16 );
            valid = valid && checkLength( email, "email", 6, 80 );
            valid = valid && checkLength( password, "password", 5, 16 );

            valid = valid && checkRegexp( name, /^[a-z]([0-9a-z_\s])+$/i, "Username may consist of a-z, 0-9, underscores, spaces and must begin with a letter." );
            valid = valid && checkRegexp( email, emailRegex, "eg. ui@jquery.com" );
            valid = valid && checkRegexp( password, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );

            if ( valid ) {
                $( "#users tbody" ).append( "<tr>" +
                    "<td>" + name.val() + "</td>" +
                    "<td>" + email.val() + "</td>" +
                    "<td>" + password.val() + "</td>" +
                    "</tr>" );
                dialog.dialog( "close" );
            }
            return valid;
        }

        dialog = $( "#dialog-form" ).dialog({
            autoOpen: false,
            height: 400,
            width: 350,
            modal: true,
            buttons: {
                "Create an account": addUser,
                Cancel: function() {
                    dialog.dialog( "close" );
                }
            },
            close: function() {
                form[ 0 ].reset();
                allFields.removeClass( "ui-state-error" );
            }
        });

        form = dialog.find( "form" ).on( "submit", function( event ) {
            event.preventDefault();
            addUser();
        });

        $( "#create-user" ).button().on( "click", function() {
            dialog.dialog( "open" );
        });



    });


    $(document).ready(function() {
        $('#example').DataTable( {
            data:           <?php echo json_encode($legalForm); ?>,
            columns: [
                {data: 'IDLegalForm'},
                {data: 'OwnershipFormID'},
                {data: 'Name'},
                {data: 'ShortName'},
                {data: 'Facet'}
                ],
            deferRender:    true,
            scrollY:        '50vh',
            scrollCollapse: true,
            scroller:       true
        } );

        $("#accordion").click(function(){

            $(this).next("va-content").slideToggle("slow")
                .siblings("va-content:visible").slideUp("slow");
            $(this).toggleClass(".va-content");
            $(this).siblings("va-heading");

        });


        $('#example').on('click', 'tbody td', function() {

            //get textContent of the TD
            console.log('TD cell textContent : ', this.textContent)

            //get the value of the TD using the API
            //console.log('value by API : ', table.cell({ row: this.parentNode.rowIndex, column : this.cellIndex }).data());

            console.log('row index ', this.parentNode.rowIndex);
        })
    } );

</script>

<div id="dialog-form" title="Create new user">
    <p class="validateTips">All form fields are required.</p>

    <form>
        <fieldset>
            <label for="name">Name</label>
            <input type="text" name="name" id="name" value="Jane Smith" class="text ui-widget-content ui-corner-all">
            <label for="email">Email</label>
            <input type="text" name="email" id="email" value="jane@smith.com" class="text ui-widget-content ui-corner-all">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" value="xxxxxxx" class="text ui-widget-content ui-corner-all">

            <!-- Allow form submission with keyboard without duplicating the dialog button -->
            <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
        </fieldset>
    </form>
</div>

<button id="create-user">Create new user</button>

<div id="accordion">
    <h3>Законная форма</h3>
    <div id="resizable" >
        <p>
            данные таблицы
        </p>

        <table id="example" class="display nowrap" style="width:100%;">
            <thead>
            <tr>
                <th>IDLegalForm</th>
                <th>OwnershipFormID</th>
                <th>Name</th>
                <th>ShortName</th>
                <th>Facet</th>
            </tr>
            </thead>
        </table>
        <pre>
            <?php //print_r($legalForm);?>
        </pre>
        <p>
            <?php //ёecho json_encode($legalForm);?>
        </p>
    </div>
    <h3>Section 2</h3>
    <div>
        <p>
            Sed non urna. Donec et ante. Phasellus eu ligula. Vestibulum sit amet
            purus. Vivamus hendrerit, dolor at aliquet laoreet, mauris turpis porttitor
            velit, faucibus interdum tellus libero ac justo. Vivamus non quam. In
            suscipit faucibus urna.
        </p>
    </div>
    <h3>Section 3</h3>
    <div>
        <p>
            Nam enim risus, molestie et, porta ac, aliquam ac, risus. Quisque lobortis.
            Phasellus pellentesque purus in massa. Aenean in pede. Phasellus ac libero
            ac tellus pellentesque semper. Sed ac felis. Sed commodo, magna quis
            lacinia ornare, quam ante aliquam nisi, eu iaculis leo purus venenatis dui.
        </p>
        <ul>
            <li>List item one</li>
            <li>List item two</li>
            <li>List item three</li>
        </ul>
    </div>
    <h3>Section 4</h3>
    <div>
        <p>
            Cras dictum. Pellentesque habitant morbi tristique senectus et netus
            et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in
            faucibus orci luctus et ultrices posuere cubilia Curae; Aenean lacinia
            mauris vel est.
        </p>
        <p>
            Suspendisse eu nisl. Nullam ut libero. Integer dignissim consequat lectus.
            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per
            inceptos himenaeos.
        </p>
    </div>
</div>

